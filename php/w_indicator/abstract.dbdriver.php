<?php

define("FETCH_ASSOC", 1);
define("FETCH_ROW", 2);
define("FETCH_BOTH", 3);
define("FETCH_OBJECT", 4);

abstract class abstractdbdriver {

    protected $connection;
    protected $results = array();
    protected $lasthash = "";

    public function count() {
        return 0;
    }
    //commented because of strict standard error
    /*public function execute() {
        //return false;
    }*/

    private function prepQuery($sql) {
        return $sql;
    }

    public function escape($sql) {
        return $sql;
    }

    public function affectedRows() {
        return 0;
    }

    public function insertId() {
        return 0;
    }

    public function transBegin() {
        return false;
    }

    public function transCommit() {
        return false;
    }

    public function transRollback() {
        return false;
    }

    public function getRow($fetchmode = FETCH_ASSOC) {
        return array();
    }

    public function getRowAt($offset = null, $fetchmode = FETCH_ASSOC) {
        return array();
    }

    public function rewind() {
        return false;
    }

    public function getRows($start, $count, $fetchmode = FETCH_ASSOC) {

        return array();
    }

}
