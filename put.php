<?php
if (!isset($_REQUEST['file']) || empty($_REQUEST['file'])) {
	echo "Error no file provided.";
	die;
}

if (! file_exists('../' . $_REQUEST['file'])) {
	echo "Error the file provided does not exist.";
	die;
}

$file = $_REQUEST['file'];
file_put_contents("/tmp/$file" . time(), $_POST['data']);
file_put_contents("../$file", $_POST['data']);