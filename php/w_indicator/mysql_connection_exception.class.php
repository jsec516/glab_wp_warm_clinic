<?php
class MYSQL_CONNECTION_EXCEPTION extends Exception{
	public function __construct(){
		$message = "Sorry, couldn't connect to the mysql server.";
		parent::__construct($message,0000);
	}
}