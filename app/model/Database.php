<?php

class Database {

	private $db = null;

	function __construct( $settings ){
		$this->db = new mysqli($settings["host"], $settings["username"], $settings["password"], $settings["database"]);

		/* check connection */
		if ($this->db->connect_errno) {
		    printf("Connect failed: %s\n", $this->db->connect_error);
		    exit();
		}
	}

	/**
	* BASIC QUERY, takes a SQL and execute it. Return the result of executing the query.
	*/
	function query( $sql ){
		if( $this->db == null ){
			return false;
		}
		$result = $this->db->query( $sql );

		return $result;
	}

	/**
	* SELECT QUERY, takes a SQL and returns an array with results.
	*/
	function select( $sql ){
		if( $this->db == null ){
			return false;
		}

		$results = array();
		if ( $result = $this->db->query( $sql ) ) {
		    while ($row = $result->fetch_object()) {
            	$results[] = $row;
            }

			$result->close();
		}

		return $results;
	}

	/**
	* SELECT QUERY only to take the first element, takes a SQL and returns an object with results.
	*/
	function first( $sql ){
		if( $this->db == null ){
			return false;
		}
		
		$result = null;
		if ( $results = $this->db->query( $sql ) ) {
		    $result = $results->fetch_object();
			$results->close();
		}

		return $result;
	}

	/**
	* INSERT statement, returns the id of inserted.
	*/
	function insert( $sql ){
		if( $this->db == null ){
			return false;
		}
		$insert_id = $this->db->query( $sql );

		return $insert_id;
	}
}