<?php

/**
 * manage Table KutuAttribute
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Attribute extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuAttribute'; 
	protected $_dependentTables = array('Kutu_Core_Orm_Table_CatalogAttribute','Kutu_Core_Orm_Table_ProfileAttribute');
}
