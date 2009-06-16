<?php
class Migration_LgsintraprimaController extends Kutu_Controller_Action
{
	private $_dbSource;
	private $_dbTarget;
	private $_guidPrefix;
	private $_guidPrefixDms;
	private $_guidPrefixFile;
	
	function preDispatch() 
    { 
		//NOTES: _guidPrefix and _guidPrefixCms should be the same. This is needed to support older code that still use _guidPrefix.
		$this->_guidPrefix = 'lgsimp';
		$this->_guidPrefixCms = 'lgsimp';
		$this->_guidPrefixDms = 'lgsimpdms';
		$this->_guidPrefixFile = 'lgsimpfl';
		
		$this->_dbSource = Zend_Db::factory("Pdo_Mysql", array(
		    'host'     => 'localhost',
		    'username' => 'root',
		    'password' => 'root',
		    'dbname'   => 'LgsonlineSip'
		)); 
		
		$this->_dbTarget = Zend_Db::factory("Pdo_Mysql", array(
		    'host'     => 'localhost',
		    'username' => 'root',
		    'password' => 'root',
		    'dbname'   => 'kutump-lgs'
		));
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/lib/LgsIntraPrima.php');
	}
	public function migrateLgsLegacyAction()
	{
		// migrate ALL columns with its articles
		
		//news and opportunities
		//$this->_migrateColumn(5, 'lgs4a1d77eb99e7a');
		
		//Indonesia Under Review
		$this->_migrateColumn(7, 'lgs4a1d7937a6f9b');
		
		//message from the board
		//$this->_migrateColumn(6, 'lgs4a1d79d321b76');
		
		// migrate ALL DMS Categories WITHOUT its catalogs
		
		// migrate USERS
		//$this->_migrateAllUsers();
		
		die('FINISHED!');
	}
	
	
	//This function is WORKING. 
	// But there is small minor problem with SOLR INDEXING.
	// SOLR can throw error: MARK INVALID. This may because of SOLR has bug when indexing an HTML string.
	// Bug is still not resolved. 
	// Temporary solution: I put try/catch block when indexing, and print out the GUIDs that have the problem with its HTML, then
	// using dms admin interface, manually edit and save those particular catalogs/guids.
	
	private function _migrateColumn($sourceColumnId, $targetFolderGuid)
	{
		// in Intra Prima a column can have sub column, but 
		// fortunately, all columns in LGS Online don't have sub-columns.
		// So, we don't need to process sub-columns.
		
		//$this->_guidPrefix = 'lgscl'
		
		$sqlSource = "SELECT * from tblArticle where Column_ID='$sourceColumnId'";
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$results = $this->_dbSource->fetchAll($sqlSource);
		
		if(count($results))
		{
			foreach($results as $row)
			{
				// do mapping here
				
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowCatalog = $tblCatalog->find($this->_guidPrefix.$row->Article_ID);

				if (count($rowCatalog) <= 0) 
				{
					$rowCatalog = $tblCatalog->fetchNew();
					$rowCatalog->guid = $this->_guidPrefix.$row->Article_ID;

					$rowCatalog->shortTitle = '';

					$rowCatalog->profileGuid = "article";
					$rowCatalog->createdBy = "admin";
					$rowCatalog->modifiedBy = 'admin';
					$rowCatalog->createdDate = $row->Date_Create;
					$rowCatalog->modifiedDate = $row->LastUpdate;
					$rowCatalog->publishedDate = $row->Date_Publish;
					$rowCatalog->expiredDate = $row->Date_Expired;
					
					if($row->IsPublished===1)
						$rowCatalog->status = 99;
					else
						$rowCatalog->status = $row->IsPublished;
						
					$rowCatalog->save();
				
					
					$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();

					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedTitle';
					$rowCatalogAttribute->value = $this->_cleanChars($row->Title);
					$rowCatalogAttribute->save();

					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedSubTitle';
					$rowCatalogAttribute->value = $this->_cleanChars($row->Sub_Title);
					$rowCatalogAttribute->save();
					
					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedDescription';
					$rowCatalogAttribute->value = $this->_cleanChars($row->Description);
					$rowCatalogAttribute->save();
					
					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedContent';
					$rowCatalogAttribute->value = $this->_cleanChars($row->Content);
					$rowCatalogAttribute->save();
					
					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedAuthor';
					$rowCatalogAttribute->value = $this->_cleanChars($row->Source);
					$rowCatalogAttribute->save();
					
					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedKeywords';
					$rowCatalogAttribute->value = '';
					$rowCatalogAttribute->save();
					
					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $this->_guidPrefix.$row->Article_ID;
					$rowCatalogAttribute->attributeGuid = 'fixedLanguage';
					$rowCatalogAttribute->value = 'en';
					$rowCatalogAttribute->save();
					
					//update KutuCatalogFolder
					$rowCatalog = $tblCatalog->find($this->_guidPrefix.$row->Article_ID)->current();
					$rowCatalog->copyToFolder($targetFolderGuid);
					
					//do indexing
					$indexingEngine = Kutu_Search::manager();
					
					try
					{
						$indexingEngine->indexCatalog($this->_guidPrefix.$row->Article_ID);
					}
					catch (Exception $e)
					{
						$sE = 'guid: '.$this->_guidPrefix.$row->Article_ID.'<br>';
						echo $sE.$e->getMessage().'<br>&nbsp;<br>';
						
						$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
						$rowCatalogAttribute = $tblCatalogAttribute->fetchRow("catalogGuid='$rowCatalog->guid' AND attributeGuid='fixedContent'");
						
						include(KUTU_ROOT_DIR.'/lib/htmLawed/htmLawed.php');

						$config = array('clean_ms_char'=>2); 
						//$sTmp = htmLawed($sTmp);
						$rowCatalogAttribute->value = $this->_cleanMsWordHtml($rowCatalogAttribute->value);
						$rowCatalogAttribute->save();
						//echo $rowCatalogAttribute->value;
						
						try
						{
							$indexingEngine->indexCatalog($rowCatalog->guid);
						}
						catch (Exception $e)
						{
							echo "TETEP AJA ERROR LAGI!";
						}
					}
					
					//die('1 row processed: '.$this->_guidPrefix.$row->Article_ID);
				}
				
				// process Related Article
				$this->_migrateColumnRelatedItems($row->Article_ID);
			}
		}
		
		
	}
	private function _migrateColumnRelatedItems($sourceCatalogId)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		
		//get data from tblDms_RelatedDoc and put it as RELATED_ITEM
		
		$rowsetSourceRelatedItem = $this->_dbSource->fetchAll("SELECT * from tblArticle_RelatedArticle C  
				WHERE C.Article_ID = $sourceCatalogId OR C.RelatedArticle_ID = $sourceCatalogId "
				);
		foreach($rowsetSourceRelatedItem as $rowSourceRelatedItem)
		{
			$catalogGuid = $this->_guidPrefixCms.$rowSourceRelatedItem->Article_ID;
			$relatedGuid = $this->_guidPrefixCms.$rowSourceRelatedItem->RelatedArticle_ID;
			
			$rowsetRelatedItem = $tblRelatedItem->find($catalogGuid, $relatedGuid, "RELATED_ITEM");
			if(count($rowsetRelatedItem) > 0)
			{
				//do nothing
			}
			else 
			{
				$row = $tblRelatedItem->createNew();
				$row->itemGuid = $catalogGuid;
				$row->relatedGuid = $relatedGuid;
				$row->relateAs = "RELATED_ITEM";
				$row->save();
			}
		}
	}
	public function migrateUsersAction()
	{
		$this->_migrateAllUsers();
	}
	
	
	//this function is WORKING.
	// please check the source table before migration. Because, in source user table, username is not UNIQUE... KOK BISA YA????
	private function _migrateAllUsers()
	{
		$sqlSource = "SELECT * from tblPersonalization_User";
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$results = $this->_dbSource->fetchAll($sqlSource);
		
		// do mapping
		
		if(count($results))
		{
			foreach($results as $row)
			{
				$tblUser = new Kutu_Core_Orm_Table_User();
				$rowsetUser = $tblUser->find($this->_guidPrefix.$row->UserID);
				if(count($rowsetUser) < 1)
				{
					// do something
					$rowUser = $tblUser->fetchNew();
					$rowUser->guid = $this->_guidPrefix.$row->UserID;
					$rowUser->username = $row->UserName;
					$rowUser->password = (is_null($row->Password))?'V3FXfFJzBnpVYg==':$row->Password;
					$rowUser->firstname = $row->FullName;
					$rowUser->lastname = '';
					$rowUser->email = $row->Email;
					$rowUser->company = $row->CompanyName;
					$rowUser->mainAddress = $row->Address;
					$rowUser->zip = $row->ZIP;
					$rowUser->phone = $row->Phone;
					$rowUser->industryId = $row->CompBusLine;
					$rowUser->isActive = (is_null($row->isActive))?0:$row->isActive;
					$rowUser->createdDate = $row->DateCreate;
					$rowUser->registrationDate = $rowUser->createdDate;
					
					$sqlMembership = "SELECT * from tblMembership_Exp where User_ID=$row->UserID";
					$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
					$resultsMembership = $this->_dbSource->fetchAll($sqlMembership);
					//print_r($resultsMembership);
					//die();
					if(count($resultsMembership))
					{
						$rowMembership = $resultsMembership[0];
						$rowUser->fax = $rowMembership->Fax;
						$rowUser->activationDate = $rowMembership->Activation_Date;
						$rowUser->expirationDate = $rowMembership->Expiration_Date;
						
					}
					//if($row->UserID==157)
					if(true)
					{
						$rowUser->save();
						//die();
					}
					//Must also assign assign user as group:member_free
					$acl = Kutu_Acl_Manager::getAdapter();
					$acl->addUserToGroup($rowUser->username,"member_free");
					
				}
				else
				{
					
				}
			}
		}
		
		die();
	}
	
	private function _cleanChars($sMustBeCleaned)
	{
		$c1 = '”';
		$c2 = '“';
		$c3 = "FONT-SIZE: 10pt; FONT-FAMILY: Verdana";
		$c4 = '<div style="background-color:"><font face="verdana,arial" size="2">
		<div>';
		$c5 = "</div>
		</font></div>";
		$c6 = "FONT-SIZE: 10pt; FONT-FAMILY: Arial";
		$c7 = "<html>";
		$c8 = "</html>";
		
		$words = array($c1, $c2, $c3, $c6, $c7, $c8);
		$sTmp = str_replace($words, "", $sMustBeCleaned);
		$sTmp = str_replace("’", "'", $sTmp);
		
		//include(KUTU_ROOT_DIR.'/lib/htmLawed/htmLawed.php');
		
		//$config = array('clean_ms_char'=>2); 
		//$sTmp = htmLawed($sTmp);
		
		
		$sTmp = $this->_cleanMsWordHtml($sTmp);
		
		return $sTmp;
		
	}
	private function _cleanMsWordHtml($textHtml)
	{
		$sTmp = $textHtml;
		/// <summary>
		/// Removes all FONT and SPAN tags, and all Class and Style attributes.
		/// Designed to get rid of non-standard Microsoft Word HTML tags.
		/// </summary>
		// start by completely removing all unwanted tags 

		$sTmp = ereg_replace("<(/)?(font|span|del|ins)[^>]*>","",$sTmp); 

		// then run another pass over the html (twice), removing unwanted attributes 

		$sTmp = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$sTmp); 
		$sTmp = ereg_replace("<([^>]*)(class|lang|style|size|face)=(\"[^\"]*\"|'[^']*'|[^>]+)([^>]*)>","<\\1>",$sTmp);
		
		return $sTmp;
	}
	public function checkShaAction()
	{
		$password = "nindyoOK";
		echo sha1($password);
		die();
	}
	
	public function viewFoldersAction()
	{
		//echo '<select>'.$this->_traverseFolder(535,'',0).'</select>';
		//echo $this->_traverseFolder(535,'',0);
		echo $this->_traverseFolder(517,'',0);
		die();
	}
	protected function _traverseFolder($folderGuid, $sGuid, $level)
	{
		
		
		$sqlSource = "SELECT * from tblDms_CategoryTree, tblDms_Category where CatParentID=$folderGuid 
						AND tblDms_CategoryTree.CategoryID=tblDms_Category.CategoryID";
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$rowset = $this->_dbSource->fetchAll($sqlSource);
		
		$sGuid = '';
		
		foreach($rowset as $row)
		{
			$sTab = '';
			for($i=0;$i<$level;$i++)
				$sTab .= '--';
			
			//calculate number of catalogs in that category
			
			$numberCatalogs = $this->_dbSource->fetchOne('SELECT count(*) count FROM tblDms_CategoryRelationship WHERE CategoryID = '.$row->CategoryID);
				
			//$option = '<option value="'.$row->CategoryID.'">'.$sTab.$row->CatTitle.'</option>';
			$option = '"'.$row->CategoryID.'" :'.'"'.$sTab.$row->CatTitle.'",';
			$sGuid .= $option." ($numberCatalogs)<br>" . $this->_traverseFolder($row->CategoryID, '', $level+1);
			
			//$sGuid .= $sTab.$row->title . '|<br>'. $this->_traverseFolder($row->guid, '', $level+1);
			
		}
		return $sGuid;
	}
	
	
	public function migrateLegalDatabaseAction()
	{
		// migrate all categories and catalogs inside a Source Parent Category to a targetParent Category
		$sourceParentCategoryId = 'xxx';
		$targetParentCategoryGuid = 'yyy';
		
		//echo $this->_migrateChildrenCategories(517,'',0);
		
		//$this->_moveCategories(517,'sys_tmp');
		
		//LGS LEGAL MEMORANDA
		//$this->_moveCategories(518,'sys_tmp');
		
		//LGS LITIGATION REPORT
		//$this->_moveCategories(619,'sys_tmp');
		
		//Ketenagalistrikan
		$this->_moveCategories(52,'sys_tmp');
		
		//convert all attributes from intraprima to comply with Kutu
		/*$oConvert = new Migration_LgsIntraPrima();
		$oConvert->putusan_ptsAmar();
		$oConvert->putusan_ptsHakim();
		$oConvert->putusan_ptsJenisLembaga();
		$oConvert->putusan_ptsTkProses();
		$oConvert->putusan_ptsYuris();
		$oConvert->peraturan_prtJenis();
		$oConvert->peraturan_prtPengumuman();
		$oConvert->peraturan_prtJenisPengumuman();
		$oConvert->peraturan_prtRancangan();*/
		
		
		die();
		
	}
	private function _moveCategories($sourceParent, $targetFolderGuid)
	{
		$this->_moveCategory($sourceParent, $targetFolderGuid);
		
		$this->_moveChildren($sourceParent, '', 0);
	}
	protected function _moveChildren($folderGuid, $sGuid, $level)
	{
		$sqlSource = "SELECT * from tblDms_CategoryTree, tblDms_Category where CatParentID=$folderGuid 
						AND tblDms_CategoryTree.CategoryID=tblDms_Category.CategoryID";
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$rowset = $this->_dbSource->fetchAll($sqlSource);
		
		foreach($rowset as $row)
		{
			$this->_moveCategory($row->CategoryID, $this->_guidPrefix.$folderGuid);	
			$this->_moveChildren($row->CategoryID, '', $level+1);
		}
	}
	private function _moveCategory($sourceCategoryId, $targetFolderGuid)
	{
		$sqlSource = "SELECT * from tblDms_CategoryTree, tblDms_Category where tblDms_Category.CategoryID=$sourceCategoryId 
						AND tblDms_CategoryTree.CategoryID=tblDms_Category.CategoryID";
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$row = $this->_dbSource->fetchRow($sqlSource);
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowsetFolder = $tblFolder->find($this->_guidPrefix.$row->CategoryID);

		if (count($rowsetFolder) <= 0) 
		{
			//insert into KutuFolder
			$newFolder = $tblFolder->fetchNew();
			$newFolder->guid = $this->_guidPrefix.$row->CategoryID;
			$newFolder->title = $row->CatTitle;
			$newFolder->description = $row->CatDescription;
			$newFolder->parentGuid = $targetFolderGuid;
			
			$rowsetFolder = $tblFolder->find($targetFolderGuid);

			if(count($rowsetFolder))
			{
				$rowTarget = $rowsetFolder->current();
				$newFolder->path = $rowTarget->path.$rowTarget->guid.'/';
				
			}
			else
			{
				throw new Zend_Exception('NO RECORD FOR TARGET FOLDER');
			}
			
			$newFolder->save();
			
			// move and migrate its catalogs
			//$this->_migrateCatalogsInCategory($sourceCategoryId);
				
		}
		else
		{
			$newFolder = $rowsetFolder->current();
		}
		
		// move and migrate its catalogs
		echo '<br>FOLDER: '.$row->CatTitle.'</br>';
		$this->_migrateCatalogsInCategory($sourceCategoryId);
		
		$sGuid = '';
		
		// do index migrated catalogs in TARGET category
		$indexingEngine = Kutu_Search::manager();
		try
		{
			$indexingEngine->reIndexCatalog('NORMAL', $newFolder->guid);
		}
		catch (Exception $e)
		{
			$sE = 'guid: '.$newFolder->guid.'<br>';
			echo $sE.$e->getMessage().'<br>&nbsp;<br>';
		}
		
		//print_r($row);
	}
	
	//run this function from _moveCategory.
	//this function REQUIRES that source category has been migrated/moved to the target.
	private function _migrateCatalogsInCategory($sourceCategoryId)
	{
		$targetFolderGuid = $this->_guidPrefix.$sourceCategoryId;
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowsetFolder = $tblFolder->find($targetFolderGuid);
		if(!count($rowsetFolder))
			throw new Zend_Exception('Target Folder: '.$targetFolderGuid.' DOES NOT EXIST!!!');
		
		//get catalogs from source category
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$catalogIds = $this->_dbSource->fetchAll("SELECT DocumentID from tblDms_CategoryRelationship C  
				WHERE C.CategoryID = $sourceCategoryId"
				);
		echo 'CATALOG: ';
		print_r($catalogIds);
		
		foreach($catalogIds as $sourceCatalog)
		{
			$catalogId = $sourceCatalog->DocumentID;
			
			//check if catalog has row in tblDms_DocumentProfile
			$rowsetAttribute = $this->_dbSource->fetchAll("SELECT * FROM tblDMS_Document A, tblDMS_DocumentProfile B  
					WHERE A.DocumentID=$catalogId AND A.DocumentID=B.DocumentID");
					
			if(count($rowsetAttribute))
			{
				$rowCatalog = $rowsetAttribute[0];
				switch ($rowCatalog->ProfileID)
				{
					case 1:
						$this->_createPeraturan($rowsetAttribute, $targetFolderGuid);
						//echo "Not processed yet. Profile not ready. Guid: ".$rowCatalog->DocumentID;
						//echo " KUPRET ";
						break;
					case 14:
						$this->_createDmsMemoranda($rowsetAttribute, $targetFolderGuid);
						break;
					default:
						//$this->_createArticle($rowsetAttribute);
						echo "<br>Not processed yet. Profile not ready. <br>Guid: ".$rowCatalog->DocumentID .' PROFILE: '. $rowCatalog->ProfileID.'<br>';
						break;
				}
				
				
			}
			else
			{
				//this means that this catalog doesn't have profile. Thus, we will treat this catalog as a catalog with an article profile
				$rowCatalog = $this->_dbSource->fetchRow("SELECT * FROM tblDMS_Document A WHERE A.DocumentID=$catalogId");
				$this->_createDmsArticle($rowCatalog, $targetFolderGuid);
				//echo " KUPRET ";
			}
			
		}
	}
	
	
	private function _createPeraturan($rowsetAttribute, $targetFolderGuid, $guidPrefix = 'lgsimpdms')
	{
		$rowSourceCatalog = $rowsetAttribute[0];
		
		$guidPrefix = $this->_guidPrefixDms;
		
		// map to KutuCatalog

		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($this->_guidPrefixDms.$rowSourceCatalog->DocumentID);

		if (count($rowCatalog) <= 0) 
		{
			$rowCatalog = $tblCatalog->fetchNew();
			$rowCatalog->guid = $guidPrefix.$rowSourceCatalog->DocumentID;

			$rowCatalog->shortTitle = $rowSourceCatalog->DocTitle;

			$rowCatalog->profileGuid = "kutu_peraturan";
			$rowCatalog->createdBy = $rowSourceCatalog->DocCreator;
			$rowCatalog->modifiedBy = $rowSourceCatalog->DocModifier;
			$rowCatalog->createdDate = $rowSourceCatalog->DocCreated;
			$rowCatalog->modifiedDate = $rowSourceCatalog->DocModified;
			
			$rowCatalog->status = 99;
			$rowCatalog->price = $rowSourceCatalog->Price;
			
			$rowCatalog->save();
		
			$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedTitle';
			$rowCatalogAttribute->value = $this->_cleanChars($rowSourceCatalog->DocTitle);
			$rowCatalogAttribute->save();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedSubTitle';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocSubject;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedKeywords';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocKeywords;;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedComments';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocComments;;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedLanguage';
			$rowCatalogAttribute->value = 'in';
			$rowCatalogAttribute->save();
			
			$oConvert = new Migration_LgsIntraPrima();
			
			foreach($rowsetAttribute as $rowDocProfile)
			{
				switch ($rowDocProfile->AttributeID)
				{
					case 1:
						$attribute = "prtJenis";
						$attributeValue = $oConvert->convertPeraturanPrtJenis($rowDocProfile->Description);
						$flagSave = true;
						break;
					case 2:
						$attribute = "fixedNumber";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 3:
						$attribute = "fixedYear";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 4:
						$attribute = "fixedDate";
						$attributeValue = $rowDocProfile->DateValue;
						$flagSave = true;
						break;
					case 5:
						$attribute = "prtBerlaku";
						$attributeValue = $rowDocProfile->DateValue;
						$flagSave = true;
						break;
					case 6:
						$attribute = "prtPengumuman";
						$attributeValue = $oConvert->convertPeraturanPrtPengumuman($rowDocProfile->Description);
						$flagSave = true;
						break;
					case 7:
						$attribute = "prtJenisPengumuman";
						$attributeValue = $oConvert->convertPeraturanPrtJenisPengumuman($rowDocProfile->Description);
						$flagSave = true;
						break;
					case 9:
						$attribute = "prtTahunPengumuman";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 10:
						$attribute = "prtNomorPengumuman";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 11:
						$attribute = "prtNomorTambahanPengumuman";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 12:
						$attribute = "prtAnotasi";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 13:
						$attribute = "prtNomor01";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 14:
						$attribute = "prtNomor02";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
					case 75:
						$attribute = "prtNomor03";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
				}

				if($flagSave)
				{

					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocProfile->DocumentID;
					$rowCatalogAttribute->attributeGuid = $attribute;
					$rowCatalogAttribute->value = $attributeValue;
					$result = $rowCatalogAttribute->save();
				}
			}
		}
		else
		{
			$rowCatalog = $rowCatalog->current();
		}
		
		//update KutuCatalogFolder
		$rowCatalog->copyToFolder($targetFolderGuid);
		
		// migrate attached files
		$this->_createDocs($rowSourceCatalog->DocumentID);
		
		// migrate related items
		$this->_migrateDmsRelatedItems($rowSourceCatalog->DocumentID);
		
	}
	
	//this function will migrate records/catalog that exist in tblDms_Document, but don't have records 
	// in tblDms_DocumentProfile.
	//ALL _createDmsXXXX function MUST BE run from function _migrateCatalogInCategory!!!
	private function _createDmsArticle($rowSourceCatalog, $targetFolderGuid, $guidPrefix = 'lgsimpdms')
	{
		// do mapping here
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($guidPrefix.$rowSourceCatalog->DocumentID);

		if (count($rowCatalog) <= 0) 
		{
			$rowCatalog = $tblCatalog->fetchNew();
			$rowCatalog->guid = $guidPrefix.$rowSourceCatalog->DocumentID;

			$rowCatalog->shortTitle = $rowSourceCatalog->DocTitle;
			$rowCatalog->profileGuid = "article";
			$rowCatalog->createdBy = $rowSourceCatalog->DocCreator;
			$rowCatalog->modifiedBy = $rowSourceCatalog->DocModifier;
			$rowCatalog->createdDate = $rowSourceCatalog->DocCreated;
			$rowCatalog->modifiedDate = $rowSourceCatalog->DocModified;
			
			$rowCatalog->status = 99;
			$rowCatalog->price = $rowSourceCatalog->Price;
				
			$rowCatalog->save();
		
			
			$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedTitle';
			$rowCatalogAttribute->value = $this->_cleanChars($rowSourceCatalog->DocTitle);
			$rowCatalogAttribute->save();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedSubTitle';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocSubject;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedDescription';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedContent';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedAuthor';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedKeywords';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocKeywords;;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedLanguage';
			$rowCatalogAttribute->value = 'en';
			$rowCatalogAttribute->save();
			
			//die('1 row processed: '.$this->_guidPrefix.$row->Article_ID);	
			
		}
		else
		{
			$rowCatalog = $rowCatalog->current();
		}
		
		//update KutuCatalogFolder
		$rowCatalog->copyToFolder($targetFolderGuid);
		
		// migrate related items
		$this->_migrateDmsRelatedItems($rowSourceCatalog->DocumentID);
		
		// migrate attached files
		$this->_createDocs($rowSourceCatalog->DocumentID);
		
		
	}
	private function _createDmsMemoranda($rowsetAttribute, $targetFolderGuid)
	{
		$rowSourceCatalog = $rowsetAttribute[0];
		
		$guidPrefix = $this->_guidPrefixDms;
		
		// map to KutuCatalog

		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($guidPrefix.$rowSourceCatalog->DocumentID);

		if (count($rowCatalog) <= 0) 
		{
			$rowCatalog = $tblCatalog->fetchNew();
			$rowCatalog->guid = $guidPrefix.$rowSourceCatalog->DocumentID;

			$rowCatalog->shortTitle = $rowSourceCatalog->DocTitle;

			$rowCatalog->profileGuid = "legal_memoranda";
			$rowCatalog->createdBy = $rowSourceCatalog->DocCreator;
			$rowCatalog->modifiedBy = $rowSourceCatalog->DocModifier;
			$rowCatalog->createdDate = $rowSourceCatalog->DocCreated;
			$rowCatalog->modifiedDate = $rowSourceCatalog->DocModified;
			
			$rowCatalog->status = 99;
			$rowCatalog->price = $rowSourceCatalog->Price;
			
			$rowCatalog->save();
		
			$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedTitle';
			$rowCatalogAttribute->value = $this->_cleanChars($rowSourceCatalog->DocTitle);
			$rowCatalogAttribute->save();

			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedSubTitle';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocSubject;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedKeywords';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocKeywords;;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedDescription';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedComments';
			$rowCatalogAttribute->value = $rowSourceCatalog->DocComments;;
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedContent';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
			$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowSourceCatalog->DocumentID;
			$rowCatalogAttribute->attributeGuid = 'fixedAuthor';
			$rowCatalogAttribute->value = '';
			$rowCatalogAttribute->save();
			
			foreach($rowsetAttribute as $rowDocProfile)
			{
				$flagSave = false;
				switch ($rowDocProfile->AttributeID)
				{
					case 3:
						$attribute = "fixedYear";
						$attributeValue = $rowDocProfile->Description;
						$flagSave = true;
						break;
				}

				if($flagSave)
				{

					$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
					$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocProfile->DocumentID;
					$rowCatalogAttribute->attributeGuid = $attribute;
					$rowCatalogAttribute->value = $attributeValue;
					$result = $rowCatalogAttribute->save();
				}
			}
		}
		else
		{
			$rowCatalog = $rowCatalog->current();
		}
		
		//update KutuCatalogFolder
		$rowCatalog->copyToFolder($targetFolderGuid);
		
		// migrate attached files
		$this->_createDocs($rowSourceCatalog->DocumentID);
	
		// migrate related items
		$this->_migrateDmsRelatedItems($rowSourceCatalog->DocumentID);
		
		//do indexing
		$indexingEngine = Kutu_Search::manager();
		
		try
		{
			$indexingEngine->indexCatalog($this->_guidPrefixDms.$rowSourceCatalog->DocumentID);
		}
		catch (Exception $e)
		{
			$sE = 'guid: '.$this->_guidPrefixDms.$rowSourceCatalog->DocumentID.'<br>';
			echo $sE.$e->getMessage().'<br>&nbsp;<br>';
		}
		
	}
	private function _migrateDmsRelatedItems($sourceCatalogId)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		
		//get data from tblDms_RelatedDoc and put it as RELATED_ITEM
		
		$rowsetSourceRelatedItem = $this->_dbSource->fetchAll("SELECT * from tblDms_RelatedDocument C  
				WHERE C.DocumentID = $sourceCatalogId OR C.RelatedDocID = $sourceCatalogId "
				);
		foreach($rowsetSourceRelatedItem as $rowSourceRelatedItem)
		{
			$catalogGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->DocumentID;
			$relatedGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->RelatedDocID;
			
			$rowsetRelatedItem = $tblRelatedItem->find($catalogGuid, $relatedGuid, "RELATED_ITEM");
			if(count($rowsetRelatedItem) > 0)
			{
				$row = $rowsetRelatedItem->current();
				$row->statusRelation = $rowSourceRelatedItem->status;
				$row->save();
			}
			else 
			{
				$row = $tblRelatedItem->createNew();
				$row->itemGuid = $catalogGuid;
				$row->relatedGuid = $relatedGuid;
				$row->relateAs = "RELATED_ITEM";
				$row->statusRelation = $rowSourceRelatedItem->status;
				$row->save();
			}
		}
		
		
		//get data from tbldms_exp_peraturan_dsrhukum and put it RELATED_BASE
		$rowsetSourceRelatedItem = $this->_dbSource->fetchAll("SELECT * from tblDms_Exp_Peraturan_DsrHukum C  
				WHERE C.PeraturanID = $sourceCatalogId OR C.DasarHukumID = $sourceCatalogId "
				);
		foreach($rowsetSourceRelatedItem as $rowSourceRelatedItem)
		{
			$catalogGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->PeraturanID;
			$relatedGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->DasarHukumID;
			
			$rowsetRelatedItem = $tblRelatedItem->find($relatedGuid, $catalogGuid, "RELATED_BASE");
			if(count($rowsetRelatedItem) > 0)
			{
				$row = $rowsetRelatedItem->current();
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->save();
			}
			else 
			{
				$row = $tblRelatedItem->createNew();
				$row->itemGuid = $catalogGuid;
				$row->relatedGuid = $relatedGuid;
				$row->relateAs = "RELATED_BASE";
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->save();
			}
		}
		
		//get data from tbldms_exp_peraturan_sejarah and put it as RELATED_HISTORY
		$rowsetSourceRelatedItem = $this->_dbSource->fetchAll("SELECT * from tblDms_Exp_Peraturan_Sejarah C  
				WHERE C.PeraturanID = $sourceCatalogId OR C.SejarahID = $sourceCatalogId "
				);
		foreach($rowsetSourceRelatedItem as $rowSourceRelatedItem)
		{
			$catalogGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->PeraturanID;
			$relatedGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->SejarahID;
			
			$rowsetRelatedItem = $tblRelatedItem->find($catalogGuid, $relatedGuid, "RELATED_HISTORY");
			if(count($rowsetRelatedItem) > 0)
			{
				$row = $rowsetRelatedItem->current();
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->valueIntRelation = $rowSourceRelatedItem->IsRoot;
				$row->save();
			}
			else 
			{
				$row = $tblRelatedItem->createNew();
				$row->itemGuid = $catalogGuid;
				$row->relatedGuid = $relatedGuid;
				$row->relateAs = "RELATED_HISTORY";
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->valueIntRelation = $rowSourceRelatedItem->IsRoot;
				$row->save();
			}
		}
		
		//get data form tbldms_exp_putusan_sejarah and insert as RELATED_HISTORY
		$rowsetSourceRelatedItem = $this->_dbSource->fetchAll("SELECT * from tblDms_Exp_Putusan_Sejarah C  
				WHERE C.PutusanID = $sourceCatalogId OR C.SejarahID = $sourceCatalogId "
				);
		foreach($rowsetSourceRelatedItem as $rowSourceRelatedItem)
		{
			$catalogGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->PutusanID;
			$relatedGuid = $this->_guidPrefixDms.$rowSourceRelatedItem->SejarahID;
			
			$rowsetRelatedItem = $tblRelatedItem->find($catalogGuid, $relatedGuid, "RELATED_HISTORY");
			if(count($rowsetRelatedItem) > 0)
			{
				$row = $rowsetRelatedItem->current();
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->save();
			}
			else 
			{
				$row = $tblRelatedItem->createNew();
				$row->itemGuid = $catalogGuid;
				$row->relatedGuid = $relatedGuid;
				$row->relateAs = "RELATED_HISTORY";
				$row->statusRelation = $rowSourceRelatedItem->StatusID;
				$row->description = $rowSourceRelatedItem->Notes;
				$row->save();
			}
		}
	}
	private function _createDocs($sourceCatalogId, $guidPrefix = 'lgsimpfl')
	{
		// get all related documents
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$this->_dbSource->setFetchMode(Zend_Db::FETCH_OBJ);
		$rowsetDocItem = $this->_dbSource->fetchAll("SELECT * from tblDms_DocItem A  
				WHERE A.CatalogID = $sourceCatalogId"
				);
				
		foreach($rowsetDocItem as $rowDocItem)
		{
			$rowsetCatalog = $tblCatalog->find($guidPrefix.$rowDocItem->DocItemID);
			
			if (count($rowsetCatalog) == 0) 
			{
				$rowCatalog = $tblCatalog->fetchNew();
				$rowCatalog->guid = $guidPrefix.$rowDocItem->DocItemID;
				echo "KITI: ".$rowCatalog->guid;

				$sFileAsli = str_replace(array('_','.'),'-',$rowDocItem->DocFileAsli);
				$rowCatalog->shortTitle = $sFileAsli;

				$rowCatalog->profileGuid = "kutu_doc";
				$rowCatalog->createdBy = $rowDocItem->DocCreator;
				$rowCatalog->modifiedBy = $rowDocItem->DocModifier;
				$rowCatalog->createdDate = $rowDocItem->DocCreated;
				$rowCatalog->modifiedDate = $rowDocItem->DocModified;
				$rowCatalog->save();

				
				$rowExp = $this->_dbSource->fetchRow("Select * from tblDms_Exp_Seq where DocItemID=$rowDocItem->DocItemID AND DocumentID=$rowDocItem->CatalogID");

			//	untuk catalogAttributenya tambahan agar isinya fixedTitle,dsb itu
				// fixedTitle

				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'fixedTitle';
				if(empty($rowExp->Friendlyname))
					$rowCatalogAttribute->value = $rowDocItem->DocFileAsli;
				else
					$rowCatalogAttribute->value = $rowExp->Friendlyname;
				$rowCatalogAttribute->save();
				// fixedKeywords

				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'fixedKeywords';
				$rowCatalogAttribute->value = "";
				$rowCatalogAttribute->save();
				// fixedDescription
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'fixedDescription';
				$rowCatalogAttribute->value = "";
				$rowCatalogAttribute->save();
				// fixedLanguage
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'fixedLanguage';
				$rowCatalogAttribute->value = "en";
				$rowCatalogAttribute->save();
				// docCategoryGuid
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'docCategoryGuid';
				$rowCatalogAttribute->value = "";
				$rowCatalogAttribute->save();
				// docSystemName
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'docSystemName';
				$rowCatalogAttribute->value = $rowDocItem->DocFileName;
				$rowCatalogAttribute->save();
				// docOriginalName
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'docOriginalName';
				$rowCatalogAttribute->value = $rowDocItem->DocFileAsli;
				$rowCatalogAttribute->save();
				// docSize
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'docSize';
				$rowCatalogAttribute->value = $rowDocItem->DocSize;
				$rowCatalogAttribute->save();
				// docMimeType
				$rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
				$rowCatalogAttribute->catalogGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowCatalogAttribute->attributeGuid = 'docMimeType';
				$rowCatalogAttribute->value = $rowDocItem->DocType;
				$rowCatalogAttribute->save();


			}
			else 
			{
				echo 'Data tidak disimpan';
			}
			
			// related the file/document as RELATED_FILE

			$rowsetRelatedItem = $tblRelatedItem->find($guidPrefix.$rowDocItem->DocItemID, 'lgsimpdms'.$rowDocItem->CatalogID, "RELATED_FILE");
			if(count($rowsetRelatedItem)==0)
			{
				$rowRelation = $tblRelatedItem->createNew();
				$rowRelation->itemGuid = $guidPrefix.$rowDocItem->DocItemID;
				$rowRelation->relatedGuid = 'lgsimpdms'.$rowDocItem->CatalogID;
				$rowRelation->relateAs = "RELATED_FILE";
				if(isset($rowExp->Sequence))
					$rowRelation->valueIntRelation = $rowExp->Sequence;
				$rowRelation->save();
				//echo "<font color=blue>Record ke = ".$r." dari itemGuid = fl".$row->DocItemID." - ".$row->CatalogID." relasi file di-saved</font><br>";
			}
		}
	}
	
}