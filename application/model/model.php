<?php

class Model
{

	/**
	 * For column name mapping
	 *
	 * @var null
	 */
	private $columnMap = null;

	/**
	 * @param object $db A PDO database connection
	 */
	function __construct($db)
	{
		try
		{
			$this->db = $db;
		} catch (PDOException $e)
		{
			exit('Database connection could not be established.');
		}

		/**
		 * Force database table creation
		 */
		$sql   = "CREATE TABLE IF NOT EXISTS `tmp` (
					`id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`firstName`         varchar(255)      NOT NULL,
					`lastName`      varchar(255)       NOT NULL,
					`email`      varchar(255)       NOT NULL,
					`firstDate`      varchar(255)       NOT NULL,
					`lastDate`      varchar(255)       NOT NULL,
					PRIMARY KEY (`id`)
					)
				ENGINE =InnoDB
				AUTO_INCREMENT =0
				DEFAULT CHARSET =utf8;";
		$query = $this->db->prepare($sql);
		$query->execute();
	}

	/**
	 * Returns a string in camelCase
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	private function camelCase($string)
	{
		// Process any already camel cased strings, while avoiding all caps words/acronyms
		$str = preg_replace('/^\b([A-Z])/', ' $1', $string);
		// Make sure that all words are upper case, but other letters lower
		$str = ucwords(strtolower($str));
		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/[^A-Za-z0-9]/', '', $str);
		// Trim whitespace and lower case first String
		$str = trim(lcfirst($str));

		return $str;
	}

	/**
	 * Deletes a record
	 *
	 * @param $id
	 */
	private function deleteRow($id)
	{
		$sql        = "DELETE FROM tmp WHERE id = :id";
		$query      = $this->db->prepare($sql);
		$parameters = array(':id' => $id);

		// useful for debugging: you can see the SQL behind above construction by using:
		// echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

		$query->execute($parameters);
	}

	/**
	 * Returns all of the records in the tmp table
	 *
	 * @return mixed
	 */
	private function getAllRecords()
	{
		$sql   = "SELECT id, firstName, lastName, email, firstDate, lastDate FROM tmp";
		$query = $this->db->prepare($sql);
		$query->execute();

		return $query->fetchAll();
	}

	/**
	 * Returns an array of data from a CSV file
	 *
	 * @param $file
	 *
	 * @return array
	 */
	private function getCsvData($file)
	{
		return array_map('str_getcsv', file($file));
	}

	/**
	 * Read the first row of a CSV to create a name based mapping of column values
	 *
	 * @param $csvfile
	 *
	 * @return mixed
	 */
	private function mapColumnNames($csvfile)
	{
		$return = new stdClass;

		foreach ($csvfile[0] as $key => $value)
		{
			$return->{$this->camelCase($value)} = $key;
		}

		return $return;
	}

	/**
	 * Parses and formats data in the CSV file
	 *
	 * @param $file
	 *
	 * @return mixed
	 */
	public function parseCSV($file)
	{
		$rows            = $this->getCsvData($file);
		$this->columnMap = $this->mapColumnNames($rows);

		// Remove header row
		array_shift($rows);

		foreach ($rows as $key => $row)
		{

			$email = strtolower($row[$this->columnMap->email]);

			$firstName = ucfirst(strtolower($row[$this->columnMap->firstname]));
			$lastName  = ucfirst(strtolower($row[$this->columnMap->lastname]));

			$firstDate = $row[$this->columnMap->firstdate];
			$lastDate  = $row[$this->columnMap->lastdate];

			$return[$key]['firstName'] = $firstName;
			$return[$key]['lastName']  = $lastName;
			$return[$key]['email']     = $email;
			$return[$key]['firstDate'] = $firstDate;
			$return[$key]['lastDate']  = $lastDate;

			$this->writeRecord($firstName, $lastName, $email, $firstDate, $lastDate);
		}

		return $return;
	}

	/**
	 * Splits the persons name and formats the result
	 *
	 * @param $name
	 *
	 * @return stdClass
	 */
	private function splitName($name)
	{
		$parts = explode(',', $name);

		$return            = new stdClass;
		$return->firstName = ucfirst(strtolower(trim($parts[1])));
		$return->lastName  = ucfirst(strtolower(trim($parts[0])));

		return $return;
	}

	/**
	 * Forces a CSV file download and deletes tmp records
	 */
	public function writeCSV()
	{

		$data = $this->getAllRecords();

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=file.csv");
		// Disable caching
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
		header("Pragma: no-cache"); // HTTP 1.0
		header("Expires: 0"); // Proxies

		// create a file pointer connected to the output stream
		$output = fopen("php://output", "w");

		foreach ($data[0] as $name => $value)
		{
			$header[] = $name;
		}

		// output the column headings
		fputcsv($output, (array) $header);

		foreach ($data as $row)
		{
			fputcsv($output, (array) $row);

			// Delete record from tmp table
			$this->deleteRow($row->id);
		}

		fclose($output);

	}

	/**
	 * Writes a record to the database
	 *
	 * @param $firstName
	 * @param $lastName
	 * @param $email
	 */
	private function writeRecord($firstName, $lastName, $email, $firstDate, $lastDate)
	{
		$sql        = "INSERT into tmp (firstName, lastName, email, firstDate, lastDate) VALUES (:firstName, :lastName, :email, :firstDate, :lastDate)";
		$query      = $this->db->prepare($sql);
		$parameters = array(':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':firstDate' => $firstDate, ':lastDate' => $lastDate);

		// useful for debugging: you can see the SQL behind above construction by using:
		// echo '[ PDO DEBUG ]: ' . Helper::debugPDO($sql, $parameters);  exit();

		$query->execute($parameters);
	}
}
