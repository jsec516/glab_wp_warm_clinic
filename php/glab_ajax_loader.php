<?php

/*
 * file name: glab_ajax_loader.php
 * 
 * purpose: load all file required to perform ajax action
 */


/*********************************************************
 *  load all models
 *********************************************************/
require_once 'glab_service_layer.php';
require_once 'glab_customer_layer.php';
require_once 'glab_clinic_layer.php';
require_once 'glab_practitioner_layer.php';
require_once 'glab_appointment_layer.php';

/*********************************************************
 *  load all helpers
 *********************************************************/
require_once 'helper/glab_html_helper.php';
require_once 'helper/glab_convert_helper.php';
require_once 'helper/glab_slot_helper.php';

/*********************************************************
 *  load all form controller
 *********************************************************/