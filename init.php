<?php

require_once(MODPATH.'dbnav\classes\dbnav\table\admin.php');
require_once(MODPATH.'dbnav\classes\dbnav\table\editable.php');
require_once(MODPATH.'dbnav\classes\dbnav\table\decorated.php');
require_once(MODPATH.'dbnav\classes\dbnav\table\full.php');

Route::set('dbnav_table', 'dbnav/db/<schema>/tbl/<table>(/<action>)(/<id>)(/page/<page>)')
	->defaults(array(
		'controller'	=> 'dbnav',
		'action'		=> 'table',
		'page'			=> 1,
	));

Route::set('dbnav_schema', 'dbnav(/db/<schema>(/<action>))')
	->defaults(array(
		'controller'	=> 'dbnav',
		'action'		=> 'schemata',
	));