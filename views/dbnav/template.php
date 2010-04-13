<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>DBNav</title>

<link type="text/css" href="http://static.pubjury.local:8088/styles/standard/reset.css" rel="stylesheet" media="screen" />
<link type="text/css" href="http://static.pubjury.local:8088/styles/standard/text.css" rel="stylesheet" media="screen" />
<link type="text/css" href="http://static.pubjury.local:8088/styles/dbnav/dbnav.css" rel="stylesheet" media="screen" />
</head>
<body id="top">
<div id="container">
<div id="header">
<h2>DBNav</h2>
</div>
<div id="nav">
<?=View::factory('dbnav/navigation')->set('schema', (isset($schema)?$schema:NULL))->set('table', (isset($table)?$table:NULL))->render();?>
</div>
<div id="content">
<?=$content;?>
</div>
<div id="footer">
Footer
</div>
</body>
</html>