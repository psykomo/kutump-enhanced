<?php

/**
 * manage Catalog
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Model_Catalog
{
	protected $_tblCatalog;
	protected $_rowCatalog;
	protected $_aRowCatalogAttribute;
	protected $_aRowCatalogFolder;
	
	protected $_stored;
	
	function __construct($rowCatalog=null)
	{
		$this->_tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		if(isset($rowCatalog) && $rowCatalog instanceof Kutu_Core_Orm_Table_Row_Catalog)
		{
			$this->_rowCatalog = $rowCatalog;
			$this->_stored = true;
		}
		else 
		{
			$this->_rowCatalog = $this->_tblCatalog->createRow();
			$this->_stored = false;
		}
	}
	function __set($name, $value)
	{
		switch ($name)
		{
			case 'guid':
				$this->_rowCatalog->guid = $value;
				break;
			case 'profileGuid':
				$this->_rowCatalog->profileGuid = $value;
				break;
			default:
				if(!isset($this->_aRowCatalogAttribute))
				{
					$this->_populateCatalogAttribute();
				}
				if(!isset($this->_aRowCatalogAttribute[$name]))
				{
					$this->_rowCatalog->$name = $value;
				}
				else 
				{
					//if(isset($this->_aRowCatalogAttribute[$name]))
					$this->_aRowCatalogAttribute[$name]->value = $value;
				}
		}
	}
	
	function __get($name)
	{
		switch ($name)
		{
			case 'guid':
				return $this->_rowCatalog->guid;
			case 'profileGuid':
				return $this->_rowCatalog->profileGuid;
			default:
				if(!isset($this->_aRowCatalogAttribute))
				{
					$this->_populateCatalogAttribute();
				}
				if(!isset($this->_aRowCatalogAttribute[$name]))
				{
					return $this->_rowCatalog->$name;
				}
				else 
				{
					//if(isset($this->_aRowCatalogAttribute[$name]))
					return $this->_aRowCatalogAttribute[$name]->value;
					//else 
					//throw new Zend_Exception("Object member $name can't be found", 'NOTICE');
				}
		}
	}
	function __isset($nm)
	{
		echo 'isset';
	}

	protected function _populateCatalogAttribute()
	{
		$this->_aRowCatalogAttribute = array();
		$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$profileGuid = $this->_rowCatalog->profileGuid;
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
		$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		
		$rowsetCatalogAttribute = $this->_rowCatalog->findDependentRowsetCatalogAttribute();
		foreach ($rowsetProfileAttribute as $rowProfileAttribute)
		{
			if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
			{
				$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);
				//array_push($this->_aRowCatalogAttribute);
				$this->_aRowCatalogAttribute[$rowCatalogAttribute->attributeGuid] = $rowCatalogAttribute;
				//echo "rowcatalogattribute:" . $rowCatalogAttribute->attributeGuid;
			}
			else 
			{
				$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $this->_rowCatalog->guid;
				$rowCatalogAttribute->attributeGuid = $rowProfileAttribute->attributeGuid;
				$this->_aRowCatalogAttribute[$rowCatalogAttribute->attributeGuid] = $rowCatalogAttribute;
				
			}
		}
	}
	function save()
	{
		//$this->_rowCatalog->save();
		echo $this->fixedTitle;
		foreach ($this->_aRowCatalogAttribute as $row)
		{
			echo $this->guid.' '.$row->catalogGuid . ' '.$row->attributeGuid . '<br>';
		}
		
		$this->_stored = true;
	}
	function addToFolder($folderGuid)
	{
		if($this->_stored)
		{
			//save the addition to DB
		}
	}
	function removeFromFolder($folderGuid)
	{
		
	}
}

?>