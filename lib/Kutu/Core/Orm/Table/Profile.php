<?php
class Kutu_Core_Orm_Table_Profile extends Zend_Db_Table_Abstract  
{ 
    protected $_name = 'KutuProfile'; 
    //protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Folder';
    //protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_Folder';
    protected $_dependentTables = array('Kutu_Core_Orm_Table_ProfileAttribute', 'Kutu_Core_Orm_Table_Catalog');
    
    public function insert (array $data)
    {    	
    	return parent::insert($data);
    }
    public function update (array $data, $where)
    {
    	//return parent::update($data, $where);
    }
    public function delete ($where)
    {
    	//return parent::delete($where);
    }
    function fetchFromProfile()
    {
    	$db = $this->_db->query("SELECT * FROM KutuProfile WHERE title NOT IN('ContentJP', 'Profil Baru', 'Email Confirm', 'Newsletter')");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	$data = array(
    		'table'		=> $this,
    		'data'		=> $dataFetch,
    		'rowClass'	=> $this->_rowClass,
    		'stored'	=> true
    	);
    	Zend_Loader::loadClass($this->_rowsetClass);
    	return new $this->_rowsetClass($data);
    }
    function countTitleInProfile()
    {
    	$db = $this->_db->query("SELECT COUNT(*) count FROM KutuProfile WHERE title NOT IN('ContentJP', 'Profil Baru', 'Email Confirm', 'Newsletter')");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	return ($dataFetch[0]['count']);
    }
} 
?>