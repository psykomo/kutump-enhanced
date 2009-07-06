<?php
class Kutu_Cms_Bpm_Menu
{
	public function getMenu($node='root')
	{
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$parentGuid = $node;
		
		if($parentGuid == 'root')
    	{
    		return $tblFolder->fetchAll("parentGuid=guid AND (cmsParams like '%".'"menu":true'."%' OR cmsParams is NULL OR cmsParams = '')",'viewOrder ASC');
    	}
    	else 
    	{
			return $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid AND (cmsParams like '%".'"menu":true'."%' OR cmsParams is NULL OR cmsParams = '')",'viewOrder ASC');
    	}
	}
	public function generateUrlPath($guid)
	{
		
	}
}
?>