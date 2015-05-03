<?php
include_once 'mysql_connection_exception.class.php';
include_once 'mysql_query_exception.class.php';
include_once 'abstract.dbdriver.php';
class DAL extends abstractdbdriver{
	public $connection;
	public $db_connected;
	public $result;
	public function __construct($connectionInfo){
		extract($connectionInfo);
		$this->connection=mysqli_connect($SERVER,$USERNAME,$PASSWORD,$DBNAME);

		if($this->connection==false){
			throw new MYSQL_CONNECTION_EXCEPTION();
		}
	}

	public function execute($sql){
		//$sql=$this->prepQuery($sql);
		$parts=explode(" ", trim($sql));
		$type=strtolower($parts[0]);
		$hash=md5($sql);
		$this->lasthash=$hash;
		if("select"==$type){
			if(isset($this->results[$hash])){
				if(is_resource($this->results[$hash])){
					return $this->results[$hash];
				}
			}
		} elseif ("update" == $type || "delete" == $type){ //clear the result cache
			$this->results=array();
		}
		$this->results[$hash]=mysqli_query($this->connection, $sql);
		
	}

	public function count(){
		$lastresult=$this->results[$this->lasthash];
		$count=mysqli_num_rows($lastresult);
		if(!$count) $count=0;
		return $count;
	}

	private function prepQuery(){
		// "DELETE FROM TABLE" returns 0 affected rows.
		// This hack modifies the query so that
		// it returns the number of affected rows
		if(preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)){
			$sql=preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
		}
		return $sql;
	}

	public function escape($sql){
		if(function_exists("mysqli_real_escape_string")){
			return mysqli_real_escape_string($this->connection, $sql);
		} elseif(function_exists("mysqli_escape_string")){
			return mysqli_escape_string($sql);
		}else{
			return addslashes($sql);
		}
	}

	public function affectedRows(){
		return @mysqli_affected_rows($this->connection);
	}

	public function inserId(){
		return @mysqli_insert_id($this->connection);
	}

	public function transBegin(){
		return true;
	}

	public function transCommit(){
		return true;
	}

	public function transRollback(){
		return true;
	}

	public function getRow($fetchmode = FETCH_ASSOC){
		$lastresult=$this->results[$this->lasthash];
		if(FETCH_ASSOC==$fetchmode){
			$row=mysqli_fetch_assoc($lastresult);
		}elseif(FETCH_ROW==$fetchmode){
			$row=mysqli_fetch_row($lastresult);
		}elseif(FETCH_OBJECT==$fetchmode){
			$row=mysqli_fetch_object($lastresult);
		}else{
			$row=mysqli_fetch_array($lastresult,MYSQLI_BOTH);
		}
		return $row;
	}

	public function getRowAt($offset=null,$fetchmode=FETCH_ASSOC){
		$lastresult=$this->results[$this->lasthash];
		if(!empty($offset)){
			mysqli_data_seek($lastresult, $offset);
		}
		return $this->getRow($fetchmode);
	}

	public function rewind(){
		$lastresult=$this->results[$this->lasthash];
		mysqli_data_seek($lastresult, 0);
	}

	public function getRows($start,$count, $fetchmode=FETCH_ASSOC){
		$lastresult=$this->results[$this->lasthash];
		mysqli_data_seek($lastresult, $start);
		$rows=array();
		for($i=$start; $i<=($start+$count);$i++){
			$rows[]=$this->getRow($fetchmode);
		}
		return $rows;
	}

	function __destruct(){
		foreach ($this->results as $result){
			@mysqli_free_result($result);
		}
	}
}