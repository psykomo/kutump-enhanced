<?php
class Kutu_Core_Bpm_Relation
{
	private $catalogGuid;
	
	//$relateAs can only be: RELATED_ITEM and RELATED_HISTORY
	//use getRelatedDocument to get downloadable files
	
	public function getRelatedItem($catalogGuid, $relateAs)
	{	
		$this->catalogGuid = $catalogGuid;
		
		
		$a2 = array();
		$aNodesTraversed = array();
		$this->_traverseHistory($aNodesTraversed, $a2,$catalogGuid, $relateAs);
		
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		$aTmp2['node'] = $catalogGuid;
		$aTmp2['nodeLeft'] = 'tmpLeft';
		$aTmp2['nodeRight'] =  'tmpRight';
		$aTmp2['description'] = '';
		$aTmp2['relationType'] = '';
		
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['title'] = $rowCatalogAttribute->value;
		else
			$aTmp2['title'] = 'No-Title';
			
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedSubTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['subTitle'] = $rowCatalogAttribute->value;
		else
			$aTmp2['subTitle'] = 'No-Title';
		
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedDate'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
		else
			$aTmp2['fixedDate'] = '00-00-00';
		
		array_push($a2, $aTmp2);
		
		UtilHistorySort::sort($a2, 'fixedDate', false);
		
		return $a2;
		//print_r($a2);
		//die();
	}
	function _traverseHistory(&$aNodesTraversed, &$a2, $node, $relateAs='RELATED_ITEM')
	{
		array_push($aNodesTraversed, $node);
		$aTmp = $this->_getNodes($node, $relateAs);
		
		foreach ($aTmp as $node2)
		{
			if(!$this->_checkTraverse($aNodesTraversed, $node2['node']))
			{
				array_push($a2, $node2);
				$this->_traverseHistory($aNodesTraversed, $a2, $node2['node'], $relateAs);
			}
		}
		return true;
	}
	
	function _getNodes($node, $relateAs='RELATED_ITEM')
	{
		$a = array();
		
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		
		$where = "relatedGuid='$node' AND relateAs='$relateAs'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->itemGuid;
			$aTmp2['nodeLeft'] = $row->itemGuid;
			$aTmp2['nodeRight'] =  $node;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
			
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
				
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
			
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
			
			array_push($a, $aTmp2);	
		}
		
		$where = "itemGuid='$node' AND relateAs='$relateAs'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->relatedGuid;
			$aTmp2['nodeLeft'] = $node;
			$aTmp2['nodeRight'] =  $row->relatedGuid;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
			
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
				
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
			
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
			
			array_push($a, $aTmp2);	
		}
		
		return $a;
	}
	
	function _checkTraverse($a, $node)
	{
		foreach($a as $row)
		{
			if($row == $node)
			{
				return true;
			}
		}
		return false;
	}
	
	public function getDasarHukum($catalogGuid)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		return $rowsetRelatedItem;
	}
	public function getFiles($catalogGuid)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_FILE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		return $rowsetRelatedItem;
	}
	public function getSejarah($catalogGuid)
	{
		return $this->getRelatedItem($catalogGuid,'RELATED_HISTORY');
	}
	public function getOthers($catalogGuid)
	{
		return $this->getRelatedItem($catalogGuid,'RELATED_ITEM');
	}
	public function getPeraturanPelaksana($catalogGuid)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "itemGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		return $rowsetRelatedItem;
	}
	public function getTranslations($catalogGuid)
	{
		return $this->getRelatedItem($catalogGuid,'RELATED_TRANSLATION');
	}
	public function getFolders($catalogGuid)
	{
		$bpm = new Kutu_Core_Bpm_Catalog();
		return $bpm->getFolders($catalogGuid);
	}
	public function delete($itemGuid, $relatedGuid, $relateAs)
	{
		if(empty($relateAs))
			throw new Zend_Exception('relateAs can not be empty!');
			
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		if($tblRelatedItem->delete("itemGuid='$itemGuid' AND relatedGuid='$relatedGuid' AND relateAs='$relateAs'"))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	//DO NOT USE THIS FUNCTION YET. IT IS STILL UNSTABLE!!!
	public function addTranslation($itemGuid, $relatedGuid)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		//check apakah relatedGuid adalah seorang parent atau child
		$rowsetRelatedItem = $tblRelatedItem->fetchAll("relatedGuid='$relatedGuid' AND relateAs='RELATED_TRANSLATION'");
		if(count($rowsetRelatedItem))
		{
			//do nothing
		}
		$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$relatedGuid' AND relateAs='RELATED_TRANSLATION'");
		if(count($rowsetRelatedItem))
		{
			$relatedGuid = $rowsetRelatedItem->current()->relatedGuid;
		}
		
		//check if $itemGuid adalah parent atau child
		$rowsetRelatedItem = $tblRelatedItem->fetchAll("relatedGuid='$itemGuid' AND relateAs='RELATED_TRANSLATION'");
		if(count($rowsetRelatedItem))
		{
			//check apakah sudah ada
			$rs1 = $tblRelatedItem->find($this->itemGuid, $relatedGuid, "RELATED_TRANSLATION");
			if(count($rs1))
			{
				
			}
			else
			{
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowset = $tblCatalog->find($itemGuid);
				if(count($rowset))
				{
					$row = $rowset->current();
					$row->relateTo($relatedGuid, "RELATED_TRANSLATION");
				}
			}
			//get all children and set its current relatedGuid to the new relatedGuid
			foreach($rowsetRelatedItem as $row)
			{
				$row->relatedGuid = $relatedGuid;
				$row->save();
			}
		}
		else
		{
			//check apakah itemGuid adalah child
			$rs1 = $tblRelatedItem->fetchAll("itemGuid='$itemGuid' AND relateAs='RELATED_TRANSLATION'");
			if(count($rs1))
			{
				//update bapaknya dan anak dari bapaknya
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowset = $tblCatalog->find($rs1->current()->relatedGuid);
				if(count($rowset))
				{
					$row = $rowset->current();
					$row->relateTo($relatedGuid, "RELATED_TRANSLATION");
				}
				$bapak = $rs1->current()->relatedGuid;
				$rs2 = $tblRelatedItem->fetchAll("relatedGuid='$bapak' AND relateAs='RELATED_TRANSLATION'");
				if(count($rs2))
				{
					foreach($rs2 as $row)
					{
						$row->relatedGuid = $relatedGuid;
						$row->save();
					}
				}
			}
		}
		
		
		
		
		
		/*$rowsetRelatedItem = $tblRelatedItem->find($this->itemGuid, $relatedGuid, "RELATED_TRANSLATION");
		if(count($rowsetRelatedItem))
		{
			
		}
		else
		{
			$rowsetRelatedItem = $tblRelatedItem->find($relatedGuid, $this->itemGuid, "RELATED_TRANSLATION");
			if(count($rowsetRelatedItem))
			{
				
			}
			else
			{
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowset = $tblCatalog->find($itemGuid);
				if(count($rowset))
				{
					$row = $rowset->current();
					$row->relateTo($relatedGuid, "RELATED_TRANSLATION");
				}
			}
		}*/	
	}
	public function deleteTranslation($guid)
	{
		
	}
}
class UtilHistorySort 
{
    static private $sortfield = null;
    static private $sortorder = 1;
    static private function sort_callback(&$a, &$b) {
        if($a[self::$sortfield] == $b[self::$sortfield]) return 0;
        return ($a[self::$sortfield] < $b[self::$sortfield])? -self::$sortorder : self::$sortorder;
    }
    static function sort(&$v, $field, $asc=true) {
        self::$sortfield = $field;
        self::$sortorder = $asc? 1 : -1;
        usort($v, array('UtilHistorySort', 'sort_callback'));
    }
}
?>