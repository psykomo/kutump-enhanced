<?php

/**
 * manage Table CatalogFolder
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_CatalogFolder extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuCatalogFolder'; 
	//protected $_dependentTables = array('');
	protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'catalogGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'Folder' => array(
            'columns'           => 'folderGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Folder',
            'refColumns'        => 'guid'
        )
    );
    public function delete($where)
    {
    	return parent::delete($where);
    }
    function countCatalogsInFolder($folderGuid)
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuCatalog,KutuCatalogFolder 
    	where KutuCatalog.guid=KutuCatalogFolder.catalogGuid AND KutuCatalogFolder.folderGuid='$folderGuid'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countFolder($folderGuid)
    {
    	$db = $this->_db->query("SELECT count(*) count FROM KutuFolder WHERE path LIKE '%$folderGuid%' AND title IN('Daftar promosi','Users','Tambah promosi')");

    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    	
    }
    function countUsersInFolder()
    {
    	$db = $this->_db->query("SELECT COUNT(*) count FROM KutuUser");

    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
   	}
    function countCatalogsInFolderAndChildren($folderGuid)
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuFolder,KutuCatalogFolder, KutuCatalog 
    	where KutuCatalog.guid=KutuCatalogFolder.catalogGuid AND KutuFolder.guid=KutuCatalogFolder.folderGuid AND KutuFolder.path LIKE '%$folderGuid%' AND KutuFolder.title NOT IN('Tambah promosi')");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']) + $this->countCatalogsInFolder($folderGuid) + $this->countFolder($folderGuid);
    }
}
