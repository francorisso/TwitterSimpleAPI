<?php

$config = array(
	//directory paths
	"root_directory" 	=> "/home/franco/720desarrollos/test-project",
	"model_path"		=> "/app/model/",
	"controller_path"	=> "/app/controller/",

	//database data
	//Types: mysql
	'database_type'		=> 'mysql',
	
	//details
	'database_info'		=> array(
			'mysql' => array(
					'host'		=> 'localhost',
					'database' 	=> 'TwitterTest',
					'username' 	=> 'root',
					'password' 	=> 'root',
			),
	),

	//default twitter configuration params
	"consumer_key" 		=> "zIhw6inTFR0WEpr4E7u1b5ex4",
	"consumer_secret" 	=> "IhfPeF58qTib2BUk7Ywy4gL5rdMnCeyoKQXaT52ZU2TDem4OvB",
	"screen_name"		=> "francorisso",
	"tweets_count"		=> 10,
);