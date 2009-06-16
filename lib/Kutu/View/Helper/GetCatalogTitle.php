<?php

/**
 * Helper viewer Catalog Title
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_View_Helper_GetCatalogTitle
{
	public function getCatalogTitle($catalogGuid)
	{ 
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value) && !empty($rowCatalogAttribute->value))
			return $rowCatalogAttribute->value;
		else
			return 'No-Title';
	}
}

?>