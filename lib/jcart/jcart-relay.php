<?php
error_reporting(E_ALL|E_STRICT); 
define('KUTU_ROOT_DIR', "/Users/n/Documents/Work/Zend/kutump-enhanced");
$paths = array(
realpath(KUTU_ROOT_DIR . '/lib'),
'.'
);
set_include_path(implode(PATH_SEPARATOR, $paths));
//include "Zend/Loader.php"; 		
//Zend_Loader::registerAutoload();

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

	$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/config/config.ini', 'general'); 
	$registry = Zend_Registry::getInstance(); 
	$registry->set('config', $config); 
	$registry->set('files', $_FILES);

	$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
	$saveHandlerManager->setSaveHandler();
	
	
	
// THIS FILE TAKES INPUT FROM AJAX REQUESTS VIA JQUERY post AND get METHODS, THEN PASSES DATA TO JCART
// RETURNS UPDATED CART HTML BACK TO SUBMITTING PAGE

// INCLUDE JCART BEFORE SESSION START

include 'jcart.php';
	
// START SESSION
session_start();

// INITIALIZE JCART AFTER SESSION START
$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();

// PROCESS INPUT AND RETURN UPDATED CART HTML
$cart->display_cart($jcart);

?>
