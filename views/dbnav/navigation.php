<ul class="inline">

<li><?=Html::anchor('dbnav', 'List databases');?></li>

<li><?=Html::anchor('dbnav/engines', 'List engines');?></li>
<li><?=Html::anchor('dbnav/variables', 'Database status');?></li>
<li><?=Html::anchor('dbnav/variables', 'Current queries');?></li>
<li><?=Html::anchor('dbnav/variables', 'List variables');?></li>
<li><?=Html::anchor('dbnav/execsql', 'Execute some SQL');?></li>
<li><?=Html::anchor('dbnav/privaledges', 'Users / Privaledges');?></li>
<li><?=Html::anchor('dbnav/triggers', 'Triggers');?></li>
<li><?=Html::anchor('dbnav/storedprocs', 'Stored Procedures / Functions');?></li>

</ul>

<? if( $schema ) : ?>

<ul class="inline">

<? if( $table ) : ?>

<li><?=Html::anchor(array('dbnav', 'db', $schema->name), 'Lists tables in ' . $schema->name);?></li>

</ul>

<ul class="inline">

<li><?=Html::anchor(array('dbnav', 'db', $schema->name, 'tbl', $table->name), 'Browse table');?></li>
<li><?=Html::anchor(array('dbnav', 'db', $schema->name, 'tbl', $table->name, 'columns'), 'Edit columns');?></li>
<li><?=Html::anchor(array('dbnav', 'db', $schema->name, 'tbl', $table->name, 'edit'), 'Edit table');?></li>
<li><?=Html::anchor(array('dbnav', 'db', $schema->name, 'tbl', $table->name, 'indices'), 'View indices');?></li>

</ul>

<ul class="inline">

<li><?=Html::anchor(array('dbnav', 'db', $schema->name, 'tbl', $table->name, 'record'), 'Insert row');?></li>

</ul>

<? endif; ?>

</ul>

<? endif; ?>