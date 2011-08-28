<?php

$handle = popen('git pull 2>&1', 'r');
while($read = fread($handle, 2096)) {
	echo $read;
}
pclose($handle);

