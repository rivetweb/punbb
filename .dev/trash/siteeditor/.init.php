<?php
/*******************************************************************************
 * SiteEditor - simple editor for pages, blocks and widgets
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

namespace SiteEditor;

const ID = "siteeditor";
const APP = __DIR__ . "/";
const LIB = APP . ".lib/";
const DATA = APP . ".data/";
const LAYOUT = APP . ".layout/";
const PARTIAL = APP . ".partial/";
const CONFIG = APP . ".config/";

define(__NAMESPACE__ . "\URL", $base_url . "/extensions/" . ID . "/");

const TYPE_BLOCK = "BLOCK";
const TYPE_CONTENT = "CONTENT";
const TYPE_WIDGET = "WIDGET";

final class ctx {
	static $section;
	static $file;
	static $attribs;
	static $attribsLabels = [
		"title" => "Meta title",
	  "h1" => "H1 title",
	  "meta_keywords" => "Meta keywords",
	  "meta_description" => "Meta description",
	  "layout" => "Page template",
	];
	static $widgets = [
		"panel" => "\SiteEditor\Widgets\Panel",
		"logo" => "\SiteEditor\Widgets\Logo",
	];
	static $pageBlocks = [];
	static $pageContents = [];
}

require LIB . "encoding/php-array.php";
require LIB . "widgets.php";

function PathFilter($path, $onlydots = true) {
	$path = filter_var($path, FILTER_SANITIZE_STRING);
	return $onlydots? str_replace("..", "", $path) : strtr($path, "./\\", "___");
}

function RewriteRules() {
	global $base_url, $forum_rewrite_rules;

	ctx::$section = substr(PathFilter(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)), strlen(parse_url($base_url, PHP_URL_PATH)));
	$dname = DATA . ctx::$section;
	$fname = $dname . "index.html";
	if (!is_readable($fname)) {
		return;
	}
	ctx::$file = $fname;

	// init page attribs
	ctx::$attribs = include $dname . "attribs.php";
	if (empty(ctx::$attribs)) {
		ctx::$attribs = [];
	}
	ctx::$attribs = array_merge([
		"title" => "",
	  "h1" => "",
	  "meta_keywords" => "",
	  "meta_description" => "",
	  "layout" => "",
	], ctx::$attribs);

	// add as first rule for rewrite page
	$forum_rewrite_rules = array_merge([
		"/^" . preg_quote(ltrim(ctx::$section, "/"), "/") . "/i"
			=> 'help.php?section=' . ctx::$section,
	], $forum_rewrite_rules);

	/*
	log($_SERVER["REQUEST_URI"], "REQUEST_URI");
	log(ctx::$section, "site section");
	log(ctx::$file, "page file");
	log(ctx::$attribs, "page attributes");
	echo "<pre>";
	var_dump($forum_rewrite_rules);
	echo "</pre>";
	*/
}

function Init(&$templatePath) {
	if (empty(ctx::$section) || FORUM_PAGE != "help") {
		return;
	}
	if (!empty(ctx::$attribs["layout"])) {
		$template = ctx::$attribs["layout"] . ".tpl";
	} else {
		$template = "page.tpl";
	}
	if (!is_readable(LAYOUT . $template)) {
		$template = "page.tpl";
	}
	$templatePath = LAYOUT . $template;
}

function HeadInit() {
	if (empty(ctx::$section)) {
		return;
	}
	global $forum_head, $forum_user;
	if (!empty(ctx::$attribs["title"])) {
		$forum_head["title"] =
			"<title>" . forum_htmlencode(ctx::$attribs["title"]) . "</title>";
	}
	if (!empty(ctx::$attribs["meta_description"])) {
		$forum_head["descriptions"] =
			'<meta name="description" content="'
				. forum_htmlencode(ctx::$attribs["meta_description"])
			. '" />';
	}
	if (!empty(ctx::$attribs["meta_keywords"])) {
		$forum_head["keywords"] =
			'<meta name="keywords" content="'
				. forum_htmlencode(ctx::$attribs["meta_keywords"])
			. '" />';
	}
	if ($forum_user["g_id"] == FORUM_ADMIN && isset($_GET["edit_mode"])) {
		global $forum_loader;
		$forum_loader->add_css(URL . "css/styles.css", [
			"type" => "url",
			"group" => FORUM_CSS_GROUP_SYSTEM,
			"media" => "screen"
		]);
	}
}

function PageInit() {
	if (empty(ctx::$section)) {
		return;
	}
	global $lang_help;
	$h1 = "";
	if (!empty(ctx::$attribs["h1"])) {
		$h1 = ctx::$attribs["h1"];
	}
	if ($h1 == "" && !empty(ctx::$attribs["title"])) {
		$h1 = ctx::$attribs["title"];
	}
	$lang_help["Help"] = $h1;
}

function PageRender($path) {
	if (empty(ctx::$section)) {
		/*
		if (FORUM_PAGE == "help") {
			// TODO 404 for not found help section
			//		or add to robots.txt `Disallow: /help.php`
			// possible confict with other extensions which use 'he_new_section' hook
			global $lang_common;
			header("HTTP/1.1 404 Not Found");
			message($lang_common["Bad request"]);
		}
		*/
		return;
	}
	if (!empty(ctx::$file)) {
		include ctx::$file;
	}
}

function FooterInit() {
	global $forum_user;
	if ($forum_user["g_id"] == FORUM_ADMIN && isset($_GET["edit_mode"])) {
		global $forum_loader;
		/*
		$forum_loader->add_js('var SiteEditor = {};', [
			"type" => "inline",
			"group" => FORUM_JS_GROUP_SYSTEM
		]);
		*/
		$forum_loader->add_js(URL . "/js/init.js", [
			"async" => false,
			"group" => FORUM_JS_GROUP_SYSTEM
		]);
	}
}

function TagsApply(&$content) {
	if (empty(ctx::$section)) {
		return;
	}
	$content = preg_replace_callback(
		'{#(' . TYPE_CONTENT . '|' . TYPE_BLOCK . '|' . TYPE_WIDGET . ')\-([^#]+)#}',
		function ($m) {
			$type = $m[1];
			$m[2] = PathFilter($m[2], false);
			$fname = "";
			$widgetFn = null;
			switch ($type) {
				case TYPE_CONTENT:
					ctx::$pageContents[$m[2]] = "";
					$fname = DATA . ctx::$section . $m[2] . ".html";
					break;
				case TYPE_BLOCK:
					ctx::$pageBlocks[$m[2]] = "";
					$fname = PARTIAL . $m[2] . ".html";
					break;
				case TYPE_WIDGET:
					if (isset(ctx::$widgets[$m[2]])) {
						$widgetFn = ctx::$widgets[$m[2]];
					}
					break;
				default:
					break;
			}

			global $forum_user;
			ob_start();
			$isEditMode = $forum_user["g_id"] == FORUM_ADMIN
				&& isset($_GET["edit_mode"]) && $m[2] != "panel";
			if ($isEditMode) {
				echo '<div class="site-editor-' . $type . '"'
					. ($type == TYPE_BLOCK || $type == TYPE_CONTENT? ' contenteditable="true"' : '')
					. ' data-id="' . forum_htmlencode($m[2]) . '">';
			}
			if ($type == TYPE_WIDGET) {
				echo is_callable($widgetFn)? $widgetFn() : "[$type:$m[2]]";
			} else {
				if (is_readable($fname)) {
					include $fname;
				} else {
					echo "[$type:$m[2]]";
				}
			}
			if ($isEditMode) {
				echo "</div>";
			}
			return ob_get_clean();
		},
		$content
	);
}

function Save() {
	global $forum_user;
	if ($forum_user["g_id"] != FORUM_ADMIN
			|| $_REQUEST["action"] != "site_editor"
			|| !isset($_POST["action_save_page"])
			|| !isset($_POST["section"])) {
		return;
	}

	$dname = DATA . PathFilter($_POST["section"]);
	if (isset($_POST["attribs"])) {
		// TODO filter attribs
		// http://php.net/manual/ru/function.filter-var-array.php
		\Encoding\PhpArray\Write($dname . "attribs.php", $_POST["attribs"]);
	}

	if (isset($_POST["blocks"])) {
		// TODO filter blocks
		foreach ($_POST["blocks"] as $k => $v) {
			$fname = PARTIAL . PathFilter($k, false) . ".html";
			file_put_contents($fname, $v);
		}
	}

	if (isset($_POST["contents"])) {
		// TODO filter contents
		foreach ($_POST["contents"] as $k => $v) {
			$fname = $dname . PathFilter($k, false) . ".html";
			file_put_contents($fname, $v);
		}
	}

	header("Location: " . $_SERVER["HTTP_REFERER"]);
	exit;
}