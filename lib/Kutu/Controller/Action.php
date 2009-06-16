<?php

/**
 * manage session for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Controller_Action extends Zend_Controller_Action 
{
	function preDispatch() 
    { 
        
    } 
    
	function getBaseDir()
	{
		$front =  $this->getRequest();
		$request = $this->getRequest();
        $module  = $request->getModuleName();
        $dirs    = $this->getFrontController()->getControllerDirectory();
        if (empty($module) || !isset($dirs[$module])) {
            $module = $this->getFrontController()->getDispatcher()->getDefaultModule();
        }
        $baseDir = dirname($dirs[$module]);
        return $baseDir;
	}
	function getModuleDir()
	{
		$front = Zend_Controller_Front::getInstance(); 
	    $request = $front->getRequest(); 
	    $dir = $front->getModuleDirectory($request->getModuleName()); 
	    return $dir;
	}
	
}

?>