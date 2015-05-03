<?php
// path defination

$glab_plug_path=dirname(dirname(__FILE__));
$glab_assets_path=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'assets';
$glab_images_path=$glab_assets_path.DIRECTORY_SEPARATOR.'images';
$glab_css_path=$glab_assets_path.DIRECTORY_SEPARATOR.'css';
$glab_js_path=$glab_assets_path.DIRECTORY_SEPARATOR.'js';
$glab_tool_path=$glab_assets_path.DIRECTORY_SEPARATOR.'tool';
$glab_lib_path=$glab_plug_path.DIRECTORY_SEPARATOR.'php';

// url defination
$glab_plug_url=$_SERVER['SERVER_NAME'].'/wp-content/plugins/glab_clinic';
$glab_assets_url=$glab_plug_url.'/assets';

// path constants 

define('GLAB_PLUGINS_PATH', $glab_plug_path);
define('GLAB_ASSETS_PATH', $glab_assets_path);
define('GLAB_IMAGES_PATH', $glab_images_path);
define('GLAB_CSS_PATH', $glab_css_path);
define('GLAB_JS_PATH', $glab_js_path);
define('GLAB_TOOL_PATH', $glab_tool_path);
define('GLAB_LIB_PATH', $glab_lib_path);

// url constants
define('GLAB_PLUGINS_URL', $glab_plug_url);
define('GLAB_ASSETS_PATH', $glab_assets_url);
