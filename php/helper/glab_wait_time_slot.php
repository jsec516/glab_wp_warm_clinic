<?php

class glab_wait_time_slot {

    function __construct() {
        $this->prac_id = $_POST['doc'];
        $this->service_id = $_POST['treat'];
        $this->exp_slot_interval = $_POST['duration'];
        $date_arr = explode('-', $_POST['fullydate']);
        $exp_date_timestamp = mktime(0, 0, 0, $date_arr[1], $date_arr[2], $date_arr[0]);
        $this->exp_date = date('Y-m-d', $exp_date_timestamp);
        $this->exp_day_char = strtolower(date('D', $exp_date_timestamp));
        $this->service_layer=new glab_service_layer();
        $this->appointment_layer=new glab_appointment_layer();
        $this->practitioner_layer=new glab_practitioner_layer();
    }

    private function set_service_attr() {
        $row = $this->service_layer->single_info($this->service_id);
        $this->service_type = $row['allow_multiple'];
        $this->service_interval = ($this->service_type == 1)?$row['betn_minutes']:$row['duration'];
        $this->service_duration = $row['duration'];
    }

    private function set_prac_attr() {
        $row = $this->practitioner_layer->get_schedule($this->prac_id, true);
        $schedule_array = explode('-', $row[$this->exp_day_char]);
        $start_time_in_min = $this->hoursToMinutes(substr($schedule_array[0], 0, 8));
        $end_time_in_min = $this->hoursToMinutes(substr($schedule_array[1], 0, 8));
        $this->prac_end_time = glab_convert_helper::to_minute($end_time_in_min);
        $this->interval_gap = ($end_time_in_min - $start_time_in_min) / $this->service_interval;
        $this->prac_blocked_hours = $this->appointment_layer->get_practitioner_blocked_time($this->exp_date, $this->prac_id);
        $appointments = $this->appointment_layer->get_practitioner_appts($this->exp_date, $this->prac_id);
        $this->app_blocked_times = array(); // $treat_time
        foreach ($appointments as $row) {
            $app_interval = $row['app_time'] . "&" . $row['app_end_time'];
            array_push($this->app_blocked_times, $app_interval);
        }
        $this->app_blocked_times_count = count($this->app_blocked_times); // $booking_array_count
    }

    private function set_options_html() {
        $total_wait_time = explode("-", $this->exp_slot_interval);
        $start_timewaiting_2min = $this->hoursToMinutes($total_wait_time[0]);
        $end_timewaiting_2min = $this->hoursToMinutes($total_wait_time[1]);
        $option_html='';
        for ($interval_start = $start_timewaiting_2min; $interval_start <= $end_timewaiting_2min; $interval_start = $interval_start + $this->service_interval) {
            $interval_start_hour = $this->ConvertMinutes2Hours($interval_start);
            $finally = DATE("g:ia", STRTOTIME($interval_start_hour));
            //$avaialable = $this->booked_or_not($interval_start_hour);
            $avaialable = glab_slot_helper::booked_or_not($finally, $this->app_blocked_times_count, $this->app_blocked_times, $this->prac_blocked_hours, $this->service_type);
            if (($avaialable != "booked") and ( ($interval_start + $this->service_duration) <= $this->prac_end_time)){
                $represting_time = glab_convert_helper::format_valid_time($finally);
                $option_html.="<option value='" . $finally . "'>" . $finally . "</option>";
            }
        }
        $this->options_html=$option_html;
    }

    private function print_options_html() {
        echo $this->options_html;
    }

    function get_slot_options() {
        $this->set_service_attr();
        $this->set_prac_attr();
        $this->set_options_html();
        $this->print_options_html();
    }

    private function hoursToMinutes($hours) {
        $minutes = 0;
        if (strpos($hours, ':') !== false) {
            list($hours, $minutes) = explode(':', $hours);
        }
        return $hours * 60 + $minutes;
    }

    private function booked_or_not($time) {   //echo $time;
        $time_in_24 = date("H:i", strtotime($time));
        if (empty($this->prac_blocked_times)) {
            return "not_booked";
        }
        for ($i = 0; $i <= $this->total_prac_blocked_count; $i++) {
            $pieces = explode("&", $this->prac_blocked_times[$i]);
            $start_time = $pieces[0];
            $end_time = $pieces[1];
            $BookingTime = $time_in_24;
            if ($this->check_date_is_within_range($start_time, $end_time, $BookingTime)) {
                return "booked";
            }
        }

        return "not_booked";
    }

    private function check_date_is_within_range($start_date, $end_date, $bookdate) {
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $book_timestamp = strtotime($bookdate);
        if ($this->service_type == '1') {
            return ($book_timestamp == $start_timestamp);
        } else {
            return (($book_timestamp >= $start_timestamp) && ($book_timestamp < $end_timestamp));
        }
    }

    private function ConvertMinutes2Hours($Minutes) {
        if ($Minutes < 0) {
            $Min = abs($Minutes);
        } else {
            $Min = $Minutes;
        }
        $iHours = floor($Min / 60);
        $Minutes = ($Min - ($iHours * 60)) / 100;
        $tHours = $iHours + $Minutes;
        if ($Minutes < 0) {
            $tHours = $tHours * (-1);
        }
        $aHours = explode(".", $tHours);
        $iHours = $aHours[0];
        if (empty($aHours[1])) {
            $aHours[1] = "00";
        }
        $Minutes = $aHours[1];
        if (strlen($Minutes) < 2) {
            $Minutes = $Minutes . "0";
        }
        $tHours = $iHours . ":" . $Minutes;
        return $tHours;
    }

}
