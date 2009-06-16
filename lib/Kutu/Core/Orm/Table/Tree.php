<?php

class Kutu_Core_Orm_Table_Tree extends Zend_Db_Table_Abstract 
{
	protected $_name = 'tblDMS_CategoryTree';
	protected $_primary = 'CategoryID';
	protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Tree';
	protected $_referenceMap = array(
		'Category' => array(
			'columns'		=> 'CategoryID',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_Category',
			'refColumns'	=> 'CategoryID'
		)
	);
}