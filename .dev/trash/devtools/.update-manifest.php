<?php

$path = getcwd();

echo "Processing " . $path . "...\n";

$hooksPath = $path . "/.hooks";
$hooksXml = [];
if ($hooksDir = opendir($hooksPath)) {
	while (($hookFile = readdir($hooksDir)) !== false) {
		if ($hookFile == "." || $hookFile == ".."
				|| is_dir($hooksPath . "/" . $hookFile)) {
			continue;
		}
		$hookCode = file_get_contents($hooksPath . "/" . $hookFile);
		if (substr($hookCode, 0, 5) == "<?php") {
			$hookCode = substr($hookCode, 5);
		}
		$hooksXml[] = '
			<hook id="' . basename($hookFile, ".php") . '"><![CDATA[
				' . $hookCode . '
			]]></hook>
		';
	}
	closedir($hooksDir);
}

if (count($hooksXml) == 0) {
	echo "No hooks in extension.\n";
	return;
}

$manifest = file_get_contents($path . "/manifest.xml");
$manifest = preg_replace(
	"{<hooks>.+?</hooks>}s",
	"<hooks>" . implode("\n", $hooksXml) . "</hooks>",
	$manifest
);
file_put_contents($path . "/manifest.xml", $manifest);
