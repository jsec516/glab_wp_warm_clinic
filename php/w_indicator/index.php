<?php

require_once 'config.php';
require_once 'class.dal.php';
require_once 'class.sevendays.php';

function getWaitingListIndicationHtml() {
    $waitlist = new SEVENDAYS();
    $waitListHtml = '';
    $availableSlotsArray = array();
    $waiting_slots = $waitlist->getWaitingSlots();    if (!is_array($waiting_slots) or empty($waiting_slots))
        return array();

    foreach ($waiting_slots as $slot) {    	    	
        $expected_slot_array = $waitlist->convertSlotToArray($slot, '-');

        $bookedSlotInWeek = $waitlist->getBookedSlotInWeek();
        if ($waitlist->isThereAnyAvailableSlot()) {
            $availableSlotsArray = $waitlist->getAvailableSlots($availableSlotsArray);
        } else {
            
        }
    }

    return $availableSlotsArray;
}
