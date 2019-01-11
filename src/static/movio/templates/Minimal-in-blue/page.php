<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php print $docTitle?></title>
    <?php print $metadata?>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.8.2.min.js"><\/script>')</script>
    <?php print $css?>
    <link rel="stylesheet" type="text/css" href="../../../mediaelement/mediaelementplayer.css" charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../../../js/slick/slick.css">
    <?php print $head?>
</head>

<body>
    <!-- outer -->
    <div id="outer">

        <!-- content -->
        <div class="clearfix container-fluid">

            <!-- header show-menu -->
            <header class="row-fluid js-header  clearfix">

                <a href="<?php print GLZ_HOST; ?>" class="site-logo"><?php print $siteTitle; ?></a>

                <!-- langs -->
                <nav class="languages">
                     <?php print $languages; ?>
                </nav>
                <!-- langs -->

                <a class="btn-menu show" >
                	<span class="wrapper-icon">
                    	<i class="fa fa-align-justify"></i>
                    </span>
                    <span class="wrapper-text">menu</span>
                </a>

                <a class="btn-menu close">
                	<span class="wrapper-icon">
                    	<i class="fa fa-times"></i>
                    </span>
                    <span class="wrapper-text">close</span>
                </a>

                <?php print $search; ?>

                <!-- nax sx -->
                <nav class="menu">
                    <?php print $menu; ?>
                </nav>
                <!-- nav sx -->

            </header>
            <!-- header -->

            <!-- content-box -->
            <div class="content-box row-fluid clearfix">

                <!-- article -->
                <article class="main-content col-md-12">
                    <?php if ($breadcrumbs) { ?>
                    <nav class="breadcrumb">
                         <?php print $breadcrumbs; ?>
                    </nav>
                    <?php } ?>
                    <?php if ($pageTitle) { ?>
                    <div class="box-title clearfix">
                    	<?php print $pageTitle; ?>
                        <a href="javascript:window.print()" class="print-ico"><i class="fa fa-print icon-grey"></i></a>
                    </div>
                    <?php } ?>
                    <?php print $content; ?>
                    <?php print $afterContent; ?>

                </article>
                <!-- article -->
            </div>
            <!-- content-box -->
        </div>
        <!-- content -->

        <?php if ($sharing) { ?>
        <div class="sharing">
            <?php print $sharing; ?>
        </div>
        <?php } ?>
    </div>
    <!-- outer -->

    <!-- footer -->
    <footer>
        <div class="clearfix">
          	 <div class="pull-left">
                <div id="info-page" class="visible-desktop">
                    <div class="container-fluid">
                        <p><?php print $docUpdate;?></p>
                    </div>
                </div>

                <div>
                    <?php print $address;?>
                    <p><?php print $copyright;?></p>
                </div>
            </div>

            <div class="pull-right">

            	<?php print $metanavigation; ?>

                <div class="logo-footer">
                    <?php print $logoFooter; ?>
                    <a href="http://www.movio.beniculturali.it" title="Movio - Online Virtual Exhibition">
                        <img src="img/logo_movio.png" alt="Movio - Online Virtual Exhibition" title="Movio - Online Virtual Exhibition" >
                    </a>
                </div>

            </div>

        </div>
    </footer>
    <!-- footer -->

    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="../../js/movio.js"></script>
    <script src="../../../mediaelement/mediaelement-and-player.js" type="text/javascript"></script>
    <script src="../../../js/slick/slick.min.js"></script>

    <?php print $tail; ?>
</body>
</html>