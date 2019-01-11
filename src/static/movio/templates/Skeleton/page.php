<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php print $docTitle?></title>
    <!-- JQUERY LIB -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.8.2.min.js"><\/script>')</script>
    <!-- JQUERY LIB -->

    <?php print $css?>
    <link rel="stylesheet" type="text/css" href="../../../mediaelement/mediaelementplayer.css" charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../../../js/slick/slick.css">
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
            <nav class="metanavigation visible-tablet visible-desktop">
                <?php print $metanavigation; ?>
            </nav>
            <div class="dropdown pull-right">
  <button aria-expanded="true" data-toggle="dropdown" id="dropdownMenu1" type="button" class="btn btn-default dropdown-toggle">
   <i class="icon fa fa-user"></i>
  </button>
    <?php print $userMenu; ?>
</div>
             <?php print $search; ?>
        </div>
        <!-- topbar -->

        <!-- content -->
        <div class="clearfix container-fluid">

            <!-- header -->
            <header id="header" class="row-fluid clearfix">
                <div class="col-md-12 visible-tablet visible-desktop">
                    <h1><?php print $siteTitle; ?></h1>
                </div>
            </header>
            <!-- header -->

            <!-- content-box -->
            <div class="content-box row-fluid clearfix">
                <!-- aside sx -->
                <aside class="sx col-md-3">
                    <nav class="menu">
                    <?php print $navigation; ?>
                    </nav>
                    <?php print $leftSidebar; ?>
                    <?php print $leftSidebarAfter; ?>
                    <?php print $sharing; ?>
                </aside>
                <!-- aside sx -->

                <!-- main-content -->
                <div class="main-content col-md-9">
                    <?php if ($breadcrumbs) { ?>
                    <nav class="breadcrumb">
                        <?php print $breadcrumbs; ?>
                    </nav>
                    <?php } ?>
                    <?php if ($pageTitle) { ?>
                    <div class="box-title clearfix">
                        <?php print $pageTitle; ?>
                        <a href="javascript:window.print()" class="print-ico"><i class="fa fa-print fa fa-grey"></i></a>
                    </div>
                    <?php } ?>
                    <?php print $content; ?>
                    <?php print $afterContent; ?>
                </div>
                <!-- article -->
            </div>
            <!-- content-box -->
        </div>
        <!-- content -->
    </div>
    <!-- outer -->

    <!-- footer -->
    <footer id="footer">
        <div class="clearfix">
            <div class="logo-footer">
                <a href="http://www.movio.beniculturali.it" title="Movio - Online Virual Exhibition">
                    <img src="img/logo_movio.png" alt="Movio - Online Virual Exhibition" title="Movio - Online Virual Exhibition" width="109" height="81">
                </a>
                <?php print $logoFooter; ?>
            </div>

            <!-- box -->
            <div class="box">
                 <?php print $linkFooter; ?>
            </div>
            <!-- box -->
        </div>

        <!-- info-page -->
        <div id="info-page" class="visible-desktop">
            <div class="container-fluid">
                <p class="pull-left"><?php print $copyright;?></p>
                <p class="pull-right"><?php print $docUpdate;?></p>
            </div>
        </div>
        <!-- info-page -->
    </footer>
    <!-- footer -->

    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="../../js/movio.js"></script>
    <script src="../../../mediaelement/mediaelement-and-player.js" type="text/javascript"></script>
    <script src="../../../js/slick/slick.min.js"></script>
</body>
</html>