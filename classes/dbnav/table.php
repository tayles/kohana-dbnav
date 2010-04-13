<?php defined('SYSPATH') or die('No direct script access.');

class DBNav_Table extends Model {

	public $name, $engine, $num_rows, $avg_row_size, $data_size, $index_size, $auto_incr_id, $date_created, $date_updated, $date_checked, $collation, $comment;
	
	public $columns;
	
	public $total_size;
	
	
	public function pre_process() {
	
		// convert from database fields
		
		if( $this->data_size || $this->index_size ) {
			$this->total_size = $this->data_size + $this->index_size;
		}
	}

}
