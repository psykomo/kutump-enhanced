<?php
class Hap_Model_Table_CatalogDetail extends Zend_Db_Table_Abstract 
{
	protected $_name = 'CatalogDetail'; 
	protected $_rowClass = 'Hap_Model_Table_Row_CatalogDetail';
    protected $_rowsetClass = 'Hap_Model_Table_Rowset_CatalogDetail';
	protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'catalogGuid',
            'refTableClass'     => 'Hap_Model_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'Attribute' => array(
            'columns'           => 'attributeGuid',
            'refTableClass'     => 'Hap_Model_Table_CatalogAttribute',
            'refColumns'        => 'guid'
        )
    );
}
?>