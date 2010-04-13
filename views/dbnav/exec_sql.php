Execute arbitrary sql

<?=Form::open();?>

<?=Form::textarea('sql', $form['sql'], array('rows'=>10,'cols'=>50));?>

<?=Form::button('submit', 'Submit', array('type'=>'submit'));?>

<?=Form::close();?>

Display the run sql command

Display the results table

Show export options