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
readfile("../$file");