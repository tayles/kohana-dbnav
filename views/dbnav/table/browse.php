<? if( count($records) > 0 ) : ?>

<div id="search">
<?=Form::open('dbnav/db/' . $schema->name . '/tbl/' . $table->name . '/search', array('method' => 'get'));?>
<input type="text" id="q" name="q" />
<button type="submit">Search</button>
<br />
<small><em>Separate multiple terms with a comma, no need to quote your search term. Possible operators include =, <, >, like, regexp (leave blank to do an equality)... e.g. "username tayles" or "id = 53534, town like readi%"</em></small>
</form>
</div>


<?=$pagination->render();?>
<div class="tbl">
<?=$tbl_view->render();?>
</div>
<?=$pagination->render();?>
<? else : ?>
<p><em>No records found</em></p>
<? endif; ?>