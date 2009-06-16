<?php

class Kutu_Core_Orm_Table_Folder extends Zend_Db_Table_Abstract  
{ 
    protected $_name = 'KutuFolder'; 
    protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Folder';
    //protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_Folder';
    protected $_dependentTables = array('Kutu_Core_Orm_Table_CatalogFolder');
    
    public function insert (array $data)
    {   
		return parent::insert($data);
    }
    public function update (array $data, $where)
    {
		return parent::update($data, $where);
    }
    public function fetchChildren($parentGuid)
    {
    	if($parentGuid == 'root')
    	{
    		return $this->fetchAll("parentGuid=guid",'title ASC');
    	}
    	else 
    	{
			return $this->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid",'title ASC');
    	}
    }
	//DEPRECATED. Use createRow.
    function createNew()
    {
    	return $this->createRow(array('guid'=>''));
    }
} 
