<?php
function req_file () {

    	echo '编译';
    	ob_start();
    	require '../app/home/cache/index.php';
    	$temp = ob_get_contents();
    	ob_clean();
    	$a = file_get_contents('../app/home/cache/index.php');
        echo $a;


}

req_file();
 ?>
