<?php

global $base_url, $forum_user;

if ($forum_user["g_id"] == FORUM_ADMIN) {
	$lang_admin_common["krud_adminer"] = "Krud Adminer";

	$forum_page["admin_menu"]["krud_adminer"] = '<li class="'
		. ((FORUM_PAGE_SECTION == "krud_adminer") ? "active" : "normal")
		. ((empty($forum_page["admin_menu"])) ? " first-item" : "")
		. '"><a href="' . $base_url . '/extensions/krud/adminer/adminer/custom.php"><span>'
		. $lang_admin_common["krud_adminer"]
		. "</span></a></li>";
}

$lang_admin_common["krud_editor"] = "Krud Editor";

$forum_page["admin_menu"]["krud_editor"] = '<li class="'
	. ((FORUM_PAGE_SECTION == "krud_editor") ? "active" : "normal")
	. ((empty($forum_page["admin_menu"])) ? " first-item" : "")
	. '"><a href="' . $base_url . '/extensions/krud/adminer/editor/custom.php"><span>'
	. $lang_admin_common["krud_editor"]
	. "</span></a></li>";
