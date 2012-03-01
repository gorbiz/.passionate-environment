<html>
<head>
	<title>.passionate-environment</title>

<?php

$available_themes = array(
	"clouds",
	"clouds_midnight",
	"cobalt",
	"crimson_editor",
	"dawn",
	"eclipse",
	"idle_fingers",
	"kr_theme",
	"merbivore",
	"merbivore_soft",
	"mono_industrial",
	"monokai",
	"pastel_on_dark",
	"twilight",
	"vibrant_ink");

$theme = "twilight";
if (isset($_GET['theme']) && in_array($_GET['theme'], $available_themes)) {
	$theme = $_GET['theme'];
}

?>

	<script src="http://www.passionismandatory.com/libs/ace/ace-uncompressed.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/mode-html.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/mode-css.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/mode-javascript.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/theme-<?php echo $theme; ?>.js" type="text/javascript" charset="utf-8"></script>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js" type="text/javascript"></script> 
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js" type="text/javascript"></script> 
	
	<script type="text/javascript">
		
		/**
		 * Some options,
		 * all are to be passed via the URL.
		 * 
		 * Edit a specific file:
		 * &file=<filename>
		 * 
		 * Hide the editor on load (fullscreen mode):
		 * &fullscreen=yes
		 * Exit fullscreen mode by mousing over left edge of the screen.
		 *
		 * Turn off the html valiator:
		 * &validator_url=none
		 *
		 * Specify a html validator to use (compatible with validator.nu):
		 * &validator_url=<url to validator>
		 * Example: &validator_url=http://localhost:8888
		 */

		function getUrlVars() {
			var vars = {};
			var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
				vars[key] = value;
			});
			return vars;
		}
		function getUnknownUrlVarsAsString() {
			var unknown = {};
			var vars = getUrlVars();
			for (key in vars) {
				if (key != 'validator_url' && key != 'file' && key != 'fullscreen') {
					unknown[key] = vars[key];
				}
			}
			var tmp = [];
			for (key in unknown) {
				tmp.push(key + '=' + unknown[key]);
			}
			return tmp.join('&');
		}

		var file_to_edit;
		if (getURLParameter('file') != 'null') {
			file_to_edit = getURLParameter('file');
		} else {
			$.get('find_file.php', {nocache: new Date().getTime()}, function(data) {
				file_to_edit = data;
				setup_editor_for(file_to_edit);
			});
		}

		/*
		 * Instead of simply rendering the file you are editing in the
		 * right hand side iframe you can render any URL you want.
		 * Useful when editing CSS, JavaScript or when deling with
		 * front controlled systems like Drupal or WordPress.
		 */
		var render_url = null;
		if (getURLParameter('render_url') != 'null') {
			render_url = decodeURIComponent(getURLParameter('render_url'));
		}

		var show_only_page_on_load = (getURLParameter('fullscreen') != 'null');

		var run_delay = 1000;

		var project_url = get_project_url();

		var validator_url = "http://validator.nu/";
		if (getURLParameter('validator_url') != 'null') {
			// TODO Maybe this could be stored in a cookie for convenience?
			validator_url = getURLParameter('validator_url');
		}
		/*
		 * TODO else if on localhost/local network check if localhost:8888
		 * appears to be a validator, if so; use it.
		 * if not, show a friendly warning.
		 */

		function get_project_url() {
			var clear_url = window.location.href.split('?')[0].slice(0, -1);
			return clear_url.substr(0, clear_url.lastIndexOf('/') + 1);			
		}

		function getURLParameter(name) {
			return decodeURI(
				(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
			);
		}


		
		var editor;
		window.onload = function() {
			editor = ace.edit("editor");
			editor.setTheme("ace/theme/<?php echo $theme; ?>");
			
			editor.setShowPrintMargin(false);
			
			// Without this, hitting the home button doesn't scroll all the way 
			editor.renderer.setPadding(0);
			
			if (file_to_edit) {
				setup_editor_for(file_to_edit);
			}
			
			if (show_only_page_on_load) {
				show_only_page();
			}
			
			check_for_updates();
			
			
						
			$("#errors").mouseover(function() {
				show_errors();
			});
			$("#errors").mouseout(function() {
				hide_errors();
			});			
			
			$("#output").mouseover(function() {
				show_output();
			});
			$("#output").mouseout(function() {
				hide_output();
			});
			
			$("iframe#iframe").load(function() {
				update_errors();
			});
			
			// FIXME When going right, toggling browser fullscreen and then going left the editor is all white (resizing the window brings it's content back...)
			$("#toggle-right").mouseover(function() {
				console.log('RIGHT');
				show_only_page();
			});

			$("#toggle-left").mouseover(function() {
				console.log('LEFT');
				show_normal_view();
			});

		};
		
		function show_only_page() {
			$("#editor").css("width", 0);
			$("#iframe").css("left", 1);
			$("#iframe").css("width", 1679);			
		}
		
		function show_normal_view() {
			$("#editor").css("width", 631);
			$("#iframe").css("left", 632);
			$("#iframe").css("width", 1047);
			editor.resize();
		}
		
		function setup_editor_for(file) {
			if ('php' == get_file_extenstion(file).toLowerCase()) {
				var mode = require("ace/mode/php").Mode;
			} else if ('css' == get_file_extenstion(file).toLowerCase()) {
				var mode = require("ace/mode/css").Mode;
			} else if ('js' == get_file_extenstion(file).toLowerCase()) {
				var mode = require("ace/mode/javascript").Mode;
			} else {
				var mode = require("ace/mode/html").Mode;
			}
			editor.getSession().setMode(new mode());

			refresh_page();
			$.get('get.php', {file: file, nocache: new Date().getTime()}, function(data) {
				editor.getSession().setValue(data);
				// XXX I put this in here so that the editor will not save the file just after loading it...
				editor.getSession().on('change', function() {
					code_changed();
					run_unintrusive();
				});
				editor.getSession().selection.on('changeCursor', abort_run);
			});
		}
		
		function get_file_extenstion(filename) {
			return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename)[0] : undefined;
		}
		
		var code_has_changed = false;
		function code_changed() {
			code_has_changed = true;
		}
		
		var run_unintrusive_timer;
		function run_unintrusive() {
			console.log('run_unintrusive()');
			clearTimeout(run_unintrusive_timer);
			run_unintrusive_timer = setTimeout(run, run_delay);
		}
		
		function run() {
			code_has_changed = false;
			console.log('run()');
			var data = editor.getSession().getValue();
			$.post("put.php", { file: file_to_edit, data: data }, function(data) {
				if (data.length) {
					alert("Got an unexpected response: " + data);
					return false;
				} else {
					refresh_page();
				}
			});
		}
		
		function refresh_page() {
			var passedParams = '';
			if (getUnknownUrlVarsAsString()) {
				passedParams = '&' + getUnknownUrlVarsAsString();
			}
			if (render_url) {
				// FIXME Serious issue we don't know if the URL has a query part or not, we simply assume so... This is something that will fail 50% of the time or so...
				$("#iframe").attr('src', render_url + '&random=' + (new Date().getTime()) + passedParams);
			} else {
				$("#iframe").attr('src', '../' + file_to_edit + '?random=' + (new Date().getTime()) + passedParams);
			}
			//$("#iframe").contentWindow.location.reload();
			//document.getElementById("iframe").contentDocument.location.href = 'src', '../' + file_to_edit;
			//document.getElementById("iframe").contentDocument.location.reload(true);
		}

		function abort_run() {
			clearTimeout(run_unintrusive_timer);
			if (code_has_changed) {
				run_unintrusive_timer = setTimeout(run, run_delay);
			}
		}
		
		function update_output() {
			$.get('output.log', function(data) {
				$("#output").html(data);
				if (data.length) {
					//show_output();
				} else {
					hide_output();
				}
			});
		}
		
		function update_errors() {
			if (validator_url == 'none') return;
			validate_html5(project_url + '/' + file_to_edit, function(data) {
				$("#errors-html").html(format_errors(data));
				if (data.length) {
					show_errors();
				} else {
					hide_errors_if_empty();
				}
			});		
		}

		function validate_html5(url, callback) {
			console.log('validate_html5()');
			$.ajax({
				url: validator_url,
				data: { doc: url, out: 'json', nocache: new Date().getTime() },
				success: function(result) {
					// FIXME Do propper error handling here!
					if (result.messages[0].type == 'non-document-error') {
						alert('Failed to validate, maybe you are using an external validator from localhost?');
						return;
					}
					callback(
						result.messages
						.filter(function(message) { return message.type != 'info'; })
						// Ignore these messages about meta tags as the validator fails fo validate them correctly
						.filter(function(message) { return !/Attribute .?property.? not allowed on element .?meta.?/.test(message.message); })
						.filter(function(message) { return !/Element .?meta.? is missing one or more of the following attributes/.test(message.message); })
						.map(function(message) {
								return "<strong>" + message.type.charAt(0).toUpperCase() + message.type.substr(1) + ":</strong>" + message.message + "<br />" 
									+ "From line " + message.firstLine + ", column" + message.firstColumn + "; to line " + message.lastLine + ", column " + message.lastColumn + "<br />"
									// Lazy man's encode html entities
									+ "<strong>" + $("<div/>").text(message.extract).html() + "</strong><br />";
						}).join('<br />'));
				},
				error: function(error) { alert("Failed to validate html"); }
			});
		}
						
		// Makes goto line links in texts like 'File "test.py", line 156, in'
		function format_errors(errors) {
			errors = errors.replace(/line\s([0-9]+)/gi, "<a href=\"javascript:gotoLine($1);\">line $1</a>");
			return errors;
		}
		
		function gotoLine(line_number) {
			editor.gotoLine(line_number);
		}
		
		function show_output() {
			$("#output").height('300px');
			$("#output").width('630px');
		}
		function hide_output() {
			$("#output").height('300px');
			$("#output").width('5px');
		}
		
		function show_errors() {
			$("#errors").height('300px');
		}
		function hide_errors() {
			$("#errors").height('1px');
		}
		
		function hide_errors_if_empty() {
			if ($(".errors").html().length == 0) {
				hide_errors();
			}
		}


		// This is some crazy attempt to allow users to update the editor from
		// inside the editor using (git pull) if there are updates...
		function check_for_updates() {
			$("#updates_refresh").hide();
			$.getJSON('check_for_updates.php', function(updates) {
				if (updates.length > 0) {
					if (updates[0].warning != undefined) {
						$("#updates-warning").html(updates[0].warning);
						$("#update_now").hide();
						$("#update_hide").text('OK, go away').show();
					} else {
						$("#updates-list").html(updates.map(function(update) {
							return '<li><q>' + update.message + '</q> by ' + update.author + ' on ' + update.date + '</li>'
						}).join(''));
						
						$("#updates.button").show();
						$("#update_hide").text('Not now').show();
					}
					$("#updates").show();
				}
			});
		}
		
		function hide_update_screen() {
			$("#updates").hide();
		}
		
		function update_now() {
			$.get('update_now.php', function(data) {
				console.log('Update results:');
				console.log(data);
				console.log('Document should now be reloaded...');
				$("#updates-results").html(data);
				$("#updates_refresh").show();
			});
		}

	</script>

	<style type="text/css">
		body {
			padding: 0;
			margin: 0;
		}
		pre {
			margin: 0;
		}
		#editor {
			position: absolute;
			width: 631px;
			height: 100%;
			/* And make room for the left hand side toggle */
			left: 1px;
		}
		#iframe {
			position: absolute;
			left: 632px;
			top: 0;
			width: 1047px;
			height: 100%;
			
			border: 0;
		}
		#output, #errors {
			z-index: 1001;
			font-family: Helvetica, Arial, sans-serif;
		}
		#output {
			position: absolute;
			top: 0;
			right: 0;
			width: 5px;
			height: 300px;
			
			background: #afa;
			
			overflow: auto;
			
			/* XXX Remove for now */
			display: none;
		}
		#errors {
			position: absolute;
			bottom: 0;
			left: 632px;
			width: 648px;
			height: 1px;
			
			overflow: auto;
			
			background: #ffa;
			border-top: 1px solid #aaa;
		}
		#errors-html {
			margin-top: 0;
			padding-top: 1em;
			padding-left: 1em;
			padding-right: 1em;
		}
		#errors-html {
			background: #ffa;
		}
		
		#toggle-left, #toggle-right {
			position: absolute;
			top: 0;
			width: 1px;
			height: 100%;
			background: white;
		}
		#toggle-left {
			left: 0;
		}
		#toggle-right {
			right: 0;
		}
		
		#updates {
			display: none;
			position: absolute;
			left: 0;
			top: 0;
			z-index: 100000;
			width: 100%;
			padding: 0 1em 1em 1em;
			font-family: Helvetica, Arial, Helvetica, sans-serif;
			color: #242;
			
			background: #eef;
			border-bottom: 1px solid #000;
			box-shadow: 3px 0px 14px #000;
		}
		#updates h2 {
			font-size: 1.25em;
		}
		#updates-list q {
			font-style: italic;
		}
		


		.awesome, .awesome:visited {
			background: #222 url(http://zurb.com/images/alert-overlay.png) repeat-x;
			border: 0px;
			border-bottom: 1px solid rgba(0,0,0,0.25);
			color: #fff;
			cursor: pointer;
			display: inline-block;
			font-family: 'Helvetica Neue', Arial, Helvetica, Verdana, sans-serif;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius:5px;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
			-moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
			box-shadow: 0 1px 3px rgba(0,0,0,0.5);
			padding: 5px 10px 6px;
			position: relative;
			text-decoration: none;
			text-shadow: 0 -1px 1px rgba(0,0,0,0.25);	
		}
		.awesome:hover {
			background-color: #111;
			color: #fff;
		}
		.awesome:active {
			top: 1px;
		}
		.small.awesome, .small.awesome:visited {
			font-size: 11px;
		}
		.awesome, .awesome:visited, .medium.awesome, .medium.awesome:visited {
			font-size: 13px;
			font-weight: bold;
			line-height: 1;
			text-shadow: 0 -1px 1px rgba(0,0,0,0.25);
		}
		.large.awesome, .large.awesome:visited {
			font-size: 14px;
			padding: 8px 14px 9px;
		}
		.green.awesome, .green.awesome:visited {
			background-color: #91bd09;
		}
		.green.awesome:hover {
			background-color: #749a02;
		}
		.blue.awesome, .blue.awesome:visited {
			background-color: #2daebf;
		}
		.blue.awesome:hover {
			background-color: #007d9a;
		}
		.red.awesome, .red.awesome:visited {
			background-color: #e33100;
		}
		.red.awesome:hover {
			background-color: #872300;
		}
		.magenta.awesome, .magenta.awesome:visited {
			background-color: #a9014b;
		}
		.magenta.awesome:hover {
			background-color: #630030;
		}
		.orange.awesome, .orange.awesome:visited {
			background-color: #ff5c00;
		}
		.orange.awesome:hover {
			background-color: #d45500;
		}
		.yellow.awesome, .yellow.awesome:visited {
			background-color: #ffb515;
		}
		.yellow.awesome:hover {
			background-color: #fc9200;
		}
		
		@-webkit-keyframes greenPulse {
			from { background-color: #749a02; -webkit-box-shadow:: 0 0 9px #333; }
			50% { background-color: #91bd09; -webkit-box-shadow:: 0 0 18px #91bd09; }
			to { background-color: #749a02; -webkit-box-shadow:: 0 0 9px #333; }
		}
		
		a.green {
			-webkit-animation-name: greenPulse;
			-webkit-animation-duration: 2s;
			-webkit-animation-iteration-count: infinite;
		}

	</style>
</head>

<body>

<div id="updates">
	<h2>There are new updated to the editor available!</h2>
	<p id="updates-warning"></p>
	<ul id="updates-list"></ul>
	<a href="javascript:update_now();" id="update_now" class="awesome large green button">Update!</a>
	<a href="javascript:hide_update_screen();" id="update_hide" class="awesome large blue button">Not now</a>
	<pre id="updates-results"></pre>
	<a href="javascript:window.location.reload();" id="updates_refresh" class="awesome large green button">Reload page to see changes</a>
</div>

<pre id="output"></pre>

<iframe id="iframe"></iframe>

<div id="errors">
	<div id="errors-code" class="errors"></div>
	<div id="errors-html" class="errors"></div>
</div>

<div id="editor">code goes here</div>

<div id="toggle-left"></div>
<div id="toggle-right"></div>

</body>
</html>
