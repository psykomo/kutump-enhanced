<?php

/**
 * manage Table_Rowset_CatalogAttribute
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Rowset_CatalogAttribute extends Zend_Db_Table_Rowset_Abstract
{
	function findByAttributeGuid($attributeGuid)
	{
        foreach ($this as $row) {
            if ($row->attributeGuid == $attributeGuid) 
            {
                return $row;
            }
        }
        return null;
	}
}
