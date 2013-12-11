<?php
    require("server/config.php");
    require("includes/functions.php");
    require("includes/constants.php");
    
    $page = isset($_GET["page"]) ? $_GET["page"] : "home";
    
    $db = new SimpleDB(SimpleDB::MYSQL, 'localhost', 'rehhoo_1', 'S6NZx8X1xfDUwgh9', 'rehhoo_db1');

    $template = new template();
    $template->set("page", $page);
    $template->display();
?>