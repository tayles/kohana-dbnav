<?php defined('SYSPATH') or die('No direct script access.');

class Controller_DBNav extends Controller_Template {
	
	public $template = 'dbnav/template';
	
	
	private $schema, $table;
	
	public function before() {
		parent::before();
		
		$this->dbnav = DBNav::instance();
		
		$this->schema_name = $this->request->param('schema');
		$this->table_name = $this->request->param('table');
		$this->current_id = $this->request->param('id');
		
		
		
		if( $this->schema_name ) {
			$this->schema = $this->dbnav->schema($this->schema_name);
			echo Kohana::debug($this->schema->name);
		}
		
		if( $this->table_name ) {
			$this->table = $this->dbnav->table($this->schema->name, $this->table_name);
			echo Kohana::debug($this->table->name);
		}
		
		$this->page = $this->request->param('page');
		$this->per_page = 20;
	}
	
	public function action_execsql() {
		$this->template->content = View::factory('dbnav/exec_sql')
										->bind('form', $form);
										
		$form = array(
			'sql' => '',
		);
	}
	
	private function _tokenize($keywords, $delimeter = ' ') {
		$token = strtok($keywords, $delimeter);
		$tokens = array();
		while ($token) {
		//echo Kohana::debug($token);
			// find double quoted tokens
			if ($token{0}=='"') { $token .= $delimeter . strtok('"').'"'; }
			// find single quoted tokens
			if ($token{0}=="'") { $token .= $delimeter . strtok("'")."'"; }
			// find bracketed tokens
			if ($token{0}=='(') { $token .= $delimeter . strtok(')').')'; }

			$tokens[] = $token;
			$token = strtok($delimeter);
		}

		return $tokens;
	}
	
	private function _matchColumnName($str, $columns) {
		$matches = array();
		foreach($columns as $column) {
			if( substr($column->name, 0, strlen($str)) == $str ) $matches[] = $column->name;
		}
		return ( count($matches) > 0 ? $matches : NULL );
	}
	
	public function action_search() {
	
		$columns = $this->dbnav->columns('geo', 'capital_cities');
	
		$query = $_GET['q'];
		
		//$tokens = $this->_tokenize($query);//$this->quotesplit($query);//preg_split("/[,]*\\\"([^\\\"]+)\\\"[,]*|" . "[,]*'([^']+)'[,]*|" . "[,]+/", $query, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		//echo Kohana::debug($tokens);
		
		//$operators = array('<(\s*)=', '>(\s*)=', 'not(\s*)in', 'is(\s*)null', 'is(\s*)not(\s*)null', '!(\s*)=');
		//$query = preg_replace('/\b(' . implode('|', $operators) . ')\b/', '', $query);
		
		$operators = array('!=', '=', '<=', '>=', '<', '>', 'like', 'rlike', 'regexp', 'in');
		$delimeters = array(',', ';', 'and');
		$brackets = array('\(', '\)');
		
		// add spaces to delimeters to ensure we can identify them
		$query = preg_replace('/(' . implode('|', array_merge($operators,$brackets, $delimeters)) . ')/', ' $1 ', $query);
		
		//echo Kohana::debug($query);
		
		// split on spaces
		$tokens = $this->_tokenize($query, ' ');
		
		//echo Kohana::debug($query);
		
		//echo Kohana::debug($tokens);
		
		// split by delimeters
		$clauses = array();
		$current = array();
		foreach($tokens as $token) {
			if( in_array($token, $delimeters) ) {
				if( count($current) > 0 ) {
					$clauses[] = $current;
					$current = array();
				}
			}
			else {
				$current[] = $token;
			}
		}
		if( count($current) > 0 ) {
			$clauses[] = $current;
		}
		
		$where = array();
		
		// loop over each clause
		foreach( $clauses as $clause ) {
		
			$column_name = NULL;
			
			// if the first token of the clause is a valid column name
			if( in_array( $clause[0], array_keys($columns) ) ) {
				$column_name = $clause[0];
			}
			else if( $matched_columns = $this->_matchColumnName($clause[0], $columns) ) {
				// attempt to guess the column name based on unique prefix
				if( count($matched_columns) == 1 ) {
					// we have a single match, use that
					$column_name = $matched_columns[0];
				}
				else {
					// raise a warning that this clause is not included
					echo Kohana::debug('Column name is ambiguous, matched: ' . implode(', ', $matched_columns) . '');
					continue;
				}
			}
			else {
				// raise a warning that this clause is not included
				echo Kohana::debug('Column name not recognised: "' . $clause[0] . '"');
				continue;
			}

			$operator = NULL;
			$val = NULL;
			
			if( count($clause) == 1 ) {
				// assume operator is IS NOT NULL
				$operator = 'is not';
				$val = NULL;
			}
			else if( count($clause) == 2 ) {
				if( $columns[$column_name]->keys == 'PRI' ) {
					// assume equality
					$operator = '=';
				}
				else {
					// do a fuzzy comparison
					$operator = 'like';				
				}
				$val = $clause[1];
			}
			else {
				
				if( in_array( $clause[1], $operators ) ) {
					// second token is an operator
					$operator = $clause[1];
					$val = array_slice($clause, 2);
				}
				else {
					// assume equality
					$operator = '=';
					$val = array_slice($clause, 1);
				}
				
				// join the arguments
				$val = implode(' ', $val);
			}
			
			// strip quotes (if not null)
			if( $val ) {
				$trim_quotes = function($str) {
					return trim($str, " '\"");
				};
				$val = $trim_quotes($val);
				
				if( $operator == 'in' ) {
					$val = trim($val, "()");
					
					$vals = array_map( $trim_quotes, $this->_tokenize($val, ',') );
					$val = $vals;
				}
				else if( $operator == 'like' && strpos($val, '%') === FALSE ) {
					// append a wildcard
					$val = '%' . $val . '%';
				}
			}
			
			
			// add clause to our sql statement
			$where[] = array(
				'column'	=> $column_name,
				'operator'	=> $operator,
				'val'		=> $val,
			);
			
			echo Kohana::debug($where);
		}
		
		
		if( count($where) > 0 ) {
			// construct sql query
			$query = DB::select('*')
						->from('geo.capital_cities')
						->limit(10);
						
			foreach( $where as $clause ) {
				$query->where($clause['column'], $clause['operator'], $clause['val']);
			}
			
			$results = $query->execute();
			
			echo Kohana::debug($results);
			
			$this->template->content = View::factory('dbnav/tabular')
					->bind('tbl_view', $tbl_view);
			
			$tbl_view = Table::factory('DBNav_Decorated', $results)
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table)
							->set_user_data('columns', $columns);
		}
	}
	
	public function action_storedprocs() {
		$this->template->content = View::factory('dbnav/tabular');
	}
	
	public function action_triggers() {
		$this->template->content = View::factory('dbnav/tabular');
	}
	
	public function action_privaledges() {
		$this->template->content = View::factory('dbnav/tabular');
	}
	
	
	public function action_engines() {
		$this->template->content = View::factory('dbnav/tabular')
					->bind('tbl_view', $tbl_view);

		$engines = $this->dbnav->engines();
		
		$tbl_view = Table::factory('DBNav_Admin', $engines);
	}
	
	public function action_variables() {
		$this->template->content = View::factory('dbnav/tabular')
					->bind('tbl_view', $tbl_view);

		$variables = $this->dbnav->variables();
		
		$tbl_view = Table::factory('DBNav_Admin', $variables);
	}
	
	public function action_schemata() {
	
		if( $this->schema_name != NULL ) {
			$this->action_schema();
		}
		else {
	
			$this->template->content = View::factory('dbnav/schema/list')
						->bind('schemata', $schemata)
						->bind('tbl_view', $tbl_view);
			
			$schemata = $this->dbnav->schemata();
			
			$tbl_view = Table::factory('DBNav_Admin', $schemata)
							->set_user_data('link_type', 'schema')
							->set_column_filter(array('name', 'charset', 'collation'));
		}
	}
	
	public function action_schema() {
		$this->template->content = View::factory('dbnav/table/list')
					->bind('tables', $tables)
					->bind('tbl_view', $tbl_view);

		$tables = $this->dbnav->tables($this->schema->name);
		
		$tbl_view = Table::factory('DBNav_Admin', $tables)
							->set_user_data('schema', $this->schema)
							->set_user_data('link_type', 'table')
							->set_column_filter(array('name', 'num_rows', 'auto_incr_id', 'data_size', 'index_size', 'total_size', 'date_created', 'date_updated', 'date_checked', 'engine', 'collation', 'comment'));
	}
	
	public function action_table() {
		$this->template->content = View::factory('dbnav/table/browse')
					->bind('columns', $columns)
					->bind('records', $records)
					->bind('foreign_keys', $foreign_keys)
					->bind('pagination', $pagination)
					->bind('tbl_view', $tbl_view);
					
		list( $total_count, $records ) = $this->dbnav->records($this->schema->name, $this->table->name, $this->page, $this->per_page);
		
		$columns = $this->dbnav->columns($this->schema->name, $this->table->name);
		
		$foreign_keys = $this->dbnav->foreign_keys($this->schema->name, $this->table->name);
		
		$render_heading = function($column_name) {
			return Html::anchor('?sort=' . $column_name, ucwords(Inflector::humanize($column_name)));
		};
		
		$tbl_view = Table::factory('DBNav_Full', $records)
							->set_user_data('sort', array('id', 'asc'))
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table)
							->set_user_data('columns', $columns)
							->set_user_data('foreign_keys', $foreign_keys)
							->set_column_titles(array_merge(array('<input type="checkbox" id="select_all_ids_head" class="select_all_ids" />', ''), array_map($render_heading, array_keys($columns))))
							->set_column_filter(array_merge(array('dbnav_select', 'dbnav_options'), array_keys($columns)));
					
		$pagination = Pagination::factory(array(
						'total_items'    => $total_count,
						'items_per_page' => $this->per_page,
					));
	}
	
	public function action_columns() {
		$this->template->content = View::factory('dbnav/column/list')
					->bind('columns', $columns)
					->bind('tbl_view', $tbl_view);

		$columns = $this->dbnav->columns($this->schema->name, $this->table->name);
		
		$tbl_view = Table::factory('DBNav_Admin', $columns)
							->set_user_data('link_type', 'column')
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table)
							->set_column_filter(array('name', 'keys', 'type', 'length', 'auto_incr', 'nullable', 'default', 'unsigned', 'zerofill', 'numeric_precision', 'numeric_scale', 'extra', 'collation', 'charset', 'comment'));
	}
	
	public function action_indices() {
		$this->template->content = View::factory('dbnav/index/list')
					->bind('indices', $indices)
					->bind('tbl_view', $tbl_view);

		$indices = $this->dbnav->indices($this->schema->name, $this->table->name);
		
		$tbl_view = Table::factory('DBNav_Admin', $indices)
							->set_user_data('link_type', 'index')
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table)
							->set_column_filter(array('name', 'unique', 'nullable', 'column_name', 'seq_in_index', 'collation', 'cardinality', 'sub_part', 'packed', 'type', 'comment'));
	}
	
	
	public function action_column() {
		$this->template->content = View::factory('dbnav/column/edit')
					->bind('column', $column)
					->bind('tbl_view', $tbl_view);
		
		$column_name = $this->current_id;

		if( $column_name ) {
			$column = $this->dbnav->column($this->schema->name, $this->table->name, $column_name);
		}
		
		$tbl_view = Table::factory('DBNav_Editable', $column)
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table);
	}
	
	public function action_index() {
		$this->template->content = View::factory('dbnav/index/edit')
					->bind('index', $index)
					->bind('tbl_view', $tbl_view);
		
		$index_name = $this->current_id;
		if( $index_name ) {
			$index = $this->dbnav->index($this->schema->name, $this->table->name, $index_name);
		}
		$tbl_view = Table::factory('DBNav_Editable', $index)
							->set_user_data('schema', $this->schema)
							->set_user_data('table', $this->table);
	}
	
	public function action_record() {
		$this->template->content = View::factory('dbnav/record/edit')
					->bind('columns', $columns)
					->bind('after', $after)
					->bind('record', $record);

		$columns = $this->dbnav->columns($this->schema->name, $this->table->name);
		
		$record_id = $this->current_id;
		
		if( $record_id ) {
			$record = $this->dbnav->record($this->schema->name, $this->table->name, $record_id);
		}
		
		$after = 'return';
	}
	
	public function action_edit() {
		$this->template->content = View::factory('dbnav/table/edit')
					->bind('columns', $columns)
					->bind('tbl_view', $tbl_view);

		$columns = $this->dbnav->columns($this->schema->name, $this->table->name);
		
		$tbl_view = Table::factory('DBNav_Admin', $columns);
	}
	
	public function after() {
		$this->template->schema = $this->schema;
		$this->template->content->schema = $this->schema;
		$this->template->table = $this->table;
		$this->template->content->table = $this->table;
		
		parent::after();
	}

}
