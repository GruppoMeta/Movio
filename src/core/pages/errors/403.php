<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
?>
<html>
<head>
<title>403 Forbidden</title>
<style type="text/css">
body {
	background-color:	#fff;
	margin:				40px;
	font-family:		Lucida Grande, Verdana, Sans-serif;
	font-size:			12px;
	color:				#000;
}

#content  {
	min-height: 400px;
	width: 500px;
	margin: auto;
	background: #fff;
}

h1 {
	font-size:			3em;
	color:				#990000;
	margin: 			0 0 4px 0;
	text-align: center;
}
h2 {
	text-align: center;
	font-size:			2em;
	color:				#0;
	margin: 			0 0 4px 0;
}
</style>
</head>
<body>
	<div id="content">
		<h1><?php echo $e['title'];?></h1>
		<h2><?php echo $e['code'].' : '.$e['description'];?></h2>
	</div>
</body>
</html>
