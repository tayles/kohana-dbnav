<?php defined('SYSPATH') or die('No direct script access.');

class DBNav {
	
	public $per_page = 10;
	
	public static function instance() {
		static $db = null;
		if ( $db == null )
		  $db = new DBNav();
		return $db;
	  }

  private function __construct() {
	
  }
  
  public function engines() {
		$engines = DB::select(
							array('ENGINE',					'name'),
							array('IF(ISNULL("SUPPORT"), NULL, IF(STRCMP("SUPPORT",\'DEFAULT\'), IF(STRCMP("SUPPORT",\'YES\'), 0,  1), \'DEFAULT\'))',				'support'),
							array('COMMENT',				'comment'),
							array('IF(ISNULL("TRANSACTIONS"), NULL, IF(STRCMP("TRANSACTIONS",\'YES\'), 0, 1))',			'transactions'),
							array('IF(ISNULL("XA"), NULL, IF(STRCMP("XA",\'YES\'), 0, 1))',						'xa'),
							array('IF(ISNULL("SAVEPOINTS"), NULL, IF(STRCMP("SAVEPOINTS",\'YES\'), 0, 1))',				'save_points')
						)
						->from('information_schema.engines')
						->as_object('DBNav_Engine')
						->execute();
		return $engines;
	}
	
	  public function variables() {
		$variables = DB::select(
							array('VARIABLE_NAME',					'name'),
							array('VARIABLE_VALUE',					'value')
						)
						->from('information_schema.global_variables')
						->execute();
		return $variables;
	}
	
	public function schemata() {
		$schemata = DB::select(
							array('SCHEMA_NAME',					'name'),
							array('DEFAULT_CHARACTER_SET_NAME',		'charset'),
							array('DEFAULT_COLLATION_NAME',			'collation')
						)
						->from('information_schema.schemata')
						->as_object('DBNav_Schema')
						->execute();
		return $schemata;
	}
	
	public function tables($schema_name) {
		$tables = DB::select(
							array('TABLE_NAME',						'name'),
							array('ENGINE',							'engine'),
							array('TABLE_ROWS',						'num_rows'),
							array('AVG_ROW_LENGTH',					'avg_row_size'),
							array('DATA_LENGTH',					'data_size'),
							array('INDEX_LENGTH',					'index_size'),
							array('AUTO_INCREMENT',					'auto_incr_id'),
							array('UNIX_TIMESTAMP("CREATE_TIME")',	'date_created'),
							array('UNIX_TIMESTAMP("UPDATE_TIME")',	'date_updated'),
							array('UNIX_TIMESTAMP("CHECK_TIME")',	'date_checked'),
							array('TABLE_COLLATION',				'collation'),
							array('TABLE_COMMENT',					'comment')
						)
						->from('information_schema.tables')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->as_object('DBNav_Table')
						->execute();
						
		$table_arr = array();
		foreach( $tables as $table ) {
			$table->pre_process();
			$table_arr[$table->name] = $table;
		}
		
		return $table_arr;
	}
	
	public function columns($schema_name, $table_name) {
		$columns = DB::select(
							array('COLUMN_NAME',					'name'),
							array('COLUMN_DEFAULT',					'default'),
							array('IF(STRCMP("IS_NULLABLE",\'YES\'), 0, 1)',	'nullable'),
							array('DATA_TYPE',						'type'),
							array('COLUMN_TYPE',					'raw_type'),
							array('CHARACTER_MAXIMUM_LENGTH',		'length'),
							array('NUMERIC_PRECISION',				'numeric_precision'),
							array('NUMERIC_SCALE',					'numeric_scale'),
							array('CHARACTER_SET_NAME',				'charset'),
							array('COLLATION_NAME',					'collation'),
							array('COLUMN_KEY',						'keys'),
							array('EXTRA',							'extra'),
							array('COLUMN_COMMENT',					'comment')
						)
						->from('information_schema.columns')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->as_object('DBNav_Column')
						->execute();
					
		// store in an assoc array so we can reference by column name
		$column_arr = array();
		foreach( $columns as $column ) {
			$column->pre_process();
			$column_arr[$column->name] = $column;
		}
		return $column_arr;
	}
	
	
	

	public function schema($schema_name) {
		$schema = DB::select(
							array('SCHEMA_NAME',					'name'),
							array('DEFAULT_CHARACTER_SET_NAME',		'charset'),
							array('DEFAULT_COLLATION_NAME',			'collation')
						)
						->from('information_schema.schemata')
						->where('SCHEMA_NAME', '=', $schema_name)
						->as_object('DBNav_Schema')
						->execute()
						->current();
		return $schema;
	}
	
	public function table($schema_name, $table_name) {
		$table = DB::select(
							array('TABLE_NAME',						'name'),
							array('ENGINE',							'engine'),
							array('TABLE_ROWS',						'num_rows'),
							array('AVG_ROW_LENGTH',					'avg_row_size'),
							array('DATA_LENGTH',					'data_size'),
							array('INDEX_LENGTH',					'index_size'),
							array('AUTO_INCREMENT',					'auto_incr_id'),
							array('UNIX_TIMESTAMP("CREATE_TIME")',	'date_created'),
							array('UNIX_TIMESTAMP("UPDATE_TIME")',	'date_updated'),
							array('UNIX_TIMESTAMP("CHECK_TIME")',	'date_checked'),
							array('TABLE_COLLATION',				'collation'),
							array('TABLE_COMMENT',					'comment')
						)
						->from('information_schema.tables')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->as_object('DBNav_Table')
						->execute()
						->current();
		$table->pre_process();
		return $table;
	}
	
	public function records($schema_name, $table_name, $page = 1, $per_page = 10) {
	
		$offset = ($page - 1) * $per_page;
	
		$total_count = DB::select(DB::expr('COUNT(*) AS mycount'))->from($schema_name.'.'.$table_name)->execute()->get('mycount');

	
		$records = DB::select()
					->from($schema_name.'.'.$table_name)
					->limit($per_page)
					->offset($offset)
					->as_object('DBNav_Record')
					->execute();
					
		return array( $total_count, $records );
	}
	
	public function record($schema_name, $table_name, $record_id) {
		$record = DB::select()
					->from($schema_name.'.'.$table_name)
					->where('id', '=', $record_id)
					->limit(1)
					->as_object('DBNav_Record')
					->execute()
					->current();
					
		return $record;
	}
	
	
	public function indices($schema_name, $table_name) {
		$indices = DB::select(
							array('IF("NON_UNIQUE" = 0, 1, 0 )',	'unique'),
							array('INDEX_SCHEMA',					'schema'),
							array('INDEX_NAME',						'name'),
							array('SEQ_IN_INDEX',					'seq_in_index'),
							array('COLUMN_NAME',					'column_name'),
							array('COLLATION',						'collation'),
							array('CARDINALITY',					'cardinality'),
							array('SUB_PART',						'sub_part'),
							array('PACKED',							'packed'),
							array('IF(STRCMP("NULLABLE",\'YES\'), 0, 1)',	'nullable'),
							array('INDEX_TYPE',						'type'),
							array('COMMENT',						'comment')
						)
						->from('information_schema.statistics')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->as_object('DBNav_Index')
						->execute();
						
		$index_arr = array();
		foreach( $indices as $index ) {
			#$index->pre_process();
			$index_arr[$index->name] = $index;
		}
		
		return $indices;
	}
	
	public function foreign_keys($schema_name, $table_name) {
		$keys = DB::select(
							array('CONSTRAINT_NAME',				'constraint_name'),
							array('TABLE_NAME',						'table_name'),
							array('COLUMN_NAME',					'column_name'),
							array('REFERENCED_TABLE_SCHEMA',		'referenced_table_schema'),
							array('REFERENCED_TABLE_NAME',			'referenced_table_name'),
							array('REFERENCED_COLUMN_NAME',			'referenced_column_name')
						)
						->from('information_schema.key_column_usage')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->where('REFERENCED_TABLE_NAME', 'IS NOT', NULL)
						->execute();
		
		return $keys;
	}
	
	
	public function index($schema_name, $table_name, $index_name) {
		$index = DB::select(
							array('IF("NON_UNIQUE" = 0, 1, 0 )',	'unique'),
							array('INDEX_SCHEMA',					'schema'),
							array('INDEX_NAME',						'name'),
							array('SEQ_IN_INDEX',					'seq_in_index'),
							array('COLUMN_NAME',					'column_name'),
							array('COLLATION',						'collation'),
							array('CARDINALITY',					'cardinality'),
							array('SUB_PART',						'sub_part'),
							array('PACKED',							'packed'),
							array('IF(STRCMP("NULLABLE",\'YES\'), 0, 1)',	'nullable'),
							array('INDEX_TYPE',						'type'),
							array('COMMENT',						'comment')
						)
						->from('information_schema.statistics')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->where('INDEX_NAME', '=', $index_name)
						->as_object('DBNav_Index')
						->execute()
						->current();
		return $index;
	}
	
	public function column($schema_name, $table_name, $column_name) {
		$column = DB::select(
							array('COLUMN_NAME',					'name'),
							array('COLUMN_DEFAULT',					'default'),
							array('IF(STRCMP("IS_NULLABLE",\'YES\'), 0, 1)',	'nullable'),
							array('DATA_TYPE',						'type'),
							array('COLUMN_TYPE',					'raw_type'),
							array('CHARACTER_MAXIMUM_LENGTH',		'length'),
							array('NUMERIC_PRECISION',				'numeric_precision'),
							array('NUMERIC_SCALE',					'numeric_scale'),
							array('CHARACTER_SET_NAME',				'charset'),
							array('COLLATION_NAME',					'collation'),
							array('COLUMN_KEY',						'keys'),
							array('EXTRA',							'extra'),
							array('COLUMN_COMMENT',					'comment')
						)
						->from('information_schema.columns')
						->where('TABLE_SCHEMA', '=', $schema_name)
						->where('TABLE_NAME', '=', $table_name)
						->where('COLUMN_NAME', '=', $column_name)
						->as_object('DBNav_Column')
						->execute()
						->current();

		$column->pre_process();
		
		return $column;
	}
  

}