<?php

require_once 'php/upload_class.php';
$error = "";
$msg = "";

$fileElementName = 'att_file';
if (!empty($_FILES[$fileElementName]['error'])) {
    switch ($_FILES[$fileElementName]['error']) {
        case '1':
            $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            break;
        case '2':
            $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            break;
        case '3':
            $error = 'The uploaded file was only partially uploaded';
            break;
        case '4':
            $error = 'No file was uploaded.';
            break;

        case '6':
            $error = 'Missing a temporary folder';
            break;
        case '7':
            $error = 'Failed to write file to disk';
            break;
        case '8':
            $error = 'File upload stopped by extension';
            break;
        case '999':
        default:
            $error = 'No error code avaiable';
    }
} elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
    $error = 'No file was uploaded..';
} else {
    /* for uploading file */
    $max_size = 1024 * 1024 * 5; // the max. size for uploading
    $my_upload = new file_upload;
    //$my_upload->upload_dir = $_SERVER['DOCUMENT_ROOT']."/files/new/"; // "files" is the folder for the uploaded files (you have to create this folder)
    $my_upload->upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/upload/";
    //$my_upload->upload_dir=$_SERVER['DOCUMENT_ROOT']."/oscommerce/wp-content/plugins/e_reminder/upload/";
    //$my_upload->upload_dir="../upload/";
    $my_upload->extensions = array(".mp3"); // specify the allowed extensions here
    // $my_upload->extensions = "de"; // use this to switch the messages into an other language (translate first!!!)
    $my_upload->max_length_filename = 50; // change this value to fit your field length in your database (standard 100)
    $my_upload->rename_file = true;
    $my_upload->the_temp_file = $_FILES[$fileElementName]['tmp_name'];
    $my_upload->the_file = $_FILES[$fileElementName]['name'];
    $extension = strtolower(strrchr($_FILES[$fileElementName]['name'], "."));
    $my_upload->http_error = $_FILES[$fileElementName]['error'];
    $my_upload->replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n"; // because only a checked checkboxes is true
    $my_upload->do_filename_check = "y"; // use this boolean to check for a valid filename
    $new_name = "" . md5(uniqid(time())) . "";
    if ($my_upload->upload($new_name)) { // new name is an additional filename information, use this to rename the uploaded file
        //$full_path = $my_upload->upload_dir.$my_upload->file_copy;
        //$info = $my_upload->get_uploaded_file_info($full_path);
        // ... or do something like insert the filename to the database
    }
    //$uploadedFileName=$new_name.strtolower(strrchr($my_upload->the_file,"."));
    /* end of upload code */

    $msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
    $msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);
    //for security reason, we force to remove all uploaded file
    //@unlink($_FILES[$fileElementName]);		
}
/*
  echo "{";
  echo				"error: '" . $error . "',\n";
  echo				"msg: '" . $msg . "',\n";
  echo 				"uploaded_file: '".$new_name.$extension."'\n";
  echo "}"; */
/* added for html value */
if ($error)
    echo $error;
else
    echo $new_name . $extension;
/* end of modification */