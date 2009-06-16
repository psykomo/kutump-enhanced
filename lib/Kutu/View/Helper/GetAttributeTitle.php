<?php
class Kutu_View_Helper_GetAttributeTitle
{
	public function getAttributeTitle($attributeGuid)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Attribute');
		$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
		
		$rowsetAttribute = $tblAttribute->find($attributeGuid);
		if(count($rowsetAttribute))
		{
			$rowAttribute = $rowsetAttribute->current();
			return $rowAttribute->name;
		}
		else 
		{
			return '';
		}
	}
}
?>