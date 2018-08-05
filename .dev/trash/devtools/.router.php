<?php

// handle only for php embedded server
if (php_sapi_name() != "cli-server") {
	return;
}

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) {
	return false;
}

include $_SERVER["DOCUMENT_ROOT"] . "/rewrite.php";
