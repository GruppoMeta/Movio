<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

?>
<?php @header("HTTP/1.1 500 Internal Server Error"); ?>
<html>
<head>
<title>Internal Server Error</title>
<style type="text/css">

body {
	background-color:	#fff;
	margin:				40px;
	font-family:		Lucida Grande, Verdana, Sans-serif;
	font-size:			12px;
	color:				#000;
}

#content  {
	margin: auto;
	padding-bottom: 200px;
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
.code {
	margin: 1em;
	background: #eee;
	border: 1px solid #ccc;
}
.currentLine {
	background: #ff6600;
}
</style>
</head>
<body>
	<div id="content">
		<h1>GLIZY framework</h1>
		<h2><?php echo $e['code'].' : '.$e['description'];?></h2>
		<h3>Stacktrace</h3>
		<ul>
<?php
$realPathCore = realPath( dirname( __FILE__ ).'/../../../' );
echo '<li>';
echo '1# '.str_replace( $realPathCore, '', $e['file'] ).':'.$e['line'].'<div class="code">';
$fileSrc = highlight_file( $e['file'], true );
$fileSrc =  explode("<br />", $fileSrc);
$start = $e['line'] < 5 ? 0 : $e['line'] - 5;
$end = $start + 10;
$out = '';
foreach ( $fileSrc as $k => $line )
{
	$k++;
    if ( $k > $end ) break;
    $line = trim( strip_tags( $line ) );
	if ( $k >= $start )
	{
		if ( $k != $e['line'] )
		{
			echo '<code>'.trim(strip_tags($line)).'</code><br />';
		}
		else
		{
			echo '<div class="currentLine"><code>'.trim(strip_tags($line)).'</code></div>';
 		}
	}
}
echo '</div></li>';

ob_start();
debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
$string .= ob_get_clean();
$errors = explode("\n", $string );
for ( $i = 3; $i < count( $errors); $i++ )
{
	if ( !empty( $errors[ $i ] ) )
	{
		$err = str_replace( $realPathCore, '', $errors[ $i ] );
		$err = str_replace( '#'.$i, '#'.($i-1), $err );
		echo '<li>'.$err.'</li>';
	}
}

?>
	</ul>
	<h3>Request variables</h3>
	<ul>
<?php
$params = __Request::getAllAsArray();
foreach ( $params as $k => $v )
{
	if ( !empty( $v ) )
	{
		echo '<li><strong>'.$k.'</strong> '.$v.'</li>';
	}
}
?>
	</ul>
	</div>
</body>
</html>