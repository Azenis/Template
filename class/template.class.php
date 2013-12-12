<?php

/**
 * @author alla2040
 */
class template{

    private $allowed, $connection;
    public $page, $title;
    public $stylesheets, $jscripts;
    public $messages, $data;
    /*
     * Constructer DUH
     */

    public function __construct() {
        $this->messages = array();
        $this->data     = array();
        //TODO: make this dynamic depending on view files
        if (function_exists("hook_pages")) {
            $this->allowed = hook_pages();
        } else {
            $this->allowed["404"] = "404 - Page not found";
        }
    }

    /*
     * Method to construct the menu
     */

    public function menu() {
        if (function_exists("hook_menu")) {
            $menu = hook_menu();
            //array key will become the page GET value
            //array value being the text link
            (string) $html = "";
            $html .= "<ul id='menu'>";
            foreach ($menu AS $key => $value) {
                //find current active tab;
                if ($this->get("page") === $key) {
                    $html .= "<li class='current'>" . "<a href='index.php?page=$key'>$value</a>";
                } else {
                    $html .= "<li>" . "<a href='index.php?page=$key'>$value</a>";
                }
            }
            $html .= "</ul>";
            return $html;
        }
        return false;
    }

    /*
     * Method to set template based messages
     */

    public function message($message = null, $type = "info") {
        if ($message === null) {
            $return = "";
            foreach ($this->messages as $message) {
                $return .= "<div class='{$message["type"]}'>{$message["text"]}</div>";
            }
            return $return;
        } else {
            $this->messages[] = array("type" => $type, "text" => $message);
            return true;
        }
    }

    /*
     * method to add different predfined packages to the application
     */

    public function add($item) {
        switch ($item) {
            case "lightbox":
                $this->stylesheets .= "<link rel='stylesheet' type='text/css' href='bin/libraries/lightbox/css/lightbox.css' />";
                $this->jscripts .= "<script type'text/javascript' src='bin/libraries/lightbox/js/modernizr.custom.js'></script>";
                $this->jscripts .= "<script type='text/javascript' src='bin/libraries/lightbox/js/lightbox-2.6.min.js'></script>";
                break;
            case "jqueryui":
                $this->stylesheets .= "<link rel='stylesheet' type='text/css' href='bin/libraries/jquery-ui-1.10.3/css/smoothness/jquery-ui-1.10.3.custom.min.css' />";
                $this->jscripts .= "<script type'text/javascript' src='bin/libraries/jquery-ui-1.10.3/js/jquery-ui-1.10.3.custom.min.js'></script>";
                break;
            case "colorbox":
                $this->stylesheets .= "<link rel='stylesheet' type='text/css' href='bin/libraries/colorbox/colorbox.css' />";
                $this->jscripts .= "<script type='text/javascript' src='bin/libraries/colorbox/jquery.colorbox-min.js'></script>";
            case "tinymce":
                $this->jscripts .= "<script type='text/javascript' src='bin/libraries/tinymce/tinymce.min.js'></script>";
                break;
            case "shadowbox":
                $this->stylesheets .= "<link rel='stylesheet' type='text/css' href='bin/libraries/shadowbox/shadowbox.css'>";
                $this->jscripts .= "<script type='text/javascript' src='bin/libraries/shadowbox/shadowbox-3.0.3.js'></script>";
                break;
            default:
                trigger_error("Unspecified library addition", E_USER_NOTICE);
        }
    }

    /*
     * Method to generate html for javascript files
     */

    public function javascript(array $jscripts) {
        foreach ($jscripts as $jscript) {
            if (file_exists("$jscript")) {
                $this->jscripts .="<script type='text/javascript' src='$jscript'></script>";
            }
        }
    }

    /*
     * Method to generate html for stylesheets
     */

    public function stylesheet(array $styles) {
        foreach ($styles as $style) {
            if (file_exists("$style")) {
                $this->stylesheets .="<link rel='stylesheet' type='text/css' href='$style' />";
            }
        }
    }
    
    public function db($host, $username, $password, $database) {
        try {
            $this->connection = new SimpleDB(SimpleDB::MYSQL, $host, $username, $password, $database) or die("error");
        } catch (Exception $e) {
            $this->message("No database connection could be established, server said<br>: {$get->getMessage()})");
        }
    }

    /*
     * method to display the html
     */

    public function display() {
        $db = $this->connection;
        global $db, $template;

        if (!isset($this->allowed[$this->get("page")])) {
            $this->set("page", "404");
        }

        //set stylesheets and javascripts for the page
        $styles = array("catalog/view/style/screen.css", "catalog/view/style/{$this->get("page")}.css");
        $jscripts = array("catalog/view/js/jquery.js", "catalog/view/js/main.js", "catalog/view/js/{$this->get("page")}.js");

        //Begin building the elements for the DOM
        $this->set("title", $this->allowed[$this->get("page")]);
        $this->stylesheet($styles);
        $this->javascript($jscripts);

        //before we even head on the start constructing the page we check if any preprocessing needs doing
        if (function_exists("preprocess_page")) {
            preprocess_page();
        }
        
        //Check if controller file to be executed
        if (is_file("catalog/controller/{$this->get("page")}.php")) require("catalog/controller/{$this->get("page")}.php");
        
        //process data from controller file
        $stylesheets = $this->get("stylesheets");
        $javascript  = $this->get("jscripts");
        $title       = $this->get("title");
        $messages    = $this->message();
        foreach($this->get("data") as $key => $value) {
            $$key = $value;
        }
        
        //now start serving the generated DOM to the browser
        if (!is_file("catalog/view/{$this->get("page")}.tpl.php")) $this->set("page", "404");
        require("template.tpl.php");
    }

    public function get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

}

?>