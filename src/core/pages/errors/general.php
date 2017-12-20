<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $e['code'];?> - <?php echo $e['description'];?></title>
<style>
body{
	background: #333;
	color: #fff;
	font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
	font-weight: normal;
}
#content{
	margin: 0 auto;
	width: 960px;
	text-align: center;
}
.font-size-10{
	font-size: 10em;
	margin: 0.5em 0;
	color: #990;
}
.font-size-2{
	font-size: 2em;
	line-height: 1.2em;
}
</style>
</head>
<body>
    <div id="content">
		<p class="font-size-10"><?php echo $e['title'];?></p>
		<p class="font-size-2"><?php echo $e['code'];?> - <?php echo $e['description'];?></p>
	</div>
</body>
</html>

