<?php
	/*
	* We want to be able to autoload classes
	*/
	function __autoload($class) {
	    if(!class_exists($class)) {
	        require_once("class/$class.class.php");
	    }
	}
	
	/*
	* Use this function to debug code
	*/
	function debug($array, $trace = false) {
	    echo "<pre>";
	    print_r($array);
	    echo $trace ? debug_print_backtrace() : "No backtrace";
		echo "</pre>";
    }
    
    /*
    * Use this function to call functions or whatever that needs to be run on each page
    */
    function preprocess_page() {
    		
    }
    
    /*
    *
    */
    
    /*
    * Use this function to construct your page wide menu
    * Key = name of page to which link should point
    * Value = What the use will see on link
    */
    function hook_menu() {
    	$menu = array();
    	$menu["home"] = "Welcome";
    	$menu["configuration"] = "Configuration";
    	$menu["database"] = "Database";
    	$menu["structure"] = "Structure";
    	$menu["functions"] = "Functions";
    	$menu["system-messages"] = "System messages";
    	$menu["libraries"] = "Libraries";
    	$menu["object-methods"] = "Object methods";
    	
    	return $menu;
    }
    
    /*
    * Will set the pages that are allowed to be shown throughout the template
    * Key ís name of the template file
    * Value will become the title of the page
    */
    function hook_pages() {
    	$pages["home"] = "Rehhoff template documentation";
    	$pages["404"] = "404 - page not found";
    	$pages["configuration"] = "System configurations";
    	$pages["structure"] = "Code structure";
    	$pages["functions"] = "Predfined Functions";
    	$pages["database"] = "Database";
    	$pages["system-messages"] = "Messages from the system";
    	$pages["libraries"] = "Third party libraries";
    	$pages["object-methods"] = "Methods in the template object";
    	
    	return $pages;
    }
    
    
?>