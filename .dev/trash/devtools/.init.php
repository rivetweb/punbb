<?php

namespace DevTools;

const ID = "devtools";
const APP = __DIR__ . "/";

define(__NAMESPACE__ . "\EXTENSIONS", dirname(__DIR__) . "/");

function dbg($message, $group = "PHP: ") {
	echo "
		<script>
			if (console != \"undefined\" && console.log != \"undefined\") {
				console.log(\"$group:\", "
					. (($jsobj = json_encode($message))? $jsobj : "\"\"")
				. ");
			}
		</script>
	";
	return 1;
}

function StripQuotes($s) {
	return $s != "" && ($s[0] == '"' || $s[0] == "'")?
		substr($s, 1, -1) : $s;
}

function ParseFile($fname, $level = 0) {
	if (!file_exists($fname)) {
		return;
	}
	$forumConsts = [
		"FORUM_ROOT" => 1,
		"FORUM_CACHE_DIR" => 1,
		"UTF8" => 1,
	];
	$showParent = true;
	$includeOn = false;
	$includeName = [];
	foreach (token_get_all(file_get_contents($fname)) as $token) {
		if (is_array($token)) {
			list($id, $text, $line) = $token;
			switch ($id) {
				case T_COMMENT:
				case T_DOC_COMMENT:
					break;

				case T_INCLUDE:
				case T_INCLUDE_ONCE:
				case T_REQUIRE:
				case T_REQUIRE_ONCE:
					$includeOn = true;
					$includeName = [];
					break;

				default:
					if ($includeOn) {
						$includeName[] = StripQuotes(trim($text));
					} /*else if ($id == T_STRING && $text == "get_hook") {
						$callOn = true;
					} else if ($callOn && $id == T_CONSTANT_ENCAPSED_STRING) {
						if ($showHooks) {
							dbg(StripQuotes($text), "HOOK");
						}
						$callOn = false;
					}*/
					break;
			}
		} else if ($token == ";" && !empty($includeName)) {
			$includeResult = [];
			foreach (array_filter($includeName) as $v) {
				if (isset($forumConsts[$v])) {
					$v = constant($v);
				}
				if ($v == "./") {
					//$v = dirname($_SERVER["SCRIPT_FILENAME"]);
				}
				if (strpos($v, '$') !== false) {
					// TODO init subst variables
					$includeResult = [];
					break;
				}
				$includeResult[] = $v;
			}
			if (!empty($includeResult)) {
				$includeFile = implode("/", $includeResult);
			} else {
				$includeFile = implode("/", $includeName);
			}
			dbg(str_repeat("    ", $level) . " ⊢ " . $includeFile, "");
			if (!empty($includeResult)) {
				ParseFile($includeFile, $level + 1);
			}
			$includeOn = false;
			$includeName = [];
		}
	}
}

function DebugInfo() {
	ob_start();

	$fname = $_SERVER["SCRIPT_FILENAME"];
	dbg($fname, "SCRIPT");

	if (isset($_GET["show_includes"])) {
		ParseFile($fname);
	}

	if (isset($_GET["show_vars"])) {
		$debugVar = $_GET["show_vars"];
		if ($debugVar == "") {
			foreach (array_keys($GLOBALS) as $v) {
				dbg($v, "");
			}
		} else if ($debugVar == "*") {
			foreach ($GLOBALS as $k => $v) {
				dbg($v, $k);
			}
		} else {
			dbg($GLOBALS[$debugVar], $debugVar);
		}
	}

	if (isset($_GET["show_hooks"])) {
		global $forum_debug_hooks;
		foreach ($forum_debug_hooks as $hookId) {
			dbg($hookId, "HOOK");
		}
	}

	global $forum_db, $forum_start, $tpl_main;
	$totalTime = forum_microtime() - $forum_start;
	if (isset($_GET["show_sql"])) {
		$sqlTime = 0.0;
		foreach ($forum_db->get_saved_queries() as $query) {
			dbg($query[0], $query[1]);
			$sqlTime += $query[1];
		}
		dbg($sqlTime, "SQL");
		dbg($totalTime - $sqlTime, "FORUM");
	}
	dbg($totalTime, "TOTAL");

	$tpl_main .= ob_get_clean();
}

// process all installed and not disabled extensions
global $forum_db;
$extensionsResult = $forum_db->query_build([
	"SELECT"	=> "e.id",
	"FROM"		=> "extensions AS e",
	"WHERE"		=> "e.disabled = 0"
]) or error(__FILE__, __LINE__);
while ($row = $forum_db->fetch_assoc($extensionsResult)) {
	$extensionId = $row["id"];
	if ($extensionId == "." || $extensionId == ".."
			|| !is_dir(EXTENSIONS . $extensionId)
			|| !is_dir(EXTENSIONS . $extensionId . "/.hooks")) {
		continue;
	}
	// process all hooks files of extension
	$hooksPath = EXTENSIONS . $extensionId . "/.hooks";
	if ($hooksDir = opendir($hooksPath)) {
		while (($hookFile = readdir($hooksDir)) !== false) {
			if ($hookFile == "." || $hookFile == ".."
					|| $hookFile == "es_essentials.php"
					|| substr($hookFile, -4) != ".php"
					|| is_dir($hooksPath . "/" . $hookFile)) {
				continue;
			}
			// get actual hook code
			$hookCode = file_get_contents($hooksPath . "/" . $hookFile);
			if (substr($hookCode, 0, 5) == "<?php") {
				$hookCode = substr($hookCode, 5);
			}
			// find and replace hook code for extension to actual version
			$hookId = basename($hookFile, ".php");
			$hookFound = false;
			if (!empty($forum_hooks[$hookId])) {
				foreach ($forum_hooks[$hookId] as $i => $v) {
					if (strpos($v, "'ext_info_stack'") !== false
							&& strpos($v, "=> '" . $extensionId . "'") !== false) {
						$hookFound = true;
						if (isset($_GET["show_liveupdate_hooks"])) {
							dbg("Replace $hookId for $extensionId", "HOOKS");
						}
						$forum_hooks[$hookId][$i] = $hookCode;
					}
				}
			}
			if (!$hookFound) {
				if (isset($_GET["show_liveupdate_hooks"])) {
					dbg("Add $hookId for $extensionId", "HOOKS");
				}
				$forum_hooks[$hookId][] = $hookCode;
			}
		}
		closedir($hooksDir);
	}
}
