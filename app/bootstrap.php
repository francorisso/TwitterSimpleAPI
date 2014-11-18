<?php

error_reporting( E_ERROR | E_WARNING | E_PARSE );

function __autoload( $class_name ) {
	require("config.php");
	if( file_exists($config["root_directory"] . $config["model_path"] . $class_name . ".php")){
    	include_once( $config["root_directory"] . $config["model_path"] . $class_name . ".php" );
    }
}