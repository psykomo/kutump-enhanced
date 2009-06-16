<?php

/**
 * manage Catalog
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Model_Factory_Catalog
{
	static function create($profileGuid, $folderGuid, $shortTitle='', $publishedDate='', $expiredDate='', $status='0')
	{
		$guidMan = new Kutu_Core_Guid();
		$catalog = new Kutu_Core_Model_Catalog();
		$catalog->guid = $guidMan->generateGuid();
		$catalog->profileGuid = $profileGuid;
		$catalog->addToFolder($folderGuid);
		$catalog->shortTitle = $shortTitle;
		$catalog->publishedDate = ($publishedDate)? $publishedDate : date('Y-m-d H:i:s');
		$catalog->expiredDate = ($expiredDate)? $expiredDate : '0000-00-00 00:00:00';
		$catalog->status = $status;
		
		$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
		}
		$catalog->createdBy = $userName;
		
		return $catalog;
	}
	static function find($catalogGuid)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);
		if($rowsetCatalog->count())
		{
			return new Kutu_Core_Model_Catalog($rowsetCatalog->current());
		}
		else 
		{
			return false;
		
		}
	}
}

?>