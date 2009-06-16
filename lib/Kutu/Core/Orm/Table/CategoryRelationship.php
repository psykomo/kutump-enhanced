<?php

class Kutu_Core_Orm_Table_CategoryRelationship extends Zend_Db_Table_Abstract 
{
	protected $_name = 'tblDMS_CategoryRelationship';
	protected $_primary = array('DocumentID','CategoryID');
}