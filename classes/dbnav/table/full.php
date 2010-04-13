<?php defined('SYSPATH') OR die('No direct access allowed.');

class DBNav_Full_Table extends DBNav_Decorated_Table {



	// set all properties in the constructor
		public function __construct($data) {
			$this->set_body_data($data)
					->add_column('dbnav_select')
					->set_footer('&#8598; with selected: ' . Html::anchor('#', 'Edit') . ' ' . Html::anchor('#', 'Delete'));
		}
		
		protected function _generate_body_cell($row, $column_name) {
			
			if( $column_name == 'dbnav_select' ) {
				return '<td><input type="checkbox" name="ids[]" id="idval_' . $row . '" value="idval" /></td>';
			}
			else if( $column_name == 'dbnav_options' ) {
				return '<td>' . Html::anchor(array('dbnav', 'db', $this->user_data['schema']->name, 'tbl', $this->user_data['table']->name, 'record', 1), 'Edit') . ' ' . Html::anchor(array('dbnav', 'db', $this->user_data['schema']->name, 'tbl', $this->user_data['table']->name, 'record', 1), 'Delete') . '</td>';
			}
			
			return parent::_generate_body_cell($row, $column_name);
		}
}