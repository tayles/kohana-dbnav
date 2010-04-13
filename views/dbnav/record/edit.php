<?=Form::open();?>

<table>
<thead>
<tr>
<th>Field</th>
<th>Type</th>
<th>Null</th>
<th>Value</th>
<th>Options</th>
</tr>
</thead>
<tbody>
<? foreach( $columns as $column ) : ?>

<tr>
<th><?=Form::label($column->name);?></th>
<td><?=$column->type();?></td>
<td><?=DBNav_Form::render_nullable($column, $record);?></td>
<td>
<?=DBNav_Form::render_column($column, $record);?>
</td>
<td>
<?=DBNav_Form::render_options($column, $record);?>
</td>
</tr>

<? endforeach; ?>
</tbody>
</table>

<p>
When finished, 
<?=Form::label('after_insert', Form::radio('after', 'insert', $after == 'insert', array('id'=>'after_insert')) . ' insert a new row', array('class'=>'inline'));?>
<?=Form::label('after_return', Form::radio('after', 'return', $after == 'return', array('id'=>'after_return')) . ' return to table', array('class'=>'inline'));?>
</p>

<?=Form::button('submit', 'Save', array('type'=>'submit'));?>
 or
<?=Html::anchor('#', 'cancel');?>

<?=Form::close();?>