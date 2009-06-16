<?php
/**
 * module Access Control List (ACL) 
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Acl
{
	static function manager()
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
}