<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php print $docTitle?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <?php print $css?>

         <!-- JQUERY LIB -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.2.min.js"><\/script>')</script>
        <!-- JQUERY LIB -->

        <script src="js/vendor/modernizr-2.6.2.min.js"></script>

        <!-- FLEXSLIDER -->
		<link rel="stylesheet" href="css/flexslider.css" type="text/css" media="screen" />
		<script defer src="js/jquery.flexslider.js"></script>
		<!-- FLEXSLIDER -->

        <!-- fancybox -->
        <link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox-1.3.4.css" media="screen" />
        <script type="text/javascript" src="fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <!-- fancybox -->

        <link rel="stylesheet" type="text/css" href="../../../mediaelement/mediaelementplayer.css" charset="utf-8">
        <?php print $head?>

    </head>
    <body class="page">
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

		<!-- outer -->
        <div id="outer">

            <div class="contentWrapper clearfix container-fluid">
                <article class="main-content col-md-12">

                    <div class="content-box row-fluid ">
                        <?php print $content; ?>
                    </div>
                </article>
            </div>
            <!-- content -->

        </div>
        <!-- outer -->

        <script src="js/plugins.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/main.js"></script>
        <script src="../../../mediaelement/mediaelement-and-player.js" type="text/javascript"></script>
    </body>
</html>
