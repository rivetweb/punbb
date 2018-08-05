<?php

function adminer_object() {

	final class KrudAdminer extends Adminer {
		public $config;

		// example on delete rows
		/*
		function beforeDelete($table, $queryWhere, $limit = 0) {
			echo "<pre>";

			$sql = "select * from " . $table . " " . $queryWhere;
			var_dump($table, $queryWhere, $sql);
			foreach (get_rows($sql) as $row) {
				// do some with $row
				// in example remove images from $row["PREVIEW_PICTURE"];
				$data = unserialize($row["DESCRIPTION"]);
				print_r($row);
				print_r($data);
			}

			echo "</pre>";

			die;
		}
		*/
	}

	final class KrudEditor extends Adminer {
		public $config;

		function name() {
			// custom name in title and heading
			return "[KRUD]";
		}

		/*
		function credentials() {
			// ODBC user without password on localhost
			return array('localhost', 'ODBC', '');
		}
		*/

		function database() {
			// will be escaped by Adminer
			return $this->config["dbname"];
		}

		/*
		function login($login, $password) {
			// TODO use admin user auth from forum
			// username: 'admin', password: anything
			return ($login == 'admin');
		}
		*/

		function tableName($tableStatus) {
			// tables without comments would return empty string and will be ignored by Adminer
			return h($tableStatus["Comment"]);
		}

		function fieldName($field, $order = 0) {
			// only columns with comments will be displayed
			// table must have at least one column with comment
			// to select properly
			return h($field["comment"]);
		}

		// example on delete rows
		/*
		function beforeDelete($table, $queryWhere, $limit = 0) {
			echo "<pre>";

			$sql = "select * from " . $table . " " . $queryWhere;
			var_dump($table, $queryWhere, $sql);
			foreach (get_rows($sql) as $row) {
				// do some with $row
				// in example remove images from $row["PREVIEW_PICTURE"];
				$data = unserialize($row["DESCRIPTION"]);
				print_r($row);
				print_r($data);
			}

			echo "</pre>";

			die;
		}
		*/
	}

	$path = __DIR__ . "/adminer/plugins/";
	// include plugins
	require $path . "database-hide.php";
	require $path . "dump-json.php";
	require $path . "dump-bz2.php";
	require $path . "dump-zip.php";
	require $path . "dump-xml.php";
	require $path . "dump-alter.php";
	require $path . "json-column.php";
	require $path . "slugify.php";
	require $path . "translation.php";
	require $path . "foreign-system.php";
	require $path . "enum-option.php";
	require $path . "tables-filter.php";
	require $path . "edit-foreign.php";
	// init db config
	require dirname(dirname(__DIR__)) . "/config.php";

	$plugins = [
    new AdminerDatabaseHide(["mysql", "sys", "information_schema", "performance_schema"]),
    new AdminerDumpJson,
    new AdminerDumpBz2,
    new AdminerDumpZip,
    new AdminerDumpXml,
    new AdminerDumpAlter,
    //new AdminerFileUploadCustom($_SERVER["DOCUMENT_ROOT"] . "/upload/", "/upload/"),
    //new AdminerTinymce("/bower_components/tinymce/tinymce.min.js"),
    new AdminerJsonColumn,
    new AdminerSlugify,
    new AdminerTranslation,
    new AdminerForeignSystem,
    new AdminerEnumOption,
    new AdminerEditForeign,
  ];
	if (defined("ADMINER_EDITOR")) {
		//$plugins[] = ...;
		$adminer = new KrudEditor($plugins);
	} else {
		$plugins[] = new AdminerTablesFilter;
		$adminer = new KrudAdminer($plugins);
	}
	$adminer->config = [
		"host" => $db_host,
		"user" => $db_username,
		"password" => $db_password,
		"dbname" => $db_name,
	];

	return $adminer;
}
