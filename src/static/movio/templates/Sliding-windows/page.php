<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php print $docTitle?></title>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script src="js/jqxcore.js"></script>
    <script src="js/jqxmenu.js"></script>
    <link href="css/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css" />
    <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
    <?php print $css?>
    <link rel="stylesheet" type="text/css" href="../../../mediaelement/mediaelementplayer.css" charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../../../js/slick/slick.css">
    <!--[if lte IE 9]>
        <link rel="stylesheet" href="css/ie.css" />
    <![endif]-->
    <?php print $head?>
</head>

<body>
    <!-- outer -->
    <div id="outer">

        <!-- topbar -->
        <div id="topbar" class="clearfix">
            <nav class="languages">
                 <?php print $languages; ?>
            </nav>
            <?php print $search; ?>
        </div>
        <!-- topbar -->

        <!-- content -->
        <div class="clearfix container-fluid">

            <!-- header -->
            <header id="header" class="row-fluid clearfix">
                <div class="col-md-12 visible-tablet visible-desktop">
                    <h1 class="site-logo"><a href="<?php print GLZ_HOST; ?>" class="site-logo"><?php print $siteTitle; ?></a></h1>
                    <button type="button"  class="show-nav-for-iphone" data-toggle="collapse" data-target="#nav-collapse">MENU</button>
                    <div id="nav-collapse" class="collapse col-md-12">
                        <nav class="menu">
                            <?php print $menu; ?>
                        </nav>
                    </div>
                </div>
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
                    </div>
                    <?php } ?>
                    <?php if ($content) { ?>
                    <div class="clearfix">
                        <?php print $content; ?>
                    </div>
                    <?php } ?>
                    <?php if ($afterContent) { ?>
                    <div class="clearfix">
                        <?php print $afterContent; ?>
                    </div>
                    <?php } ?>
                </article>
                <!-- article -->
            </div>
            <!-- content-box -->
        </div>
        <!-- content -->


    </div>
    <!-- outer -->

    <!-- footer -->
    <footer id="footer">
        <div id="info-page" class="visible-desktop">
            <div class="container-fluid">
                <nav>
                    <?php print $metanavigation; ?>
                </nav>
                <p><?php print $docUpdate;?></p>
            </div>
        </div>

        <div class="clearfix">
            <div class="logo-footer">
                <?php if ($sharing) { ?>
                <div class="sharing">
                    <?php print $sharing; ?>
                </div>
                <?php } ?>
                <?php print $logoFooter; ?>
                <a href="http://www.movio.beniculturali.it" title="Movio - Online Virtual Exhibition">
                    <img src="img/logo_movio.png" alt="Movio - Online Virtual Exhibition" title="Movio - Online Virtual Exhibition" width="80" >
                </a>
            </div>

            <div class="box">
                <?php print $address; ?>
            </div>
            <div class="box">
                <p><?php print $copyright; ?></p>
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