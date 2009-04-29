<?php
/**
 * Defining bootstrap
 */

$db = 'Zend_Db_Adapter'; // Leave blank if you set Zend_Db_Table default adapter
$cache = 'Zend_Cache';
$options = array(
    'z-index'           => 255,
    'jquery_path'       => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
    'image_path'        => '/images/debugbar' // set to false if you want to use no images
);

$debug = new Scienta_Controller_Plugin_Debug($options);
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Variables());
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Database($db));
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Memory());
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_File('/home/web32/buam/newdev'));
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Time());
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Cache($cache->getBackend()));
$debug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Exception());
$controller->registerPlugin($debug);

/**
 * Registering other plugins and start dispatch
 */
