<?php

class Kutu_Core_Orm_Table_Row_Tree extends Zend_Db_Table_Row_Abstract 
{
	public function findParentRowCategory()
	{
		return $this->findParentRow('Kutu_Core_Orm_Table_Category');
	}
}