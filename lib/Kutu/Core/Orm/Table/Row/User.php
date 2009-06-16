<?php

/**
 * manage Table_Row_User
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Row_User extends Zend_Db_Table_Row_Abstract
{
	protected function _insert()
	{
		if (empty($this->guid))
		{
			$generateGuid = new Kutu_Core_Guid();
			$this->guid = $generateGuid->generateGuid();
		}
		$today = date('Y-m-d h:i:s');
		
		$this->createdDate = $today;
		$this->modifiedDate = $today;
		
		$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
			$this->createdBy = $userName;
			$this->modifiedBy = $userName;
		}
		else
		{
			$this->createdBy = $this->username;
			$this->modifiedBy = $this->username;
		}
	}
	protected function _postInsert()
	{
		$acl = Kutu_Acl_Manager::getAdapter();
		$acl->addUser($this->username);
		
	}
	protected function _update()
	{
    	$this->modifiedDate = date("Y-m-d h:i:s");
    	
    	$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
		}
		$this->modifiedBy = $userName;
	}
	/*protected function _postDelete()
	{
		//delete from table KutuUserInvoice
		$tblInvoice = new Kutu_Core_Orm_Table_Invoice();
		$tblInvoice->delete("memberid='$this->guid'");
		//delete from table KutuUserAccessLog
		$tblUserLog = new Kutu_Core_Orm_Table_UserLog();
		$tblUserLog->delete("user_id='$this->guid'");
		//delete from ACL
		$aclMan = new Kutu_Acl_Adapter_Local();
		$aclMan->deleteUser($this->username);
	}
	public function findParentRowPacked()
	{
		return $this->findParentRow('Kutu_Core_Orm_Table_Packed');
	}
	public function findParentRowStatus()
	{
		return $this->findParentRow('Kutu_Core_Orm_Table_Userstatus');
	}*/
}

?>