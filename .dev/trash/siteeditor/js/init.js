/*******************************************************************************
 * SiteEditor - simple editor for pages, blocks and widgets
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

SiteEditor = (function (app) {
	"use strict";

	let $panel = document.querySelector(".site-editor-panel");
	if (!$panel) {
		return;
	}

	//for (let $el of document.querySelectorAll(".site-editor-BLOCK,.site-editor-CONTENT")) {
		//...
	//}
	for (let $el of document.querySelectorAll(".site-editor-WIDGET")) {
		// TODO init onclick for widgets
	}

	let $form = document.querySelector(".site-editor-form");

	$form.querySelector(".btn-apply").addEventListener("click", function (e) {
		for (let $el of document.querySelectorAll(".site-editor-BLOCK")) {
			let $input = $form.querySelector(
				".form-input-block-" + $el.getAttribute("data-id"));
			$input.value = $el.innerHTML;
		}
		for (let $el of document.querySelectorAll(".site-editor-CONTENT")) {
			let $input = $form.querySelector(
				".form-input-content-" + $el.getAttribute("data-id"));
			$input.value = $el.innerHTML;
		}
		//e.preventDefault();
	});

	return app;
})((typeof SiteEditor !== "undefined")? SiteEditor : {});
