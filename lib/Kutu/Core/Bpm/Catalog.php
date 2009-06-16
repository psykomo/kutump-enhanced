<?php
class Kutu_Core_Bpm_Catalog
{
	// Describe minimal $aData
	// 
	public function save($aData)
	{
		//do minimal pre-requisite of aData
		if(empty($aData['fixedTitle']))
			throw new Zend_Exception('Catalog Title can not be EMPTY!');
		if(empty($aData['profileGuid']))
			throw new Zend_Exception('Catalog Profile can not be EMPTY!');
			
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		$gman = new Kutu_Core_Guid();
		$catalogGuid = (isset($aData['guid']) && !empty($aData['guid']))? $aData['guid'] : $gman->generateGuid();
		$folderGuid = (isset($aData['folderGuid']) && !empty($aData['folderGuid']))? $aData['folderGuid'] : '';
		
		//if not empty, there are 2 possibilities
		$where = $tblCatalog->getAdapter()->quoteInto('guid=?', $catalogGuid);
		if($tblCatalog->fetchRow($where))
		{
			$rowCatalog = $tblCatalog->find($catalogGuid)->current();
			//echo "guid ditemukan:" .$rowCatalog->guid;
			
			$rowCatalog->shortTitle = (isset($aData['shortTitle']))?$aData['shortTitle']:$rowCatalog->shortTitle;
			//$rowCatalog->profileGuid = $request->getParam('profileGuid');
			$rowCatalog->publishedDate = (isset($aData['publishedDate']))?$aData['publishedDate']:$rowCatalog->publishedDate;
			$rowCatalog->expiredDate = (isset($aData['expiredDate']))?$aData['expiredDate']:$rowCatalog->expiredDate;
			//$rowCatalog->createdBy = $request->getParam('createdBy');
			//$rowCatalog->modifiedBy = ($aData['username'])?$aData['username']:'';
			//$rowCatalog->createdDate = $request->getParam('createdDate');
			//$rowCatalog->modifiedDate = $aData['modifiedDate'];
			//$rowCatalog->deletedDate = $request->getParam('deletedDate');
			$rowCatalog->status = (isset($aData['status']))?$aData['status']:$rowCatalog->status;
			$rowCatalog->price = (isset($aData['price']))?$aData['price']:$rowCatalog->price;
			
		}
		else 
		{
			$rowCatalog = $tblCatalog->fetchNew();
			//echo "guid tidak ditemukan";
			$rowCatalog->guid = $catalogGuid;
			$rowCatalog->shortTitle = (isset($aData['shortTitle']))?$aData['shortTitle']:'';
			$rowCatalog->profileGuid = $aData['profileGuid'];
			$rowCatalog->publishedDate = '0000-00-00 00:00:00';
			$rowCatalog->expiredDate = '0000-00-00 00:00:00';
			$rowCatalog->createdBy = (isset($aData['username']))?$aData['username']:'';
			$rowCatalog->modifiedBy = $rowCatalog->createdBy;
			$rowCatalog->createdDate = date("Y-m-d h:i:s");
			$rowCatalog->modifiedDate = $rowCatalog->createdDate;
			$rowCatalog->deletedDate = '0000-00-00 00:00:00';
			$rowCatalog->status = (isset($aData['status']))?$aData['status']:0;
			$rowCatalog->price = (isset($aData['price']))?$aData['price']:0;
		}
		try 
		{
			$catalogGuid = $rowCatalog->save();
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
		
		$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$profileGuid = $rowCatalog->profileGuid;
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
		$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		
		$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		foreach ($rowsetProfileAttribute as $rowProfileAttribute)
		{
			if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
			{
				$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);
				//echo "rowcatalogattribute:" . $rowCatalogAttribute->attributeGuid;
			}
			else 
			{
				$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $catalogGuid;
				$rowCatalogAttribute->attributeGuid = $rowProfileAttribute->attributeGuid;
				
			}
			
			$rowCatalogAttribute->value = (isset($aData[$rowProfileAttribute->attributeGuid]))?$aData[$rowProfileAttribute->attributeGuid]:'';
			
			$rowCatalogAttribute->save();
		}
		
		//save to table CatalogFolder only if folderGuid is not empty
		if (!empty($folderGuid)) 
		{
			
			$rowCatalog->copyToFolder($folderGuid);
			
		}
		
		//do indexing
		$indexingEngine = Kutu_Search::manager();
		$indexingEngine->indexCatalog($catalogGuid);
		
		//after indexing, update isIndex and indexedDate in table KutuCatalog
		
		
		return $catalogGuid;
		
	}
	public function uploadFile($aDataCatalog, $relatedGuid)
	{
		// must have row catalog with profile Kutu_Doc. See Kutu_Core_Orm_Table_Row_Catalog on how to create a row of catalog.
		if($aDataCatalog['profileGuid']!='kutu_doc')
			throw new Zend_Exception('Profile does not match profile for FILE');
		$aDataCatalog['fixedTitle'] = ($aDataCatalog['fixedTitle'])?$aDataCatalog['fixedTitle']:'No-Title';
		
		if(empty($relatedGuid))
			throw new Zend_Exception('No RELATED GUID specified!');
		
		//get uploaded file data
		$registry = Zend_Registry::getInstance(); 
    	$files = $registry->get('files');
    	///foreach ($files as $file){}
		
		if(isset($files['uploadedFile']))
		{
			$file = $files['uploadedFile'];
			
			if(isset($files['uploadedFile']['name']) && !empty($files['uploadedFile']['name']))
			{
				$aDataCatalog['docSystemName'] = str_replace(' ','_',$file['name']);
				$aDataCatalog['docOriginalName'] = $file['name'];
				$aDataCatalog['docSize'] = $file['size'];
				$aDataCatalog['docMimeType'] = $file['type'];
			}
		}
		else
		{
			throw new Zend_Exception('No File was uploaded.');
		}
		
		if(isset($files['uploadedFile']))
		{
			if(isset($files['uploadedFile']['name']) && !empty($files['uploadedFile']['name']))
			{
				$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$relatedGuid;
				if(is_dir($sDir))
		    	{
		    		//if enters here, you may save the files
		    		move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . str_replace(' ','_',$file['name']));
		    		//echo 'dir';
		    	}
		    	else 
		    	{
		    		if(mkdir($sDir))
		    		{
		    			//if enters here, let's continue saving the file.
		    			move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . str_replace(' ','_',$file['name']));
		    		}
		    		else 
		    		{
		    			//if enters here, then it means, you CAN'T create the folder, maybe because the safe mode is ON.
		    			//save the file in the upload/files folder 
		    			move_uploaded_file($file['tmp_name'], KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR . str_replace(' ','_',$file['name']));
		    		}
		    	}
			}
		}
		$gman = new Kutu_Core_Guid();
		$catalogGuid = $gman->generateGuid();
		$aDataCatalog['guid'] = $catalogGuid;
		
		$this->relateTo($catalogGuid, $relatedGuid, 'RELATED_FILE');
		
		$catalogGuid = $this->save($aDataCatalog);
		//print_r($aDataCatalog);
		//die();
		
		
		//reindex catalog or parentCatalog or flag parent catalog as "need to be indexed" by setting isIndexed=0
		$indexingEngine = Kutu_Search::manager();
		$indexingEngine->indexCatalog($relatedGuid);
		
		
		
	}
	public function relateTo($itemGuid, $relatedGuid, $as='RELATED_ITEM', $valRelation = 0)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		if(empty($itemGuid))
			throw new Zend_Exception('Can not relate to empty GUID');
		
		$rowsetRelatedItem = $tblRelatedItem->find($itemGuid, $relatedGuid, $as);
		if(count($rowsetRelatedItem) > 0)
		{
			$row = $rowsetRelatedItem->current();
			$row->valueIntRelation = $valRelation;
		}
		else 
		{
			$row = $tblRelatedItem->createNew();
			$row->itemGuid = $itemGuid;
			$row->relatedGuid = $relatedGuid;
			$row->relateAs = $as;
			$row->valueIntRelation = $valRelation;
		}
		$row->save();
	}
	public function delete($catalogGuid)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog;
		$rowset = $tblCatalog->find($catalogGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$profileGuid = $row->profileGuid;
			
			if($row->profileGuid == 'kutu_doc')
			{
				$rowRelatedItem = $tblRelatedItem->fetchRow("itemGuid='$row->guid' AND relateAs='RELATED_FILE'");
			}
			
			$row->delete();
			
			//if deleted catalog is kutu_doc then re-index its parentGuid
			if($profileGuid == 'kutu_doc')
			{
				$indexingEngine = Kutu_Search::manager();
				$indexingEngine->indexCatalog($rowRelatedItem->relatedGuid);
			}
		}
		
		
	}
	public function moveToFolder($catalogGuid, $sourceFolderGuid, $targetFolderGuid)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog;
		$rowset = $tblCatalog->find($catalogGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$row->moveToFolder($sourceFolderGuid, $targetFolderGuid);
		}
	}
	public function copyToFolder($catalogGuid, $targetFolderGuid)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog;
		$rowset = $tblCatalog->find($catalogGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$row->copyToFolder($targetFolderGuid);
		}
	}
	public function removeFromFolder($catalogGuid, $folderGuid)
	{
		$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
		$rowset = $tblCatalogFolder->fetchAll("catalogGuid='$catalogGuid'");
		if(count($rowset)>1)
		{
			try
			{
				$tblCatalogFolder->delete("catalogGuid='$catalogGuid' AND folderGuid='$folderGuid'");
			}
			catch (Exception $e)
			{
				throw new Zend_Exception($e->getMessage());
			}
		}
		else
		{
			throw new Zend_Exception("Can not remove from the only FOLDER.");
		}
	}
	public function shareWith($catalogGuid, $username, $groupname, $permissions)
	{
		
	}
	public function getPrice($catalogGuid)
	{
		// rules specific to LGS
		$bpm = new Kutu_Core_Bpm_Catalog();
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->find($catalogGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			//die($row->price);
			return $row->price;
			
			/*switch ($row->profileGuid)
			{
				case 'kutu_peraturan':
				case 'kutu_putusan':
				case 'kutu_peraturan_kolonial':
				case 'kutu_rancangan_peraturan':
					if($row->price<0)
						return 0;
					else
					{
						//check language
						$lang = Kutu_Core_Util::getCatalogAttributeValue($row->guid,'fixedLanguage');
						switch ($lang)
						{
							case 'in':
								return 10;
								break;
							case 'en':
								return 20;
								break;
							default:
								return $row->price;
								break;
						}
					}
					break;
				default:
					return $row->price;
			}*/
		}
		else
		{
			return 0;
		}
	}
	public function updateNumberOfDownloads($catalogGuid, $count=1)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		$row = $tblCatalog->find($catalogGuid)->current();
		$count = $row->numDownloads + $count;
		
		$tblCatalog->update(array("numDownloads"=>$count), "guid='$catalogGuid'");
		
		return true;
	}
	public function updateNumberOfViews($catalogGuid, $count=1)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		$row = $tblCatalog->find($catalogGuid)->current();
		$count = $row->numViews + $count;
		
		$tblCatalog->update(array("numViews"=>$count), "guid='$catalogGuid'");
		
		return true;
	}
	public function alterDate($aRowCatalog)
	{
		if(isset($aRowCatalog['guid']) && empty($aRowCatalog['guid']))
		{
			throw new Zend_Exception("Guid can not be empty!");
		}
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		$row = $tblCatalog->find($aRowCatalog['guid'])->current();
		
		$catalogGuid = $aRowCatalog['guid'];
		$createdDate = (isset($aRowCatalog['createdDate']))?$aRowCatalog['createdDate']:$row->createdDate;
		$modifiedDate = (isset($aRowCatalog['modifiedDate']))?$aRowCatalog['modifiedDate']:$row->modifiedDate;
		
		
		$tblCatalog->update(array('createdDate'=>$createdDate,'modifiedDate'=>$modifiedDate), "guid='$catalogGuid'");
		
		//reindex catalog
		$indexingEngine = Kutu_Search::manager();
		$indexingEngine->indexCatalog($catalogGuid);
		
		return true;
	}
	public function getFolders($catalogGuid)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Catalog');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		$rowsetFolder = $rowCatalog->findManyToManyRowset('Kutu_Core_Orm_Table_Folder', 'Kutu_Core_Orm_Table_CatalogFolder');
		
		return $rowsetFolder;
	}
}
?>