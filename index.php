<html>
<head>
	<title>Passionate environment</title>

	<script src="http://www.passionismandatory.com/libs/ace/ace-uncompressed.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/mode-html.js" type="text/javascript" charset="utf-8"></script>
	<script src="http://www.passionismandatory.com/libs/ace/theme-twilight.js" type="text/javascript" charset="utf-8"></script>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js" type="text/javascript"></script> 
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js" type="text/javascript"></script> 
	
	<script type="text/javascript">
		
		var project_url = get_project_url();

		var validator_url = "http://validator.nu/";
		if (getURLParameter('validator_url') != 'null') {
			// TODO Maybe this could be stored in a cookie for convenience?
			validator_url = getURLParameter('validator_url');
		}

		var run_delay = 1500;
		
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
			editor.setTheme("ace/theme/twilight");
			
			editor.setShowPrintMargin(false);
			
			// Without this, hitting the home button doesn't scroll all the way 
			editor.renderer.setPadding(0);
			
			var mode = require("ace/mode/html").Mode;
			editor.getSession().setMode(new mode());
			
			$.get('get.php', function(data) {
				editor.getSession().setValue(data);
			});
			
			editor.getSession().on('change', function() {
				code_changed();
				run_unintrusive();
			});
			editor.getSession().selection.on('changeCursor', abort_run);
			
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
				// update_output();
			});
			
			// FIXME When going right, toggling browser fullscreen and then going left the editor is all white (resizing the window brings it's content back...)
			$("#toggle-right").mouseover(function() {
				console.log('RIGHT');
				
				$("#editor").css("width", 0);
				$("#iframe").css("left", 1);
				$("#iframe").css("width", 1279);
			});

			$("#toggle-left").mouseover(function() {
				console.log('LEFT');

				$("#editor").css("width", 631);
				$("#iframe").css("left", 632);
				$("#iframe").css("width", 647);
				editor.resize();
			});

		};
		
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
			$.post("put.php", { data: data }, function(data) {
				if (data.length) {
					alert("Got an unexpected response: " + data);
					return false;
				} else {
					$("#iframe").attr('src', '../index.php');
				}
			});
			
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
			validate_html5(project_url, function(data) {
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
			console.log(validator_url);
			$.ajax({
				url: validator_url,
				data: { doc: url, out: 'json' },
				success: function(result) {
					// FIXME Do propper error handling here!
					if (result.messages[0].type == 'non-document-error') {
						alert('Failed to validate, maybe you are on using an external validator from localhost?');
						return;
					}
					callback(
						result.messages
						.filter(function(message) { return message.type != 'info' })
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
			width: 647px;
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
	</style>
</head>

<body>

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