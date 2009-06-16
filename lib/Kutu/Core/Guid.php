<?php

/**
 * module guid for universal prefix
 * 
 * @author Himawan Anindya Putra
 * @package Kutu
 * 
 */

class Kutu_Core_Guid
{
	public function generateGuid($prefix=null)
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		$prefix = $config->guid->prefix; // a universal prefix prefix 
		return uniqid($prefix);;
	}	
}
