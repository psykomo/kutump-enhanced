<?php

class Kutu_Form_Helper_ClinicInputGenerator
{
	function generateFormAdd($profileGuid, $folderGuid=null)
	{
		if(empty($folderGuid))
			throw new Zend_Exception('Kutu_Form_Helper_ClinicInputGenerator: Can not generate form with empty folderGuid');
		
		$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
		$rows = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$i = 0;
		foreach ($rows as $row)
		{
			$row3 = $row->findParentRow('Kutu_Core_Orm_Table_Attribute');
			$attributeRenderer = new Kutu_Form_Attribute_Renderer($row3->guid,null,$row3->type,null);
			if ($row3->description == 'Content' || $row3->description == 'Category' || $row3->description == 'Name') continue;
			$aRenderedAttributes[$row3->guid]['description'] = $row3->description;
			$aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
			$i++;
		}
		
		$aBaseAttributes['profileGuid']['description'] = '';
		$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='$profileGuid'>";
		$aBaseAttributes['folderGuid']['description'] = '';
		$aBaseAttributes['folderGuid']['form'] = "<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";
		$aBaseAttributes['status']['description'] = '';
		$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='0'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
	function generateFormAnswer($catalogGuid)
	{
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
				$guidMan = new Kutu_Core_Guid();
				$catalogAttributeGuid = $guidMan->generateGuid();
			}
				
			$attributeRenderer = new Kutu_Form_Attribute_Renderer($rowAttribute->guid, $attributeValue, $rowAttribute->type,null,'clinic','clinic_category');
			
			$aRenderedAttributes[$rowAttribute->guid]['description'] = $rowAttribute->description;
			$aRenderedAttributes[$rowAttribute->guid]['form'] = $attributeRenderer->render();
			$i++;
			
		}
		
		$aBaseAttributes['guid']['description'] = '';
		$aBaseAttributes['guid']['form'] = "<input type='hidden' name='guid' id='guid' value='$rowCatalog->guid'>";
//		$aBaseAttributes['shortTitle']['description'] = 'shortTitle';
//		$aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1'' cols='50'>$rowCatalog->shortTitle</textarea>";
		$aBaseAttributes['profileGuid']['description'] = '';
		$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='$rowCatalog->profileGuid'>";
		
		$aBaseAttributes['profileGuid']['description'] = 'Sender';
		$aBaseAttributes['profileGuid']['form'] = "$rowCatalog->createdBy";
		
//		$s = '<input type="Text" id="publishedDate" maxlength="25" size="25" name="publishedDate" value="'.$rowCatalog->publishedDate.'"><a href="javascript:NewCal(\'publishedDate\',\'yyyymmdd\',true,24)"><img src="'.KUTU_ROOT_URL.'/mix_lib/extjs/resources/images/default/custom/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
//		$aBaseAttributes['publishedDate']['description'] = 'Published Date';
//		$aBaseAttributes['publishedDate']['form'] = $s;
		
//		$n = '<input type="Text" id="expiredDate" maxlength="25" size="25" name="expiredDate" value="'.$rowCatalog->expiredDate.'"><a href="javascript:NewCal(\'expiredDate\',\'yyyymmdd\',true,24)"><img src="'.KUTU_ROOT_URL.'/mix_lib/extjs/resources/images/default/custom/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
//		$aBaseAttributes['expiredDate']['description'] = 'Expired Date';
//		$aBaseAttributes['expiredDate']['form'] = $n;
		
		$aBaseAttributes['status']['description'] = '';
		$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='$rowCatalog->status'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
}

?>