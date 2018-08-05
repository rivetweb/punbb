<?php

namespace Punbb\SiteIntegration;

define(__NAMESPACE__ . "\ID", "site_integration");
define(__NAMESPACE__ . "\APP", __DIR__ . "/");
//define(__NAMESPACE__ . "\LIB", __DIR__  . "/lib/");
define(__NAMESPACE__ . "\CONFIG", __DIR__  . "/.config/");
define(__NAMESPACE__ . "\URL", $GLOBALS["base_url"] . "/extensions/" . ID . "/");

function UseSiteTemplate() {
	$config = include CONFIG . "site.php";
	if (empty($config["apply_on_admin_area"])) {
		// FORUM_PAGE
		if (FORUM_PAGE_TYPE == "admin-page") {
			return false;
		}
	}
	return true;
}

function FixStyles(&$data, &$options) {
	if (!UseSiteTemplate() || $options["type"] != "url" || strpos($data, "/style/Oxygen/Oxygen.min.css") === false) {
		return;
	}
	$data = URL . "style/Oxygen/Oxygen.min.css";
}

function FixContent(&$content) {
	if (!UseSiteTemplate()) {
		return;
	}
	$config = include CONFIG . "site.php";
	$url = empty($config["page_url"])? "/" : $config["page_url"];
	if (substr($url, 0, 4) != "http") {
		$url = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off"? "https" : "http") . "://" . $_SERVER["SERVER_NAME"] . $url;
	}

	ob_start();

	/*
	// TODO remove cookie for using cached page
	// send with cookies for actual site status (login, etc.)
	$opts = [
		"http" => [
			"method" => "GET",
			"header" => "Cookie: " . http_build_query($_COOKIE, "", ";") . "\r\n"
		]
	];
	$context = stream_context_create($opts);
	$html = file_get_contents($url, false, $context);
	*/
	$ch = curl_init();
	curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => 1,
	]);
	$html = curl_exec($ch);
	curl_close($ch);
	list($header, $footer) = explode("<!-- SPLITCONTENT -->", $html);

	// fetch forum HEAD
	$start = strpos($content, "<head>") + 6;
	$end = strpos($content, "</head>", $start);
	$forumHeader = substr($content, $start, $end - $start);

	// remove TITLE from page
	$start = strpos($header, "<title>");
	$end = strpos($header, "</title>", $start);
	$header = substr($header, 0, $start) . substr($header, $end + 8);

	// insert forum HEAD to page HEAD
	$tmp = explode("</head>", $header);
	$header = $tmp[0] . $forumHeader . "</head>" . $tmp[1];

	// fetch forum BODY
	$start = strpos($content, "<body>") + 6;
	$end = strpos($content, "</body>", $start);
	$forumBody = substr($content, $start, $end - $start);

	$content = $header . $forumBody . $footer;

	ob_end_clean();
}

/*
function FixUserActions() {
	$config = include CONFIG . "site.php";
	if (empty($config["users_from_site"])) {
		return;
	}
	// disable register, login, logout, change email and password from forum pages
	$disable = false;
	$url = "/";
	switch (basename($_SERVER["SCRIPT_FILENAME"])) {
		case "login.php":
			$url = "/login";
			$disable = true;
			break;

		case "register.php":
			$url = "/registration";
			$disable = true;
			break;

		case "profile.php":
			$url = "/profile/profile.html?layout=edit";
			$disable = in_array($_REQUEST["action"], ["change_pass", "change_email"]);
			break;

		default:
			break;
	}
	if ($disable) {
		header("Location: " . $url);
		die;
	}
}

function FixNavlinks(&$links) {
	$config = include CONFIG . "site.php";
	if (empty($config["users_from_site"])) {
		return;
	}
	unset($links["logout"]);
	unset($links["login"]);
	unset($links["register"]);
}

function FixProfilePage(&$forum_page) {
	$config = include CONFIG . "site.php";
	if (empty($config["users_from_site"])) {
		return;
	}
	unset($forum_page["user_options"]["change_password"]);
	unset($forum_page["user_options"]["change_email"]);
}
*/
