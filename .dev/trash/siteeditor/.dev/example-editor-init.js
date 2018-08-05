
// example for integration TinyMCE
app.initContentEditor = function(params) {
	var path = '/vendor/assets/responsive-filemanager';
	var defaultParams = {
		/*setup: function (ed) {
			ed.on('change', function(e) {
				console.log('content was changed');
			});
		},*/
		filemanager_title: 'Файл-менеджер',
		external_filemanager_path: path + '/filemanager/',
		external_plugins: {
			'filemanager': path + '/filemanager/plugin.min.js'
		},
		language: 'ru',
		convert_urls: false
	};
	tinymce.init($.extend(defaultParams, params));
};

// example for add button to TinyMCE
app.initFrontEditPluginTinyMCE = function () {
	tinymce.PluginManager.add('frontedit', function(editor, url) {
		var saveContent = function() {
			$.post(app.baseUrl + 'block/', {
				template: (typeof SiteTemplatePath !== 'undefined')? SiteTemplatePath : '',
				current: location.pathname,
				id: $(editor.getElement()).attr('data-inc-block'),
				content: editor.getContent()
			})
			.then(function(data) {
				if (data.indexOf('error:') >= 0) {
					alert('Не удалось сохранить изменения.');
				}
			});
		};

		editor.addButton('savecontent', {
			//text: 'Сохранить',
			title: 'Сохранить',
			icon: 'save',
			onclick: saveContent
		});

		/*
		editor.addButton('contentparams', {
			//editor.addMenuItem('contentparamsedit', {
			text: '...',
			title: 'Параметры',
			//context: 'tools',
			//icon: 'setting',
			onclick: contentParams
		});
		*/
	});
};

// example create page
app.createPage = function (parent) {
	var pageTitle = prompt('Введите название');
	if ($.trim(pageTitle) != '') {
		$.post(app.baseUrl + 'attribs/', {
			'new': 1,
			parent: parent,
			name: pageTitle,
			current: location.pathname + app.translit(pageTitle, true)
		})
		.then(function(data) {
			if (data == 'error:Page exists') {
				alert('Страница уже существует.');
			}
			console.log(data);
		});
	}
};

// example init edit page
app.initEditor = function () {
	// init blocks
	$(app.editableBlocks).addClass('site-editor-block');

	// init editors
	app.initFrontEditPluginTinyMCE();
	app.initContentEditor({
		selector: app.editableBlocks,
		inline: true,
		plugins: [
				'advlist autolink lists link image charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table contextmenu paste'
				// custom plugins
				+ ' frontedit'
				+ ' responsivefilemanager'
		],
		toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image '
			// custom buttons
			+ ' responsivefilemanager '
			+ '| savecontent'
	});

	// init menu
	$('body').append('<div id="site-editor-content-menu">\
		<ul class="fa-ul">\
			<li><i class="fa-li fa fa-plus"></i><a class="btn-site-editor-new" href="#" title="Создать страницу в текущем разделе">Создать страницу</a></li>\
			<li><i class="fa-li fa fa-plus"></i><a class="btn-site-editor-new-category" title="Создать подраздел в текущем разделе" href="#">Создать раздел</a></li>\
			<li><i class="fa-li fa fa-edit"></i><a class="btn-site-editor-attribs site-editor-attribs_open" href="#" title="Свойства страницы">Свойства</a></li>\
			<li><i class="fa-li fa fa-cogs"></i><a class="btn-site-editor-settings" href="#" title="Настройки раздела">Настройки</a></li>\
			<li><i class="fa-li fa fa-picture-o"></i><a class="btn-site-editor-manager" href="#" title="Управление изображениями и файлами">Файлы</a></li>\
			</ul>\
	</div>');
	$.getJSON(app.baseUrl + 'attribs/', {
		current: location.pathname,
		t: new Date().getTime()
	}).then(function (data) {
		if (data.parent != 1) {
			$('.btn-site-editor-new-category').closest('li').hide();
			$('.btn-site-editor-new').closest('li').hide();
		}
	});

	// init properties form
	app.initAttribsForm();

	// init buttons
	$('.btn-site-editor-new').click(function () {
		app.createPage(0);
		return false;
	});
	$('.btn-site-editor-new-category').click(function () {
		app.createPage(1);
		return false;
	});

	$('.btn-site-editor-manager').click(function () {
		$(this).attr('target', '_blank');
		$(this).attr('href', app.filemanagerUrl + '?current=' + location.pathname);
		//return false;
	});

	$('.btn-site-editor-settings').click(function () {
		return false;
	});
};

// example load css
app.loadCss = function (url) {
	//$('head').append('<link href="' + url + '" rel="stylesheet" />');
	var link = document.createElement('link');
	link.setAttribute('rel', 'stylesheet');
	link.type = 'text/css';
	link.href = url;
	document.head.appendChild(link);
};

// example load js
app.loadJs = function (url) {
	var js = document.createElement('script');
	js.type = 'text/javascript';
	js.src = url;
	document.body.appendChild(js);
};
