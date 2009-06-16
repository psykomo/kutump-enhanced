<?php

class Kutu_Core_Orm_Table_Row_DocumentProfile extends Zend_Db_Table_Row_Abstract 
{
	public function findParentRowDocument()
	{
		return $this->findParentRow('Kutu_Core_Orm_Table_Document');
	}
}