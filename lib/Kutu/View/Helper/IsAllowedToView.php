<?php
class Kutu_View_Helper_IsAllowedToView
{
	public function isAllowedToView($itemGuid)
	{
		$auth = Zend_Auth::getInstance();
		
		$aclMan = Kutu_Acl_Manager::getAdapter();
		
        if (!$auth->hasIdentity()) { 
        	return $aclMan->getPermissionsOnContent('','everyone', $itemGuid);         
        }
		else {		
			$aReturn = $aclMan->getUserGroupIds($auth->getIdentity()->username);
			return $aclMan->getPermissionsOnContent('',$aReturn[1], $itemGuid);
		}
	}
}
?>