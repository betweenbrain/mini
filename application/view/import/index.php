<p>This is the import view</p>

<form enctype="multipart/form-data" action="<?php echo URL ?>import/upload/" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
	<input name="file" type="file" />
	<input type="submit" value="Upload File" />
</form>