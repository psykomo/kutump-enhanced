<?php

/**
 * manage Table CatalogAttribute
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_CatalogAttribute extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuCatalogAttribute'; 
	//protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Catalog';
    protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_CatalogAttribute';
	protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'catalogGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
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
    	if(empty($data['guid']))
    	{
    		$guidMan = new Kutu_Core_Guid();
    		$data['guid'] = $guidMan->generateGuid();
    	}
    	
    	return parent::insert($data);
    }
    public function delete ($where)
    {
    	return parent::delete($where);
    }
}

?>