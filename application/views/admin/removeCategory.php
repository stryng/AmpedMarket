<div class='mainContent'>
Complete the form to remove the category.

<?php echo form_open('admin/category/remove'); ?>

<fieldset>
<?php echo validation_errors(); ?>

<label for='parent'>Category</label> 
<select name='categoryID'>
	<?php foreach ($subCats as $subCat): ?>
		<option value='<?=$subCat['id'];?>'><?=$subCat['name'];?></option>
	<?php endforeach ?>
</select><br />

<br /><br />
<label for="submit"><input type='submit' value='Submit' /></label><br />
</form>
</fieldset>



</div>

