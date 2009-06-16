<?php

class Kutu_View_Helper_GetExpiredDay
{
	public function getExpiredDay()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$tblUsername = new Kutu_Core_Orm_Table_User();
			$rowset = $tblUsername->getExpiredDay($auth->getIdentity()->username,2);
			if ($rowset) return $rowset;
		}
	}
}