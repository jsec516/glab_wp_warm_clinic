<?php
require('Uploader.php');

$upload_dir = '/img_uploads/';
$valid_extensions = array('mp3');

$Upload = new FileUpload('att_file');
$ext = $Upload->getExtension(); // Get the extension of the uploaded file
$Upload->newFileName = uniqid('glab_').'.'.$ext;
$result = $Upload->handleUpload($upload_dir, $valid_extensions);

if (!$result) {
    echo json_encode(array('success' => false, 'msg' => $Upload->getErrorMsg()));   
} else {
    echo json_encode(array('success' => true, 'file' => $Upload->getFileName()));
}
