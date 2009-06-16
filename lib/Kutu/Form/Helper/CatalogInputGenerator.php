<?php

class Kutu_Form_Helper_CatalogInputGenerator
{
	function generateFormAdd($profileGuid, $folderGuid=null)
	{
		if(empty($folderGuid))
			throw new Zend_Exception('Kutu_Form_Helper_CatalogInputGenerator: Can not generate form with empty folderGuid');
		
		Zend_Loader::loadClass('Kutu_Form_Attribute_Renderer');
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_ProfileAttribute');
		$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
		$rows = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$i = 0;
		foreach ($rows as $row)
		{
			$row3 = $row->findParentRow('Kutu_Core_Orm_Table_Attribute');
			if(!empty($row3))
			{
				$attributeRenderer = new Kutu_Form_Attribute_Renderer($row3->guid,null,$row3->type,null, $profileGuid);
			
				//$aRenderedAttributes[$i]['attributeGuid'] = $row3->guid;
				$aRenderedAttributes[$row3->guid]['description'] = $row3->description;
				$aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
			}
			$i++;
			
		}
		$today = date('Y-m-d H:i:s');
		
		$aBaseAttributes['guid']['description'] = 'Guid';
		$aBaseAttributes['guid']['form'] = "[Auto Generated]"."<input type='text' name='guid' id='guid' value=''>";
		//$aBaseAttributes['shortTitle']['description'] = 'shortTitle';
		//$aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1' cols='50'></textarea>";
		$aBaseAttributes['profileGuid']['description'] = 'Profile';
		$aBaseAttributes['profileGuid']['form'] = $profileGuid."<input type='hidden' name='profileGuid' id='profileGuid' value='$profileGuid'>";
		$aBaseAttributes['folderGuid']['description'] = 'Category';
		$aBaseAttributes['folderGuid']['form'] = $folderGuid."<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";
		//$aBaseAttributes['publishedDate']['description'] = 'Published Date';
		//$aBaseAttributes['publishedDate']['form'] = "<input type='text' name='publishedDate' id='publishedDate' value='$today'>";
		//$aBaseAttributes['expiredDate']['description'] = 'Expired Date';
		//$aBaseAttributes['expiredDate']['form'] = "<input type='text' name='expiredDate' id='expiredDate' value=''>";
		//$aBaseAttributes['createdBy']['description'] = 'Created By';
		//$aBaseAttributes['createdBy']['form'] = "<input type='text' name='createdBy' id='createdBy' value=''>";
		//$aBaseAttributes['modifiedBy']['description'] = 'Modified By';
		//$aBaseAttributes['modifiedBy']['form'] = "<input type='text' name='modifiedBy' id='modifiedBy' value=''>";
		//$aBaseAttributes['modifiedBy']['description'] = 'Modified By';
		//$aBaseAttributes['modifiedBy']['form'] = "<input type='text' name='modifiedBy' id='modifiedBy' value=''>";
		
		
		$aBaseAttributes['createdDate']['description'] = 'Created on';
		$aBaseAttributes['createdDate']['form'] = $today."<input type='hidden' name='createdDate' id='createdDate' value='$today'>";
		$aBaseAttributes['modifiedDate']['description'] = 'Modified on';
		$aBaseAttributes['modifiedDate']['form'] = "0000-00-00 00:00:00"."<input type='hidden' name='modifiedDate' id='modifiedDate' value='0000-00-00 00:00:00'>";
		$aBaseAttributes['deletedDate']['description'] = 'Deleted on';
		$aBaseAttributes['deletedDate']['form'] = "0000-00-00 00:00:00"."<input type='hidden' name='deletedDate' id='deletedDate' value='0000-00-00 00:00:00'>";
		
		$aBaseAttributes['status']['description'] = 'Status';
		
		$aBaseAttributes['price']['description'] = 'Price (in USD)';
		$aBaseAttributes['price']['form'] = "<input type='text' name='price' id='price' value=''>";
		
		require_once(KUTU_ROOT_DIR.'/application/config/master-status.php');
		$statusConfig = MasterStatus::getPublishingStatus();
		
		$aBaseAttributes['status']['form'] = $statusConfig[0]."<input type='hidden' name='status' id='status' value='0'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
	function generateFormEdit($catalogGuid)
	{
		$today = date('Y-m-d H:i:s');
		
		Zend_Loader::loadClass('Kutu_Form_Attribute_Renderer');
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_ProfileAttribute');
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Catalog');
		
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$tableCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tableCatalog->find($catalogGuid);
		$rowCatalog = $rowsetCatalog->current();
		
		$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $rowCatalog->profileGuid);
		$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,array('viewOrder ASC'));
		
		$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		
		$i = 0;
		foreach ($rowsetProfileAttribute as $row)
		{
			$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($row->attributeGuid);
			
			
			$rowAttribute = $row->findParentRow('Kutu_Core_Orm_Table_Attribute');
			if(isset($rowCatalogAttribute->value))
				$attributeValue = $rowCatalogAttribute->value;
			else 
				$attributeValue = '';
			if(isset($rowCatalogAttribute->guid))
				$catalogAttributeGuid = $rowCatalogAttribute->guid;
			else 
			{
				Zend_Loader::loadClass('Kutu_Core_Guid');
				$guidMan = new Kutu_Core_Guid();
				$catalogAttributeGuid = $guidMan->generateGuid();
			}
			if(isset($rowAttribute))
			{
				$attributeRenderer = new Kutu_Form_Attribute_Renderer($rowAttribute->guid, $attributeValue, $rowAttribute->type,null, $rowCatalog->profileGuid);
				
				$aRenderedAttributes[$rowAttribute->guid]['description'] = $rowAttribute->description;
				$aRenderedAttributes[$rowAttribute->guid]['form'] = $attributeRenderer->render();
			}
			$i++;
			
		}
		
		$aBaseAttributes['guid']['description'] = 'Guid';
		$aBaseAttributes['guid']['form'] = $rowCatalog->guid."<input type='hidden' name='guid' id='guid' value='$rowCatalog->guid'>";
		//$aBaseAttributes['shortTitle']['description'] = 'shortTitle';
		//$aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1'' cols='50'>$rowCatalog->shortTitle</textarea>";
		$aBaseAttributes['profileGuid']['description'] = 'Profile';
		$aBaseAttributes['profileGuid']['form'] = $rowCatalog->profileGuid."<input type='hidden' name='profileGuid' id='profileGuid' value='$rowCatalog->profileGuid'>";
		
		//TO DO: I don't think we should put category/folder input here in cataloginputgenerator.
		/*$aBaseAttributes['folderGuid']['description'] = 'Category';
		$aBaseAttributes['folderGuid']['form'] = $folderGuid."<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";*/
		
		//$aBaseAttributes['publishedDate']['description'] = 'Published Date';
		//$aBaseAttributes['publishedDate']['form'] = "<input type='text' name='publishedDate' id='publishedDate' value='$rowCatalog->publishedDate'>";
		//$aBaseAttributes['expiredDate']['description'] = 'Expired Date';
		//$aBaseAttributes['expiredDate']['form'] = "<input type='text' name='expiredDate' id='expiredDate' value='$rowCatalog->expiredDate'>";
		$aBaseAttributes['createdBy']['description'] = 'Created By';
		$aBaseAttributes['createdBy']['form'] = $rowCatalog->createdBy;//"<input type='text' name='createdBy' id='createdBy' value='$rowCatalog->createdBy'>";
		$aBaseAttributes['modifiedBy']['description'] = 'Modified By';
		$aBaseAttributes['modifiedBy']['form'] = $rowCatalog->modifiedBy;//"<input type='text' name='modifiedBy' id='modifiedBy' value='$rowCatalog->modifiedBy'>";
		
		$aBaseAttributes['createdDate']['description'] = 'Created on';
		$aBaseAttributes['createdDate']['form'] = $rowCatalog->createdDate."<input type='hidden' name='createdDate' id='createdDate' value='$rowCatalog->createdDate'>";
		$aBaseAttributes['modifiedDate']['description'] = 'Last Modified on';
		$aBaseAttributes['modifiedDate']['form'] = $rowCatalog->modifiedDate."<input type='hidden' name='modifiedDate' id='modifiedDate' value='$today'>";
		$aBaseAttributes['deletedDate']['description'] = 'Deleted on';
		$aBaseAttributes['deletedDate']['form'] = $rowCatalog->deletedDate."<input type='hidden' name='deletedDate' id='deletedDate' value='$rowCatalog->deletedDate'>";
		
		$aBaseAttributes['status']['description'] = 'Status';
		
		$aBaseAttributes['price']['description'] = 'Price (in USD)';
		$aBaseAttributes['price']['form'] = "<input type='text' name='price' id='price' value='$rowCatalog->price'>";
		
		require_once(KUTU_ROOT_DIR.'/application/config/master-status.php');
		$statusConfig = MasterStatus::getPublishingStatus();
		
		$aBaseAttributes['status']['form'] = $statusConfig[$rowCatalog->status]."<input type='hidden' name='status' id='status' value='$rowCatalog->status'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
		
		
		
	}
}

?>