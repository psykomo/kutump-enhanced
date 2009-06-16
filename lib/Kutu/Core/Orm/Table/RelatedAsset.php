<?php
class Kutu_Core_Orm_Table_RelatedAsset extends Zend_Db_Table_Abstract  
{ 
    protected $_name = 'KutuRelatedAsset'; 
    //protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Folder';
    //protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_Folder';
    //protected $_dependentTables = array('Kutu_Core_Orm_Table_CatalogFolder');
    
    public function insert (array $data)
    {    	
    	//return parent::insert($data);
    }
    public function update (array $data, $where)
    {
    	//return parent::update($data, $where);
    }
    public function delete ($where)
    {
    	//return parent::delete($where);
    }
} 
?>