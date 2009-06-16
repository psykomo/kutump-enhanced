<?php

/**
 * Helper viewer Catalog Title
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_View_Helper_GetCatalogDownloadTitle
{
	public function GetCatalogDownloadTitle($catalogGuid)
	{ 
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);
		
		if(count($rowsetCatalog))
		{
			$rowCatalog = $rowsetCatalog->current();
			$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			$oriName = $rowsetCatAtt->findByAttributeGuid('docOriginalName')->value;
		}
		else 
		{
			$oriName = 'No-Title';
		}
		
		return $oriName;
	}
}

?>