<?php

class glab_convert_helper {

    static function to_minute($hours, $minutes = '0') {
        if (strpos($hours, ':') !== false) {
            list($hours, $minutes) = explode(':', $hours);
        }
        $hours_in_minute = $hours * 60;
        return $hours_in_minute + $minutes;
    }

    static function only_hours($duration) {
        $hours = $duration / 60;
        return floor($hours);
    }

    static function only_minutes($duration) {
        $minutes = $duration % 60;
        return $minutes;
    }

    static function is_off_day($args) {
        if (strpos($args, 'OFF') != false) {
            return true;
        } else {
            return false;
        }
    }

    static function user_readable_prac_schedule($data) {
        if ($data != '') {
            $hours = explode('-', $data);
            if (is_array($hours)) {
                $start_hour = $hours[0];
                $end_hour = $hours[1];
                if ($start_hour != '' && $end_hour != '') {
                    $begins = explode(':', $start_hour);
                    $ends = explode(':', $end_hour);
                    if (is_array($begins) && is_array($ends)) {
                        $start_meridian = 'AM';
                        $end_meridian = 'PM';

                        if ($ends[3] == 'OFF') {
                            return 'CLOSED';
                        } else {
                            if ($begins[0] > 12) {
                                $begins[0] = $begins[0] - 12;
                                $begins[3] = 'PM';
                            }
                            if ($ends[0] > 12) {
                                $ends[0] = $ends[0] - 12;
                                $ends[3] = 'PM';
                            }

                            return str_pad($begins[0], 2, '0', STR_PAD_LEFT) . ':' . $begins[1] . ' ' . $start_meridian . ' TO ' . str_pad($ends[0], 2, '0', STR_PAD_LEFT) . ':' . $ends[1] . ' ' . $end_meridian;
                        }
                    } else {
                        return 'CLOSED';
                    }
                } else {
                    return 'CLOSED';
                }
            } else {
                return 'CLOSED';
            }
        } else {
            return 'CLOSED';
        }
    }

    static function show_schedule_data($schedule) {
        $label = '';
        if ($schedule) {
            $parts = explode("-", $schedule);

            if (self::is_off_day($schedule)) {
                $label = "CLOSED";
            } else {
                $start_meridian = glab_html_helper::get_meridian($schedule, 'start');
                $end_meridian = glab_html_helper::get_meridian($schedule, 'end');
                $start_hour = substr($schedule, 0, 2);
                $end_hour = substr($schedule, 10, 2);
                if ($start_hour > 12)
                    $start_hour-=12;
                if ($end_hour > 12)
                    $end_hour-=12;
                $label = sprintf("%02d", $start_hour) . ':' . substr($schedule, 3, 2) . ':' . $start_meridian . '-';
                $label.=sprintf("%02d", $end_hour) . ':' . substr($schedule, 13, 2) . ':' . $end_meridian;
            }
        }else {
            $label = 'NOT SET';
        }
        //if($label)
        return $label;
    }

    static function convert_to_standard_time($time) {
        $meridiem_status = 'AM';
        $formatted_hour = $time;
        if ($formatted_hour >= 12) {
            if ($formatted_hour > 12)
                $formatted_hour = $formatted_hour - 12;
            $meridiem_status = 'PM';
        }
        $formatted_hour = sprintf("%02s", $formatted_hour);
        return $formatted_hour . ' ' . $meridiem_status;
    }

    static function convert_to_working_hour($dayHourStr) {
        $working_hour = 0;
        if (empty($dayHourStr))
            return $working_hour;

        $dayHourArr = explode('-', $dayHourStr);
        $beginHourArr = explode(':', $dayHourArr[0]);
        $endHourArr = explode(':', $dayHourArr[1]);

        if ($beginHourArr[0] != '' AND $endHourArr[0] != '') {
            $working_hour = $endHourArr[0] - $beginHourArr[0];
            if ($endHourArr[1] > $beginHourArr[1]) {
                $working_hour+=.5;
            } elseif ($endHourArr[1] == $beginHourArr[1]) {
                $working_hour+=0;
            } else {
                $working_hour-=.5;
            }
        }
        return $working_hour;
    }

    static function convert_24_to_12($hour_in_24, $with_meridian = false) {
        $hour_in_12 = null;
        if (!$with_meridian) {
            if ($hour_in_24[0] >= 13) {
                $hour_in_12 = ($hour_in_24[0] - 12) . ':' . $hour_in_24[1];
            } else {
                $hour_in_12 = $hour_in_24[0] . ':' . $hour_in_24[1];
            }
        }else{
            $hour_in_12=  strtoupper(date("g:i:a", strtotime($hour_in_24[0] . ':' . $hour_in_24[1])));
        }

        return $hour_in_12;
    }

    static function convert_12_to_24($hour_in_12, $with_meridian = true) {
        $data = array();

        if ($with_meridian) {
            if (strtolower($hour_in_12['begin_time'][2]) == 'pm' AND $hour_in_12['begin_time'][0] != 12) {
                $data['appTime'] = $hour_in_12['begin_time'][0] + 12;
                $data['appTime1'] = $hour_in_12['begin_time'][1];
            } else {
                $data['appTime'] = $hour_in_12['begin_time'][0];
                $data['appTime1'] = $hour_in_12['begin_time'][1];
            }
        } else {
            $data['appTime'] = $hour_in_12['begin_time'][0];
            $data['appTime1'] = $hour_in_12['begin_time'][1];
        }

        return $data;
    }

    static function convert_to_timeformatdb($date) {
        $replace_string = str_replace("/", "-", $date);
        return $replace_string;
    }

    static function convert_minutes_2_hours($Minutes) {
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

    static function format_valid_time($timeToFormat) {
        $timeSplit = explode(":", $timeToFormat);
        $timeSplit[0] = (strlen($timeSplit[0]) < 2) ? ('0' . $timeSplit[0]) : $timeSplit[0];
        $timeSplit[1] = substr($timeSplit[1], 0, strlen($timeSplit[1]) - 2) . ' ' . strtoupper(substr($timeSplit[1], -2, 2));
        return $timeSplit[0] . ":" . $timeSplit[1];
    }

    static function prepare_wait_interval($data, $day, $week_days) {
        if (!empty($data) AND ! empty($day)) {

            $startHour = trim($data[$week_days[$day] . '_s_h']);
            $startMinute = trim($data[$week_days[$day] . '_s_m']);
            $endHour = trim($data[$week_days[$day] . '_d_h']);
            $endMinute = trim($data[$week_days[$day] . '_d_m']);
            if ($startHour . ':' . $startMinute == $endHour . ':' . $endMinute) {
                return sprintf('%02d', $data[$week_days[$day] . '_s_h']) . ':' . sprintf('%02d', $data[$week_days[$day] . '_s_m']) . '-' . sprintf('%02d', ($data[$week_days[$day] . '_d_h'] + 1)) . ':' . sprintf('%02d', $data[$week_days[$day] . '_d_m']);
            } else {
                return sprintf('%02d', $data[$week_days[$day] . '_s_h']) . ':' . sprintf('%02d', $data[$week_days[$day] . '_s_m']) . '-' . sprintf('%02d', $data[$week_days[$day] . '_d_h']) . ':' . sprintf('%02d', $data[$week_days[$day] . '_d_m']);
            }
        } else {
            return '';
        }
    }

    static function get_numeric_time($app_time) {
        // app time expects value like 06:00pm
        $numeric_time = 0;
        // process here
        $minutes = 0;
        $hours = $app_time;
        if (strpos($hours, ':') !== false) {
            // Split hours and minutes.
            list($hours, $minutes) = explode(':', $hours);
        }
        $meridian = substr($minutes, 2, 2);
        $minutes = substr($minutes, 0, 2);
        if ($meridian == 'pm' && $hours < 12) {
            $hours+=12;
        }
        $minutes = $hours * 60 + $minutes; // 1110 for 06:30pm
        if ($minutes < 0) {
            $Min = abs($minutes);
        } else {
            $Min = $minutes;
        }
        $iHours = floor($Min / 60); // 18 for 06:30pm
        $Minutes = ($Min - ($iHours * 60)) / 100; //0.3 for 06:30pm
        $numeric_time = $iHours + $Minutes; //18.3 for 06:30pm
        return $numeric_time;
    }

}
