<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>

	<?php for( $i = 0;$i < 2;$i++) {?>
		<div style="width: 100px; height: 100px;background: red;">helo</div>
	<?php }  ?>
	<p>$test is <?php echo ($test); ?></p>
	<script>console.log('this is js文件')
</script>
	<!-- 引入的js还得要相对于index.php的相对路径 ,用<script src=""></script>的形式才会被正则匹配到    -->
</body>
</html>
