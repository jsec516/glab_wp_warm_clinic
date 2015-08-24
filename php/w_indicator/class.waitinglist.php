<?php

class WAITINGLIST {

    protected $db;
    protected $allWaitingSlots;
    protected $weekDays;
    protected $practitioner;
    protected $treatmentDuration;
    protected $breakTime;
    protected $expectedDay;
    protected $formattedConsiderableDays;

    function __construct() {
        $env='development';
        if($env=='development'){
        $db_config_info = array(
            'SERVER' => 'localhost',
            'USERNAME' => 'root',
            'PASSWORD' => '',
            'DBNAME' => 'clinic'
        );
        }else{
        $db_config_info = array(
            'SERVER' => 'localhost',
            'USERNAME' => 'ishift',
            'PASSWORD' => 'eLVxBMa2Zbxa',
            'DBNAME' => 'ishift_cas'
        );    
        }
        $this->db = new DAL($db_config_info);
        $this->weekDays = $this->setWeekDays();
    }

    function setWeekDays() {
        $this->weekDays = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
        return $this->weekDays;
    }

    function setPractitioner($practitioner) {
        $this->practitioner = $practitioner;
    }

    function getWaitingSlots() {
        $waiting_slot_sql = 'SELECT w.*,duration,allow_multiple as service_type,betn_minutes FROM glab_cas_waiting_appointments AS w LEFT JOIN glab_cas_services AS s ON s.id=w.service_id WHERE w.blog_id="'.get_current_blog_id().'"';
        $this->db->execute($waiting_slot_sql);
        $count_row = $this->db->count();
        if ($count_row > 0) {
            $this->allWaitingSlots = $this->db->getRows(0, $count_row - 1);
        }
        return $this->allWaitingSlots;
    }

    function convertSlotToArray($singleSlot, $separator) {
        $this->resetConsiderableDates();
        foreach ($this->weekDays as $week_day) {
            if (trim($singleSlot[$week_day]) != '') {
                $singleSlot[$week_day] = explode($separator, $singleSlot[$week_day]);
                $this->formattedConsiderableDays[$week_day]['expected_slot'] = $singleSlot[$week_day];
                $this->formattedConsiderableDays[$week_day]['treatment'] = $singleSlot['service_id'];
                $this->formattedConsiderableDays[$week_day]['type'] = $singleSlot['service_type'];
                $this->formattedConsiderableDays[$week_day]['doctor'] = $singleSlot['practitioner_id'];
                $this->formattedConsiderableDays[$week_day]['duration'] = $singleSlot['duration'];
                $this->formattedConsiderableDays[$week_day]['break'] = $singleSlot['betn_minutes'];
            } else
                $singleSlot[$week_day] = null;
        }
        return $singleSlot;
    }

    function getFirstDateOfWeek() {
        $firstDayOfWeek = date('d') - (date('N') - 1);
        return $firstDayOfWeek;
    }

    function getLastDateOfWeek() {
        $lastDayOfWeek = date('d') + (7 - date('N'));
        return $lastDayOfWeek;
    }

    function getCurrentDayOfWeek() {
        return date('N');
    }

    function storeConsiderableDates($consideringDate) {
        $considering_week_day = strtolower(date('D', $consideringDate));
        if (isset($this->formattedConsiderableDays[$considering_week_day]['expected_slot'])) {

            $this->formattedConsiderableDays[$considering_week_day]['full_date'] = date('Y-m-d', $consideringDate);
            $this->formattedConsiderableDays[$considering_week_day]['day'] = date('d', $consideringDate);
            $this->formattedConsiderableDays[$considering_week_day]['month'] = date('m', $consideringDate);
            $this->formattedConsiderableDays[$considering_week_day]['year'] = date('Y', $consideringDate);
            $this->formattedConsiderableDays[$considering_week_day]['full_week_day'] = date('l', $consideringDate);
            $this->formattedConsiderableDays[$considering_week_day]['timestamp'] = $consideringDate;
        }
    }

    function resetConsiderableDates() {
        $this->formattedConsiderableDays = array();
    }

    function restOfTheDayToConsider() {

        $week_start_date = $this->getFirstDateOfWeek();
        $week_end_date = $this->getLastDateOfWeek();
        $current_day_of_week = $this->getCurrentDayOfWeek();
        $current_month = date('m');
        $current_year = date('Y');
        for ($i = 0; $i < 7; $i++) {
            $considering_date = mktime(0, 0, 0, $current_month, $week_start_date, $current_year);
            if ($i >= ($current_day_of_week - 1)) {
                $this->storeConsiderableDates($considering_date);
            } else {

                $considering_week_day = strtolower(date('D', $considering_date));
                $this->formattedConsiderableDays[$considering_week_day] = null;
            }
            $week_start_date++;
        }
    }

    function populateBookedSlot($booked_slot_array) {
        $slots_array = array();
        foreach ($booked_slot_array as $slot) {
            $slot_app_time_array = explode(':', $slot['app_time']);
            $slot_app_time = ($slot_app_time_array[0] * 60 + $slot_app_time_array[1]) . ' & ' . ($slot_app_time_array[0] * 60 + $slot_app_time_array[1] + $slot['treatment_duration']);

            array_push($slots_array, $slot_app_time);
        }
        return $slots_array;
    }

    function bookedTimeListInADate($consideredTimeStamp, $doctor) {
        $booked_slot_in_date_sql = 'select app_time,service_duration as treatment_duration from glab_cas_appointments where app_date="' . date('Y-m-d', $consideredTimeStamp) . '" and practitioner_id="' . $doctor . '" and blog_id="'.get_current_blog_id().'"';
        $this->db->execute($booked_slot_in_date_sql);
        $count_row = $this->db->count();
        if ($count_row > 0) {
            $booked_slot_array = $this->populateBookedSlot($this->db->getRows(0, $count_row - 1));
        } else {
            $booked_slot_array = null;
        }
        return $booked_slot_array;
    }

    function getBookedSlotInWeek() {
        $this->restOfTheDayToConsider();
        foreach ($this->formattedConsiderableDays as $week_day => $consider) {
            if ($consider['expected_slot']) {
                $booked_slot_array = $this->bookedTimeListInADate($consider['timestamp'], $consider['doctor']);
                $this->formattedConsiderableDays[$week_day]['booked_slot'] = $booked_slot_array;
            } else {
                unset($this->formattedConsiderableDays[$week_day]);
            }
        }
    }

    function updateAvailabilityOfSlot($week_day) {
        $this->formattedConsiderableDays[$week_day]['available'] = true;
        if(isset($this->formattedConsiderableDays[$week_day]['num_of_availability'])){
        $this->formattedConsiderableDays[$week_day]['num_of_availability']+=1;
        }else{
            $this->formattedConsiderableDays[$week_day]['num_of_availability']=1;
        }
    }

    function isTheSlotAvailable($week_day, $start_time, $end_time) {
        if (isset($this->formattedConsiderableDays[$week_day]['booked_slot']) AND ! empty($this->formattedConsiderableDays[$week_day]['booked_slot'])) {
            foreach ($this->formattedConsiderableDays[$week_day]['booked_slot'] as $booked_slot) {

                $booked_slot_array = explode('&', $booked_slot);
                if (($start_time >= $booked_slot_array[0] AND $start_time < $booked_slot_array[1]) OR ( $end_time >= $booked_slot_array[0] AND $end_time < $booked_slot_array[1])) {

                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return true;
        }
    }

    function isAvailableParticularSlot($week_day, $booked_slots, $expected_slot_start, $expected_slot_end) {

        if ($this->formattedConsiderableDays[$week_day]['type'] == '1') {
            $interval = $this->formattedConsiderableDays[$week_day]['duration'];
        } else {
            $interval = $this->formattedConsiderableDays[$week_day]['break'];
        }
        $duration = $this->formattedConsiderableDays[$week_day]['duration'];
        for ($start_time = $expected_slot_start; $start_time < $expected_slot_end; $start_time = $start_time + $interval) {
            if ($this->isTheSlotAvailable($week_day, $start_time, $start_time + $duration)) {
                return true;
            }
        }
        return false;
    }

    function searchForAnyAvailability() {

        foreach ($this->formattedConsiderableDays as $week_day => $day_slot) {
            $expected_slot_start_array = explode(':', $day_slot['expected_slot'][0]);
            $expected_slot_start = ($expected_slot_start_array[0] * 60) + $expected_slot_start_array[1];
            $expected_slot_end_array = explode(':', $day_slot['expected_slot'][1]);
            $expected_slot_end = ($expected_slot_end_array[0] * 60) + $expected_slot_end_array[1];
            if ($this->isAvailableParticularSlot($week_day, $day_slot['booked_slot'], $expected_slot_start, $expected_slot_end)) {
                $this->updateAvailabilityOfSlot($week_day);
            }
        }

        foreach ($this->formattedConsiderableDays as $week_day => $day_slot) {
            if ($day_slot['available'])
                return true;
        }
    }

    function isThereAnyAvailableSlot() {
        $isSlotAvailble = $this->searchForAnyAvailability();
        if ($isSlotAvailble == true)
            return true;
        else
            return false;
    }

    function getFirstDayOfNextWeek() {
        return $firstDayOfWeek;
    }

    function getLastDayOfNextWeek() {
        return $lastDayOfWeek;
    }

    function getAvailableSlots($availableSlotsArray) {
        foreach ($this->formattedConsiderableDays as $week_day => $day_slot) {
            if(isset($availableSlotsArray[$week_day]['num_of_availability'])){
                $availableSlotsArray[$week_day]['num_of_availability'] += $day_slot['num_of_availability'];
            }else{
                $availableSlotsArray[$week_day]['num_of_availability'] = $day_slot['num_of_availability'];
            }
            $availableSlotsArray[$week_day]['full_week_day'] = $day_slot['full_week_day'];
            $availableSlotsArray[$week_day]['day'] = $day_slot['day'];
            $availableSlotsArray[$week_day]['month'] = $day_slot['month'];
            $availableSlotsArray[$week_day]['year'] = $day_slot['year'];
            $availableSlotsArray[$week_day]['full_date'] = $day_slot['full_date'];
        }
        return $availableSlotsArray;
    }

    function generateAvailableSlotHtml() {

        return $generatedContent;
    }

}
