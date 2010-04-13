<?php defined('SYSPATH') or die('No direct script access.');

class DBNav_Column extends Model {

	public $name, $type, $raw_type, $length, $collation, $nullable, $default, $extra, $numeric_precision, $numeric_scale, $charset, $comment;
	
	public $keys, $auto_incr;
	
	public $unsigned = FALSE, $zerofill = FALSE;
	
	
	public function pre_process() {
	
		// convert from database fields
		
		list($type, $opts) = $this->_parse_type($this->raw_type);
		
		if( strpos($type, ' zerofill') !== FALSE ) {
			$this->zerofill = TRUE;
			$type = str_replace(' zerofill', '', $type);
		}
		
		if( strpos($type, ' unsigned') !== FALSE ) {
			$this->unsigned = TRUE;
			$type = str_replace(' unsigned', '', $type);
		}
		
		$this->type = $type;
		
		switch($type) {
			case 'enum':
			case 'set':
				$trimquotes = function($str) {
					return trim($str, "'");
				};
				$this->choices = array_map($trimquotes, explode(',', $opts));
				break;
			default:
				if( $opts && $this->length == NULL ) {
					$this->length = $opts;
				}
				break;
		}
		
		$this->nullable = ( $this->nullable == '1' );
		
		$this->auto_incr = (bool)DBNav_Text::contains($this->extra, 'auto_increment');
	}
	
	/**
	 * Extracts the text between parentheses, if any
	 *
	 * @param   string
	 * @return  array   list containing the type and length, if any
	 */
	protected function _parse_type($type)
	{
		if (($open = strpos($type, '(')) === FALSE)
		{
			// No length specified
			return array($type, NULL);
		}

		// Closing parenthesis
		$close = strpos($type, ')', $open);

		// Length without parentheses
		$length = substr($type, $open + 1, $close - 1 - $open);

		// Type without the length
		$type = substr($type, 0, $open).substr($type, $close + 1);

		return array($type, $length);
	}
	
	public function type() {
		return $this->type . ( $this->length() ? '(' . $this->length . ')' : '' );
	}
	
	public function length() {
		switch($this->type) {
			case 'enum':
			case 'set':
			case 'mediumtext':
			case 'longtext':
				return NULL;
			case 'decimal':
				return $this->numeric_precision + $this->numeric_scale + 1;
			default:
				return $this->length;
		}
	}

}
