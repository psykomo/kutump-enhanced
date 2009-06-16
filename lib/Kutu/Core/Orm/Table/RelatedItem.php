<?php

/**
 * manage Table KutuRelatedItem
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_RelatedItem extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuRelatedItem';
	protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'itemGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'RelatedCatalog' => array(
            'columns'           => 'relatedGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
            'refColumns'        => 'guid'
        )
    );
    
    public function insert (array $data)
    {
    	return parent::insert($data);
    }
    function createNew()
    {
    	return $this->createRow(array('itemGuid'=>'', 'relatedGuid'=>'','relateAs'=>''));
    }
	public function getSumComment($relatedGuid, $relateAs)
	{
		$db = $this->_db->query("SELECT sum(valueIntRelation) as sum_hits FROM KutuRelatedItem WHERE relatedGuid ='".$relatedGuid."' AND relateAs ='".$relateAs."'");
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		return ($dataFetch[0]['sum_hits'])? $dataFetch[0]['sum_hits'] : 0;
	}
}
?>