<?php
class Kutu_Cms_Bpm_Folder
{
	public function fetchCatalogs($folderGuid,$offset = 0 ,$limit = 0)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();

		//$select = $tblCatalog->select();
		$select = $tblCatalog->select();
		$select->setIntegrityCheck(false);
		$select->from(array('kc' => 'KutuCatalog'))
				->join(array('kcf' => 'KutuCatalogFolder'),
						'kc.guid = kcf.catalogGuid', array())
				->where('kcf.folderGuid = ?', $folderGuid)
				->order('kc.createdDate DESC')->limit($limit, $offset);

		//print_r($select->__toString()); die();
		return $tblCatalog->fetchAll($select);
	}
}
?>