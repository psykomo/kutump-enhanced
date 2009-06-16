<?php

/**
 * module Access Control List (ACL) 
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Acl_Manager
{
	/**
	 * factory()
	 *
	 * Removes an Object from a group.
	 *
	 * @return bool Returns TRUE if successful, FALSE otherwise
	 *
	 * @param string username
	 * @param string groupValue
	 */
	static function getAdapter()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		switch (strtolower($config->acl->adapter))
		{
			case 'phpgacl':
				$aclAdapter = new Kutu_Acl_Adapter_PhpGacl();
				return $aclAdapter;
			default :
				throw new Zend_Exception('Kutu_Acl_Manager does not support adapter: '. $config->acl->adapter. '. Please check your configuration.', 101);
		}
		
	}
	static function manager()
	{
		return Kutu_Acl_Manager::getAdapter();
	}
}