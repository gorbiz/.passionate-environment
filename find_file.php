<?php

function find_file() {
	foreach (scandir('../') as $file) {
		if ($file[0] == '.') continue;
		if (strtolower($file) == 'index.html' || strtolower($file) == 'index.php') {
			return $file;
		}
	}
	
	foreach (scandir('../') as $file) {
		if ($file[0] == '.') continue;
		if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), array('php', 'html'))) {
			return $file;
		}
	}
	return false;
}

echo find_file();