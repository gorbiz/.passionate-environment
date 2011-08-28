<?php
function get_changes() {
	$handle = popen('git fetch 2>&1', 'r');
	$read = fread($handle, 2096);
	pclose($handle);
	
	$handle = popen('git log --name-only ..origin/master 2>&1', 'r');
	$result = "";
	while($read = fread($handle, 2096)) {
		$result .= $read;
	}
	pclose($handle);
	
	$result = trim($result);
	
	if (empty($result)) {
		return array();
	}
	
	$commits = explode("\n\ncommit ", $result);
	return array_map('pretty_commit', $commits);
}

function pretty_commit($commit) {
	$result = array();
	
	$lines = explode("\n", $commit);
	$result['id'] = explode(' ', $lines[0]); $result['id'] = $result['id'][count($result['id']) - 1];
	
	$sections = explode("\n\n", $commit);
	$info = $sections[0];
	$result['message'] = $sections[1];
	if (isset($sections[2])) {
		$result['files'] = $sections[2];
	}
	
	$lines = explode("\n", $info);
	
	$matches = array();
	foreach ($lines as $line) {
		preg_match('/Author:\s(.+)$/', $line, $matches);
		if (!empty($matches)) $result['author'] = $matches[1];
	}
	foreach ($lines as $line) {
		preg_match('/Date:\s(.+)$/', $line, $matches);
		if (!empty($matches)) $result['date'] = date('Y-m-d H:i:s', strtotime($matches[1]));
	}
	foreach ($lines as $line) {
		preg_match('/Merge:\s(.+)$/', $line, $matches);
		if (!empty($matches)) $result['merge'] = $matches[1];
	}
	
	$result = array_map('trim', $result);
	return $result;
}

function is_working_copy_clean() {
	$handle = popen('git status -s 2>&1', 'r');
	$result = $read = fread($handle, 2096);
	pclose($handle);
	
	return empty($result);	
}

$changes = get_changes();

if (count($changes) && !is_working_copy_clean()) {
	echo json_encode(array(array('warning' => 'There are updates available but I think you will have to apply them manually as your working copy contains changes...')));
} else {
	echo json_encode($changes);
}