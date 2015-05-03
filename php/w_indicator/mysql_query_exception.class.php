<?php
class MYSQL_QUERY_EXCEPTION extends Exception{
	public function __construct($connection){
		parent::__construct(mysql_error($connection),0);
	}
}