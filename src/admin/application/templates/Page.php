<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php print($docTitle); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <?php print($head); ?>
        <link rel="stylesheet" href="css/styles.css" />
    </head>
    <body class="with-sidebar">
    <div id="outer">
        <div id="topbar">
            <div class="pull-left">
                <div id="dummy-logo"><img src="img/logo/logo-top.png" alt="<?php print( __Config::get( 'APP_NAME' ) ) ?>"></div>
                <div id="dummy-text"><?php print( __Config::get( 'APP_NAME' ) ) ?> <small><?php echo __Config::get('APP_VERSION') ?></small></div>
            </div>

            <div class="pull-right">

                <!-- show-nav-for-iphone -->
                <button type="button" class="show-nav-for-iphone" data-toggle="collapse" data-target="#nav-collapse"></button>
                <!-- show-nav-for-iphone -->

                <?php print($languageMenu); ?>
                <div class="pull-left">
                    <div id="exit-menu" class="pull-left"><?php print($logout); ?></div>
                </div>
            </div>
        </div>
        <div id="sidebar">
            <div id="sidebar-inner">
                <?php print($leftSidebar); ?>
            </div>
        </div>

        <?php if (isset($treeview)) {?>
            <?php print($treeview); ?>
            <div id="container" class="with-treeview">
                <div id="container-inner" class="container-fluid">
                    <?php print($content); ?>
                </div>
            </div>
        <?php } else {?>
            <div id="container">
                <?php
                    $showBreadcrumbsBar = (isset($breadcrumbs) && $breadcrumbs) || (isset($actions) && $actions);
                    $cssClass = $showBreadcrumbsBar ? 'with-breadcrumb' : '';
                ?>
                <div id="container-inner" class="container-fluid <?php print($cssClass);?>">
                    <?php if ($showBreadcrumbsBar) {?>
                    <div id="breadcrumb-bar">
                        <div id="breadcrumb">
                            <?php print(@$breadcrumbs); ?>
                        </div>
                        <div id="breadcrumb-actions">
                            <?php print(@$actions); ?>
                        </div>
                    </div>
                    <?php }?>
                    <div class="box-content">
                    <div class="row-fluid">
                        <div class="span12">
                            <div id="admincontent" class="box">
                                <div id="message-box"></div>
                                <?php print($content); ?>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php print(@$tail); ?>
    <script type="text/javascript">
// <![CDATA[
$(function(){
    if ($.fn.button && $.fn.button.noConflict) {
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
    }
})
// ]]>
</script>
    </body>
</html>