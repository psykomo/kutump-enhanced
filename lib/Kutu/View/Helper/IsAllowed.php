<?php

/**
 * check if User is allowed to view
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_View_Helper_IsAllowed
{
	public function isAllowed($itemGuid, $action, $section='content')
	{
		$auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) { 
            return false;
        }
		$aclMan = Kutu_Acl_Manager::getAdapter();
		return $aclMan->isAllowed($auth->getIdentity()->username, $itemGuid, $action, $section);
	}
}

?>