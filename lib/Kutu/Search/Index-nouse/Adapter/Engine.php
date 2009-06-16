<?php

/**
 * module search for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Search_Index_Engine
{
	private $_index;
	
	public function __construct($indexDir=null)
	{
		if(empty($indexDir))
		{
	    	$registry = Zend_Registry::getInstance(); 
			$conf = $registry->get('config');
			
			$indexDir = KUTU_ROOT_DIR.$conf->indexing->dir;
		}
		
		try 
		{
			$this->_index = Zend_Search_Lucene::open($indexDir);
		}
		catch (Exception $e)
		{
			$this->_index = Zend_Search_Lucene::create($indexDir);
		}
		
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive());
	}
	public function indexCatalog($catalogGuid)
	{
		$index = $this->_index;
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);
		if(count($rowsetCatalog))
		{
			//check if guid exist in index, then delete
			$term = new Zend_Search_Lucene_Index_Term($catalogGuid, 'guid');
			$docIds  = $index->termDocs($term);
			
			foreach ($docIds as $id) {
			    $doc = $index->getDocument($id);
			    $index->delete($id);
			}
			
			$rowCatalog = $rowsetCatalog->current();
			$doc = new Zend_Search_Lucene_Document();
			$doc->addField(Zend_Search_Lucene_Field::Keyword('guid', $rowCatalog->guid));
			
			//fill parentGuid with catalogGuid if it's kutu_doc
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
				$rowset = $tblRelatedItem->fetchAll("itemGuid='$rowCatalog->guid' AND relateAs='RELATED_FILE'");
				if(count($rowset))
				{
					$row = $rowset->current();
					$parentCatalogGuid = $row->relatedGuid;
					$doc->addField(Zend_Search_Lucene_Field::Keyword('parentguid', $parentCatalogGuid));
				}
			}
			else 
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('parentguid', $rowCatalog->guid));
			}
			
			$doc->addField(Zend_Search_Lucene_Field::Text('profile', $rowCatalog->profileGuid));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('publisheddate', $this->_filterDateTime($rowCatalog->publishedDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('expireddate', $this->_filterDateTime($rowCatalog->expiredDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('createdby', $rowCatalog->createdBy));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('modifiedby', $rowCatalog->modifiedBy));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('createddate', $this->_filterDateTime($rowCatalog->createdDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('modifieddate', $this->_filterDateTime($rowCatalog->modifiedDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('status', $rowCatalog->status));
			
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('objecttype', 'file'));
			}
			else 
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('objecttype', 'catalog'));
			}
			
			$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			if(count($rowsetCatalogAttribute))
			{
				foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
				{
					switch ($rowCatalogAttribute->attributeGuid)
					{
						case 'fixedTitle':
						case 'title':
							$doc->addField(Zend_Search_Lucene_Field::Text('title', $rowCatalogAttribute->value));
							break;
							
						case 'fixedSubTitle':
						case 'subTitle':
							$doc->addField(Zend_Search_Lucene_Field::Text('subtitle', $rowCatalogAttribute->value));
							break;
							
						case 'fixedContent':
						case 'content':
							$docHtml = Zend_Search_Lucene_Document_Html::loadHTML($rowCatalogAttribute->value);
							$cleanedText = $docHtml->getFieldValue('body');
							$doc->addField(Zend_Search_Lucene_Field::UnStored('content', $cleanedText));
							break;
							
						case 'fixedKeywords':
						case 'keywords':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('keywords', $rowCatalogAttribute->value));
							break;
						
						case 'fixedDescription':
						case 'description':
							$doc->addField(Zend_Search_Lucene_Field::Text('description', $rowCatalogAttribute->value));
							break;
							
						case 'prtNomor':
						case 'fixedNomor':
						case 'fixedNumber':
						case 'nomor':
						case 'ptsNomor':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('number', $rowCatalogAttribute->value));
							break;
						
						case 'prtTahun':
						case 'fixedTahun':
						case 'fixedYear':
						case 'tahun':
						case 'ptsTahun':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('year', $rowCatalogAttribute->value));
							break;
							
						default:
							//check if attribute is a datetime field
							$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
							$rowAttribute = $tblAttribute->find($rowCatalogAttribute->attributeGuid)->current();
							if($rowAttribute->type == 4)
							{
								$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $this->_filterDateTime($rowCatalogAttribute->value)));
							}
							else 
							{
								if($rowAttribute->type == 2)
								{
									$docHtml = Zend_Search_Lucene_Document_Html::loadHTML($rowCatalogAttribute->value);
									$cleanedText = $docHtml->getFieldValue('body');
									$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $cleanedText));
								}
								else 
								{
									$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $rowCatalogAttribute->value));
								}
							}
							break;
							
					}
				}
				//if profile=kutu_doc, extract text from its file and put it in content field
				if($rowCatalog->profileGuid=='kutu_doc')
				{
					$row = $rowsetCatalogAttribute->findByAttributeGuid('docSystemName');
					$systemName = $row->value;
					$row = $rowsetCatalogAttribute->findByAttributeGuid('docMimeType');
					$mimeType = $row->value;
					$extactedText = $this->_extractText($rowCatalog->guid, $systemName, $mimeType);
					$doc->addField(Zend_Search_Lucene_Field::UnStored('content', $extactedText));
				}
			}
			// if catalog is a kutu_doc, and if field content empty (this means 
			// file can't be read, text can't be extracted, or file empty), do not index
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$tmpS = $doc->getFieldValue('content');
				if(!empty($tmpS))
				{
					$index->addDocument($doc);
				} else {
					
				}
			}
			else 
			{
				$index->addDocument($doc);
			}
		}
		else 
		{
			// do nothing
		}
	}
	private function _filterDateTime($mySqlDateTime)
	{	
		$aReplace = array(' ',':','-');
		return str_replace($aReplace,'',$mySqlDateTime);
	}
	private function _extractText($guid, $fileName, $mimeType)
	{
		$registry = Zend_Registry::getInstance();
	    $c = $registry->get('config');
	    
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		if(count($rowset))
		{
			$row = $rowset->current();
			$parentCatalogGuid = $row->relatedGuid;
			$sDir1 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$fileName;
			$sDir2 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentCatalogGuid.DIRECTORY_SEPARATOR.$fileName;
			
			$sDir = '';
			if(file_exists($sDir1))
			{
				$sDir = $sDir1;
			}
			else 
				if(file_exists($sDir2))
				{
					$sDir = $sDir2;
				}
				
			if(!empty($sDir))
			{
				$outpath = $sDir.'.txt';
				
				switch ($mimeType)
				{
					case 'application/pdf':
						$pdfExtractor = $c->indexing->pdfextractor->executable;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        echo $value;
					        return $value;
					    }
					    if ($ret == 127)
					        //print "Could not find pdftotext tool.";
					        return '';
					    if ($ret == 1)
					        //print "Could not find pdf file.";
					        return '';
						break;
					case 'text/html':
					case 'text/plain':
						$docHtml = Zend_Search_Lucene_Document_Html::loadHTMLFile($sDir);
						return $docHtml->getFieldValue('body');
						break;
					case 'application/x-javascript':
					case 'application/octet-stream':
					case 'application/msword':
						if(strpos(strtolower($fileName), '.doc'))
						{
							$extractor = $c->indexing->wordextractor->executable;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        echo $value;
						        return $value;
						    }
						    if ($ret == 127)
						        //print "Could not find pdftotext tool.";
						        return '';
						    if ($ret == 1)
						        //print "Could not find pdf file.";
						        return '';
						}
						else 
						{
							return '';
						}
						break;
					default :
						return '';
						break;
				}
			}
		}
		return '';
	}
	
	public function optimize()
	{
		$this->_index->optimize();
	}
	public function deleteCatalogFromIndex($catalogGuid)
	{
		$index = $this->_index;
			
		//check if guid exist in index, then delete
		$term = new Zend_Search_Lucene_Index_Term($catalogGuid, 'guid');
		$docIds  = $index->termDocs($term);
		if (isset($docIds)) 
		{
			foreach ($docIds as $id) {
			    $doc = $index->getDocument($id);
			    $index->delete($id);
			}
		}
	}
	
	public function emptyIndex()
	{
		$index = $this->_index;
		$query = 'a';
		
		$hits = $index->find($query);

		foreach ($hits as $hit) {
		    echo $hit->score;
		    //echo $hit->guid;
		    //echo $hit->content;
		    $index->delete($hit->id);
		}
		
		$index->optimize();
	}
	
	function testFind($query)
	{
		$index = $this->_index;
		$hits = $index->find($query);

		foreach ($hits as $hit) {
		    echo $hit->score;
		    echo $hit->guid;
		    echo $hit->title;
		    echo $hit->modifieddate;
		    //$index->delete($hit->id);
		}
		
		echo $index->numDocs();
		echo $index->count();
		$index->optimize();
	}
	
	public function find($query)
	{
		$index = $this->_index;
		return $index->find($query);
	}
}

?>