<?php
!defined("APPLICATION_RUNNING") ? die("direct acces error") : "";
;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php echo $stylesheets; ?>
        <?php echo $javascript; ?>
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <div id="wrap">
            <div id="header"> 
                <h1><?php echo $template->get("title"); ?></h1>
            </div>
            <?php echo $template->menu(); ?>
            <div id="content">
                <?php require("catalog/view/{$template->get("page")}.tpl.php"); ?>
            </div>
        </div>
    </body>
</html>
