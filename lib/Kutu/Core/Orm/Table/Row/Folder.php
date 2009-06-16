<?php

class Kutu_Core_Orm_Table_Row_Folder extends Zend_Db_Table_Row_Abstract 
{
	protected function _insert()
	{
		//add your custom logic here
		//must set the new PATH here
		if(empty($this->guid))
		{
    		$guidMan = new Kutu_Core_Guid();
    		$this->guid = $guidMan->generateGuid();
		}
		if(empty($this->parentGuid))
		{
			throw new Zend_Exception('parentGuid can not be empty!');
		}
		if(empty($this->title))
		{
			throw new Zend_Exception('Title can not be empty!');
		}
		
		if($this->parentGuid == 'root')
		{
			$this->path = '';
			$this->parentGuid = $this->guid;
		}
		else 
		{
			$parentFolder = $this->_getTable()->find($this->parentGuid)->current();
			
			$this->path = $parentFolder->path.$parentFolder->guid.'/';
		}
		
	}
	
	protected function _update()
	{
		//echo $this->guid;
	}
	protected function _delete()
	{
		$rowsetCatalog = $this->findDependentRowsetCatalogFolder();
		$rowsetChildren = $this->fetchChildren();
		if(count($rowsetCatalog) || count($rowsetChildren))
		{
			throw new Exception('Deletion Failed! Folder may contain catalog or sub-folder.');
		}
		else 
		{
			//delete row in lucene index (if folder is indexed)
		}
		
	}
	protected function _updatePath()
	{
		$parentFolder = $this->_getTable()->find($this->parentGuid)->current();
		$this->path = $parentFolder->path.$targetFolder->guid.'/';
		
	}
	
	public function test()
	{
		echo $this->getTableClass();
	}
	
	public function findDependentRowsetCatalogFolder()
	{
		return $this->findDependentRowset('Kutu_Core_Orm_Table_CatalogFolder');
	}
	
	public function copyFolderContent($currentFolderGuid, $newCatalogGuid)
	{
		$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
		$rowset = $tblCatalogFolder->fetchAll("folderGuid = '$currentFolderGuid'");
		
		if (count($rowset) > 0)
		{
			foreach ($rowset as $row)
			{
				$newContent = $tblCatalogFolder->createRow();
				$newContent->folderGuid = $newCatalogGuid;
				$newContent->catalogGuid = $row->catalogGuid;
				$newContent->save();
			}
		}
		
	}
	
	public function copy($targetFolderGuid, $currentFolderGuid)
	{
		$currentFolder = $this->_getTable()->find($currentFolderGuid)->current();
		
		$this->title = $currentFolder->title;
		$this->description = $currentFolder->description;
		
		if($targetFolderGuid == 'root')
		{
			$this->parentGuid = $targetFolderGuid;
			$this->path = '';
			$cGuid = $this->save();
				
			$this->copyFolderContent($currentFolderGuid,$cGuid);
		
			$rowset = $this->_getTable()->fetchAll("path LIKE '%".$currentFolderGuid."/%'");
			foreach ($rowset as $row)
			{
				$rowChild = $this->_getTable()->createRow();
				$rowChild->title = $row->title;
				$rowChild->description = $row->description;
				$rowChild->parentGuid = $this->guid;
				$catalogGuid = $rowChild->save();
				
				$this->copyFolderContent($row->guid,$catalogGuid);
			}
		}
		else 
		{
			$targetFolder = $this->_getTable()->find($targetFolderGuid)->current();
			
			//check if targetFolderGuid is a child of this folder
			$rowset = $this->_getTable()->fetchAll("guid='$targetFolderGuid' AND path LIKE '%".$currentFolderGuid."/%'");
			
			if(count($rowset) > 0)
			{
				throw New Zend_Exception('Can\'t copy folder to children');
			}
			else 
			{
				$this->parentGuid = $targetFolderGuid;
				$this->path = $targetFolder->path.$targetFolder->guid.'/';
				$cGuid = $this->save();
				
				$this->copyFolderContent($currentFolderGuid,$cGuid);
				
				//update all children
				$rowset = $this->_getTable()->fetchAll("guid !='$targetFolderGuid' AND path LIKE '%".$currentFolderGuid."/%'");
				foreach ($rowset as $row)
				{
					$rowChild = $this->_getTable()->createRow();
					$rowChild->title = $row->title;
					$rowChild->description = $row->description;
					$rowChild->parentGuid = $this->guid;
					$catalogGuid = $rowChild->save();
					
					$this->copyFolderContent($row->guid,$catalogGuid);
				}
			}
		}
	}
	
	public function move($targetFolderGuid)
	{
		if($targetFolderGuid == 'root')
		{
			if($this->guid == $this->parentGuid)
			{
				
			}
			else 
			{
				$this->parentGuid = $this->guid;
				$originalPath = $this->path;
				$this->path = '';
				$this->save();
				
				$rowset = $this->_getTable()->fetchAll("path LIKE '%".$this->guid."/%'");
				foreach ($rowset as $row)
				{
					$row->path = str_replace($originalPath, $this->path, $row->path);
					$row->save();
				}
			}
		}
		else 
		{
			$targetFolder = $this->_getTable()->find($targetFolderGuid)->current();
			
			//check if targetFolderGuid is a child of this folder
			$rowset = $this->_getTable()->fetchAll("guid='$targetFolderGuid' AND path LIKE '%".$this->guid."/%'");
			
			$originalPath = $this->path;
			
			if(count($rowset) > 0)
			{
				throw New Zend_Exception('Can\'t move folder to children');
			}
			else 
			{
				$this->parentGuid = $targetFolderGuid;
				$this->path = $targetFolder->path.$targetFolder->guid.'/';
				$this->save();
				
				//update all children
				$rowset = $this->_getTable()->fetchAll("guid !='$targetFolderGuid' AND path LIKE '%".$this->guid."/%'");
				foreach ($rowset as $row)
				{
					if(empty($originalPath))
					{
						$row->path = $this->path.$row->path; //str_replace($this->guid.'/', $this->path, $row->path);
						//echo "folder: ".$row->title." ori:$originalPath, this: $this->path, now: $row->path"."<br>";
					}
					else
					{
						$row->path = str_replace($originalPath, $this->path, $row->path);
						//echo "folder: ".$row->title." ori:$originalPath, this: $this->path, now: $row->path"."<br>";
					}
					$row->save();
				}
			}
		}
		
	}
	
	public function fetchChildren()
	{
		return $this->_getTable()->fetchChildren($this->guid);
	}
	
	public function findRowsetCatalog($startFrom=0, $limit=0)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		return $tblCatalog->fetchFromFolder($this->guid, $startFrom, $limit);
	}
}