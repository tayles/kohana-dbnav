<?php defined('SYSPATH') or die('No direct script access.');

class DBNav_Form extends Model {

	public static function get_value($column, $record = NULL) {
		if( isset($record) ) {
			return $record->{$column->name};
		}
		else if( $column->default !== NULL ) {
			return $column->default;
		}
		return NULL;
	}

	public static function render_column($column, $record = NULL) {
	
		$html = '';
		$attribs = array();
		
		$val = self::get_value($column, $record);
		
		
		switch( $column->type ) {
			case 'set':
			case 'enum':
				$choices = $column->choices;
				if( $column->nullable ) {
					$choices = array_merge(array(''), $choices);
				}
				$html = Form::select($column->name, $choices, $column->default);
				break;
			case 'timestamp':
			case 'datetime':
			case 'date':
			case 'time':
				$attribs['class'] = $column->type;
				$html = Form::input($column->name, $val, $attribs) . '(clock)';
				break;
			case 'text':
			case 'mediumtext':
			case 'longtext':
			case 'blob':
			case 'mediumblob':
				$attribs['rows'] = 3;
				$attribs['cols'] = 70;
				$html = Form::textarea($column->name, $val, $attribs);
				break;
			default:
			
				if( $column->length() ) {
					$attribs['size'] = min( 70, $column->length() );
					$attribs['maxlength'] = $column->length();
				}
			
				$html = Form::input($column->name, $val, $attribs);
				break;
		}
		
		// heuristic based inputs
		if( $column->type == 'tinyint' && $column->length == 1 ) {
			$html = Form::label($column->name . '_yes', Form::radio($column->name, '1', $val == 1, array('id'=>$column->name . '_yes')) . ' yes', array('class'=>'inline')) . ' ' . Form::label($column->name . '_no', Form::radio($column->name, '0', $val == 0, array('id'=>$column->name . '_no')) . ' no', array('class'=>'inline'));
		}
		
		return $html . Kohana::debug($attribs);
	}
	
	public static function render_nullable($column, $record = NULL) {
		if( $column->nullable ) {
			$isnull = FALSE;
			if( isset($record) ) {
				$val = $record->{$column->name};
				$isnull = is_null($val);
			}
			else {
				$isnull = is_null($column->default);
			}
			
			return Form::checkbox($column->name . '_null', 1, $isnull, array('class'=>'nullval'));
		}
	}
	
	public static function render_options($column, $record = NULL) {
		$val = self::get_value($column, $record);
		
		$html = '';
		
		switch( $column->type ) {
			case 'timestamp':
			case 'datetime':
			case 'date':
			case 'time':
				$html = Html::anchor('#', 'now()');
				break;
		}
		
		if( Text::contains($column->name, array('lat', 'latitude')) ) {
			$html = Html::anchor('#', 'map');
		}
		
		return $html;
	}
	
}
