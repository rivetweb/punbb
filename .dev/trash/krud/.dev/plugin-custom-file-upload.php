<?php

// Improved plugin for file uploading - support html5 multiple files uploading
// - Must use prefix for field i.e. somefield_PICTURE | somefield_FILE
// - File names stored as json [{file:, title:, description:}, ...]
// - Uploaded files will not deleted, actually

//! delete

/** Edit fields ending with "_path" by <input type="file"> and link to the uploaded files from select
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, http://www.vrana.cz/
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerFileUploadCustom {

	/** @access protected */
	var $uploadPath, $displayPath, $extensions;

	/**
	* @param string prefix for uploading data (create writable subdirectory for each table containing uploadable fields)
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with allowed file extensions
	*/
	function __construct($uploadPath = "../static/data/", $displayPath = null, $extensions = "[a-zA-Z0-9]+") {
		$this->uploadPath = $uploadPath;
		$this->displayPath = ($displayPath !== null ? $displayPath : $uploadPath);
		$this->extensions = $extensions;
	}

	function head() { ?>

		<script src="/bower_components/sortable/Sortable.min.js"></script>
		<script>
			document.addEventListener("DOMContentLoaded", function () {
				// make sortable uploaded files
				var uploadedFiles = document.getElementById("form-uploaded-files-block");
				var sortable = Sortable.create(uploadedFiles);
				var images = uploadedFiles.querySelectorAll(".uploaded-file-src");
				for (var i = 0, l = images.length; i < l; i++) {
					if (images[i].href.endsWith(".jpg")
							|| images[i].href.endsWith(".png")
							|| images[i].href.endsWith(".gif")) {
						images[i].title = images[i].innerHTML;
						images[i].innerHTML = "<img src='" + images[i].href + "'>"; // TODO use thumb size
					}
				}
			});
		</script>

	<?php }

	function editInput($table, $field, $attrs, $value) {
		if (preg_match('~(.*)_FILE$~', $field["field"], $regs) || preg_match('~(.*)_PICTURE$~', $field["field"], $regs)) {
			$table = ($_GET["edit"] != "" ? $_GET["edit"] : $_GET["select"]);
			$path = "$this->displayPath$table";
			$html = ["<ul id='form-uploaded-files-block'>"];
			foreach (json_decode($value, true) as $i => $file) {
				$html[] = "
					<li class='form-uploaded-files'>
						<a href='" . $path . "/" . $file["file"] . "' class='uploaded-file-src'>" . $file["file"] . "</a>&nbsp;
						<a href='#' onclick='this.parentNode.parentNode.removeChild(this.parentNode);return false;'>&#10006;</a>
						<input type='hidden' name='uploaded_files_" . $field["field"] . "[file][]' value='" . $file["file"] . "'><br>
						<input type='text' name='uploaded_files_" . $field["field"] . "[title][]' value='" . $file["title"] . "' placeholder='title'><br>
						<input type='text' name='uploaded_files_" . $field["field"] . "[descr][]' value='" . $file["descr"] . "' placeholder='description'>
					</li>
				";
			}
			$html[] = "</ul>";
			return implode(" ", $html) . "<br>
				Добавить файлы: <input type='file' name='fields-" . $field["field"] . "[]' multiple='multiple'><br><br>";
		}

	}

	function processInput($field, $value, $function = "") {
		if (preg_match('~(.*)_FILE$~', $field["field"], $regs) || preg_match('~(.*)_PICTURE$~', $field["field"], $regs)) {
			$table = ($_GET["edit"] != "" ? $_GET["edit"] : $_GET["select"]);
			$name = "fields-$field[field]";

			$value = [];
			foreach ($_POST["uploaded_files_" . $field["field"]]["file"] as $i => $v) {
				$value[] = [
					"file" => $_POST["uploaded_files_" . $field["field"]]["file"][$i],
					"title" => $_POST["uploaded_files_" . $field["field"]]["title"][$i],
					"descr" => $_POST["uploaded_files_" . $field["field"]]["descr"][$i],
				];
			}

			$regs2 = [];
			foreach ($_FILES[$name]["error"] as $i => $error) {
				if ($error || !preg_match("~(\\.($this->extensions))?\$~", $_FILES[$name]["name"][$i], $regs2[$i])) {
					continue;
				}
			}
			$path = "$this->uploadPath$table";
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			foreach ($_FILES[$name]["name"] as $i => $fname) {
				//! unlink old
				$filename = $regs[1] . "-" . uniqid() . $regs2[$i][0];
				if (!move_uploaded_file($_FILES[$name]["tmp_name"][$i], $path . "/" . $filename)) {
					continue;
				}
				$value[] = [
					"file" => $filename,
					"title" => "",
					"descr" => "",
				];
			}

			return q(json_encode($value));
		}
	}

	function selectVal($val, &$link, $field, $original) {
		// ???
		if ($val != "&nbsp;" && preg_match('~(.*)_path$~', $field["field"], $regs)) {
			$link = "$this->displayPath$_GET[select]/$regs[1]-$val";
		}
	}

}
