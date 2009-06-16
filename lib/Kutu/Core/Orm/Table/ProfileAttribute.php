<?php

/**
 * manage Table ProfileAttribute for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_ProfileAttribute extends Zend_Db_Table_Abstract  
{ 
    protected $_name = 'KutuProfileAttribute'; 
    //protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Folder';
    //protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_Folder';
    //protected $_dependentTables = array('Kutu_Core_Orm_Table_CatalogAttribute');
    protected $_referenceMap    = array(
        'Profile' => array(
            'columns'           => 'profileGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Profile',
            'refColumns'        => 'guid'
        ),
        'Attribute' => array(
            'columns'           => 'attributeGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Attribute',
            'refColumns'        => 'guid'
        )
     );
    
    public function insert (array $data)
    {    	
    	return parent::insert($data);
    }
    public function update (array $data, $where)
    {
    	return parent::update($data, $where);
    }
    public function delete ($where)
    {
    	return parent::delete($where);
    }
}
 
?>