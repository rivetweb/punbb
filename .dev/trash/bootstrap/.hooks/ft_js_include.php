<?php

$forum_loader->add_js(\Bootstrap\URL . "js/jquery.min.js", [
	"async" => false,
	"group" => FORUM_JS_GROUP_SYSTEM
]);

$forum_loader->add_js(\Bootstrap\URL . "js/tether.min.js", [
	"async" => false,
	"group" => FORUM_JS_GROUP_SYSTEM
]);

$forum_loader->add_js(\Bootstrap\URL . "js/bootstrap.min.js", [
	"async" => false,
	"group" => FORUM_JS_GROUP_SYSTEM
]);
