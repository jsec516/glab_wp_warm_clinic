<?php

require_once 'class.waitinglist.php';

class SEVENDAYS extends WAITINGLIST {

    function __construct() {
        parent::__construct();
    }

    function getFirstDateOfWeek() {
        $firstDayOfWeek = date('d');
        return $firstDayOfWeek;
    }

    function getLastDateOfWeek() {
        $lastDayOfWeek = date('d') + 6;
        return $lastDayOfWeek;
    }

    function restOfTheDayToConsider() {

        $week_start_date = $this->getFirstDateOfWeek();
        $week_end_date = $this->getLastDateOfWeek();
        $current_month = date('m');
        $current_year = date('Y');
        for ($i = 0; $i < 7; $i++) {
            $considering_date = mktime(0, 0, 0, $current_month, $week_start_date, $current_year);
            $this->storeConsiderableDates($considering_date);
            $week_start_date++;
        }
        //print_r($this->formattedConsiderableDays);
    }

}
