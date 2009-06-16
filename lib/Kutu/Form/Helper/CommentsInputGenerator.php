<?php

class Kutu_Form_Helper_CommentsInputGenerator
{
	function generateFormAdd($profileGuid, $folderGuid=null, $relatedGuid=null)
	{ 
		if(empty($folderGuid))
			throw new Zend_Exception('Kutu_Form_Helper_CommentsInputGenerator: Can not generate form with empty folderGuid');
		
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
			$aRenderedAttributes[$row3->guid]['description'] = $row3->description;
			$aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
			$i++;
		}
		
		$aBaseAttributes['relatedGuid']['description'] = '';
		$aBaseAttributes['relatedGuid']['form'] = "<input type='hidden' name='relatedGuid' id='relatedGuid' value='$relatedGuid'>";
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
}

?>