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
}
.font-size-2{
	font-size: 2em;
	margin: 0;
}
.with-line {
	border-bottom: 1px solid #ccc;
}
</style>
</head>
<body>
    <div id="content">
		<p class="font-size-2"><?php echo $e['code'].' : '.$e['description'];?></p>
		<?php if (isset($e['trace'])) { ?>
			<p class="with-line">Stacktrace</p>
			<ol>
			<?php
				$errors = $e['trace'];
				for ( $i = 0; $i < count($errors); $i++ ) {
					echo '<li>'.$errors[$i].'</li>';
				}
			?>
			</ol>
		<?php } ?>
		<p class="with-line">Request variables</p>
		<ul>
		<?php
			$params = __Request::getAllAsArray();
			foreach ( $params as $k => $v ) {
				if ( !empty( $v ) ) {
					echo '<li><strong>'.$k.'</strong> '.$v.'</li>';
				}
			}
		?>
		</ul>
	</div>
</body>
</html>