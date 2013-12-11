<?php
    require("server/config.php");
    require("includes/functions.php");
    require("includes/constants.php");
    
    $page = isset($_GET["page"]) ? $_GET["page"] : "home";
    
    $template = new template();
    //$template->db("localhost", "username", "password", "database");
    $template->set("page", $page);
    $template->display();
?>