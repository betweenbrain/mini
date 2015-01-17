<?php

/**
 * File       import.php
 * Created    1/17/15 4:35 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2015 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v2 or later
 */
class Import extends Controller
{

	public $data = null;

	public function index()
	{
		// load views. within the views we can echo out $songs and $amount_of_songs easily
		require APP . 'view/_templates/header.php';
		require APP . 'view/import/index.php';
		require APP . 'view/_templates/footer.php';
	}

	public function upload()
	{

		if (array_key_exists('file', $_FILES))
		{
			$file = $_FILES['file']['tmp_name'];

			$this->data = $this->model->parseCSV($file);
		}

		require APP . 'view/_templates/header.php';
		require APP . 'view/import/results.php';
		require APP . 'view/_templates/footer.php';

	}

	public function export()
	{
		$this->model->writeCSV($this->data);
	}
}