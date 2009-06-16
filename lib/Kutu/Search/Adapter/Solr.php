<?php

/**
 * module search for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

require_once( 'Apache/Solr/Service.php' );

class Kutu_Search_Adapter_Solr extends Kutu_Search_Adapter_Abstract  
{
	private $_index;
	private $_solr;
	private $_registry;
	private $_pdfExtractor;
	private $_wordExtractor;
	
	private $_conn;
	
	
	public function __construct($solrHost, $solrPort, $solrHomeDir)
	{
		$this->_solr = new Apache_Solr_Service( $solrHost, $solrPort, $solrHomeDir );
		
		if ( ! $this->_solr->ping() ) 
		{
			
			throw new Zend_Exception('Solr service not responding.');
		 }
		 
		 $this->_registry = Zend_Registry::getInstance();
		 $c = $this->_registry->get('config');
		 $this->_pdfExtractor = $c->indexing->pdfextractor->executable;
		 $this->_wordExtractor = $c->indexing->wordextractor->executable;
		 
		 $this->_conn = Zend_Db_Table_Abstract::getDefaultAdapter();
		
	}
	public function indexCatalog($guid, $mode="NORMAL")
	{
		$solr = &$this->_solr;
		
		$tbl = new Kutu_Core_Orm_Table_Catalog();
		
		$rowset = $tbl->find($guid);
		if(count($rowset))
		{
			$row = $rowset->current();
			
			if($row->profileGuid != 'kutu_doc')
			{
				$documents = array();
			
				$documents[] = $this->_createSolrDocument($row, $mode);
			
				try {
					$solr->addDocuments( $documents );
					$solr->commit();
				}
				catch ( Exception $e ) {
					throw new Zend_Exception($e->getMessage());
					//echo $e->getMessage();
				}
			}
		}
	}
	public function reIndexCatalog($mode="NORMAL", $folderGuid='')
	{
		
		$time_start = microtime(true);
		
		$solr = &$this->_solr;
		
		//$tbl = new Kutu_Core_Orm_Table_Catalog();
		//$rowset = $tbl->fetchAll(); //("profileGuid='kutu_peraturan'");
		
		if(empty($folderGuid))
		{
			$this->emptyIndex();
			
			$query="SELECT * FROM KutuCatalog where profileGuid != 'kutu_doc'";
			$results = $this->_conn->query($query);
		}
		else
		{
			$query="SELECT A.* FROM KutuCatalog A,KutuCatalogFolder B 
						where B.folderGuid='$folderGuid' AND A.profileGuid != 'kutu_doc' AND A.guid=B.catalogGuid ";
			$results = $this->_conn->query($query);
		}
		
		
		
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		  
		$documents = array();
		$rowCount = count($rowset);
		echo $rowCount;
		//die($rowCount);
		for($iCount=0;$iCount<$rowCount;$iCount++)
		{
			$row = $rowset[$iCount];
			
			echo 'urutan: '.$iCount .'<br>';
			
		  	$documents[] = $this->_createSolrDocument($row, $mode);
		  	
		  	if($iCount%1000 == 0)
		  	{
			  	try 
			  	{
					$solr->addDocuments( $documents );
					$solr->commit();
					//$solr->optimize();
					$documents = array();
					/*if($iCount>2000)
					{
						echo $iCount;
						die();
					}*/
				}
				catch ( Exception $e ) 
				{
					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount;
					throw new Zend_Exception($e->getMessage());
					//echo $e->getMessage();
				}
		  	}
		}
		  
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			//$solr->optimize();
		}
		catch ( Exception $e ) {
			throw new Zend_Exception($e->getMessage());
			//echo $e->getMessage();
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		echo'<br>WAKTU EKSEKUSI: '. $time;
		
		//update status in KutuCatalog: isIndex and lastIndexedDate
		
		
	}
	private function _createSolrDocument(&$row, $mode='NORMAL')
	{
		
		$part = new Apache_Solr_Document();
	  	$part->id = $row->guid;
	  	$part->shortTitle = $row->shortTitle;
	  	$part->profile = $row->profileGuid;
	  	$part->publishedDate = $this->_translateMySqlDateToSolrDate($row->publishedDate);
	  	$part->expiredDate = $this->_translateMySqlDateToSolrDate($row->expiredDate);
	  	$part->createdBy = $row->createdBy;
	  	$part->createdDate = $this->_translateMySqlDateToSolrDate($row->createdDate);
	  	$part->modifiedBy = $row->modifiedBy;
	  	$part->modifiedDate = $this->_translateMySqlDateToSolrDate($row->modifiedDate);
	  	if(!$row->status==null)
	  		$part->status = $row->status;
	  	$part->url = ''; //TODO what to input here as the URL???
	  	$part->serviceId = '';
		$part->price = (!empty($row->price))?$row->price:0;
	  	
	  	  
	  	//$rowsetAttr = $row->findDependentRowsetCatalogAttribute();
	  	
	  	$query="SELECT * FROM KutuCatalogAttribute where catalogGuid='".$row->guid."'";
		$results2 = $this->_conn->query($query);
		
		$rowsetAttr = $results2->fetchAll(PDO::FETCH_OBJ);
	  	
	  	$rowCount = count($rowsetAttr);
	  	for($i=0;$i<$rowCount;$i++)
	  	{
	  		$rowAttr = $rowsetAttr[$i];
			switch ($rowAttr->attributeGuid)
			{
	  	  	  	case 'fixedTitle':
	  	  	  		if(empty($rowAttr->value))
	  	  	  		{
	  	  	  			$part->title = $row->shortTitle;
	  	  	  		}
	  	  	  		else 
	  	  	  		{
	  	  	  			$part->title = $rowAttr->value;
	  	  	  		}
	  	  	  		//echo $part->title.'<br>';
	  	  	  		break;
	  	  	  	case 'fixedSubTitle':
	  	  	  		$part->subTitle = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedContent':
	  	  	  		$part->content = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedKeywords':
	  	  	  		$part->keywords = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDescription':
	  	  	  		$part->description = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedComments':
	  	  	  		$part->comments = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedNumber':
	  	  	  	case 'prtNomor':
	  	  	  	case 'ptsNomor':
	  	  	  		$part->number = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedYear':
	  	  	  	case 'ptsTahun':
	  	  	  	case 'prtTahun':
	  	  	  		$part->year = (int)$rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDate':
	  	  	  	case 'prtDisahkan':
	  	  	  	case 'ptsDibacakan':
	  	  	  		$part->date = $this->_translateMySqlDateToSolrDate($rowAttr->value);
	  	  	  		break;
	  	  	  	case 'fixedLanguage':
	  	  	  		$part->language = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'prtJenis':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		//echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		//enter the regulation type order. i.e. undang-undang=1, pp=2, dst.
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'konstitusi':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'tap mpr':
	  	  	  				$part->regulationOrder = 11;
	  	  	  				break;
	  	  	  			case 'tus mpr':
	  	  	  				$part->regulationOrder = 21;
	  	  	  				break;
	  	  	  			case 'undang-undang':
	  	  	  			case 'uu':
	  	  	  				$part->regulationOrder = 31;
	  	  	  				break;
	  	  	  			case 'undang-undang darurat':
	  	  	  				$part->regulationOrder = 41;
	  	  	  				break;
	  	  	  			case 'perpu':
	  	  	  				$part->regulationOrder = 51;
	  	  	  				break;
	  	  	  			case 'pp':
	  	  	  				$part->regulationOrder = 61;
	  	  	  				break;
	  	  	  			case 'perpres':
	  	  	  				$part->regulationOrder = 71;
	  	  	  				break;
	  	  	  			case 'penpres':
	  	  	  				$part->regulationOrder = 81;
	  	  	  				break;
	  	  	  			case 'keppres':
	  	  	  				$part->regulationOrder = 91;
	  	  	  				break;
	  	  	  			case 'inpres':
	  	  	  				$part->regulationOrder = 101;
	  	  	  				break;
	  	  	  			case 'konvensi internasional':
	  	  	  				$part->regulationOrder = 111;
	  	  	  				break;
	  	  	  			case 'keputusan bersama':
	  	  	  				$part->regulationOrder = 121;
	  	  	  				break;
	  	  	  			case 'keputusan dewan':
	  	  	  				$part->regulationOrder = 131;
	  	  	  				break;
	  	  	  			case 'kepmen':
	  	  	  				$part->regulationOrder = 141;
	  	  	  				break;
	  	  	  			case 'permen':
	  	  	  				$part->regulationOrder = 151;
	  	  	  				break;
	  	  	  			case 'inmen':
	  	  	  				$part->regulationOrder = 161;
	  	  	  				break;
	  	  	  			case 'pengumuman menteri':
	  	  	  				$part->regulationOrder = 171;
	  	  	  				break;
	  	  	  			case 'surat edaran menteri':
	  	  	  				$part->regulationOrder = 181;
	  	  	  				break;
	  	  	  			case 'surat menteri':
	  	  	  				$part->regulationOrder = 191;
	  	  	  				break;
	  	  	  			case 'keputusan asisten menteri':
	  	  	  				$part->regulationOrder = 201;
	  	  	  				break;
	  	  	  			case 'surat asisten menteri':
	  	  	  				$part->regulationOrder = 211;
	  	  	  				break;
	  	  	  			case "keputusan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 221;
	  	  	  				break;
	  	  	  			case "peraturan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 231;
	  	  	  				break;
	  	  	  			case "instruksi menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 241;
	  	  	  				break;
	  	  	  			case "pengumuman menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 251;
	  	  	  				break;
	  	  	  			case "surat edaran menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 261;
	  	  	  				break;
	  	  	  			case "surat menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 271;
	  	  	  				break;
	  	  	  			case "keputusan lembaga/badan":
	  	  	  				$part->regulationOrder = 281;
	  	  	  				break;
	  	  	  			case "peraturan lembaga/badan":
	  	  	  				$part->regulationOrder = 291;
	  	  	  				break;
	  	  	  			case "instruksi lembaga/badan":
	  	  	  				$part->regulationOrder = 301;
	  	  	  				break;
	  	  	  			case "pengumuman lembaga/badan":
	  	  	  				$part->regulationOrder = 311;
	  	  	  				break;
	  	  	  			case "surat edaran lembaga/badan":
	  	  	  				$part->regulationOrder = 321;
	  	  	  				break;
	  	  	  			case "surat lembaga/badan":
	  	  	  				$part->regulationOrder = 331;
	  	  	  				break;
	  	  	  			case "keputusan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 341;
	  	  	  				break;
	  	  	  			case "peraturan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 351;
	  	  	  				break;
	  	  	  			case "instruksi kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 361;
	  	  	  				break;
	  	  	  			case "pengumuman kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 371;
	  	  	  				break;
	  	  	  			case "surat edaran kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 381;
	  	  	  				break;
	  	  	  			case "surat kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 391;
	  	  	  				break;
	  	  	  			case "keputusan komisi":
	  	  	  				$part->regulationOrder = 401;
	  	  	  				break;
	  	  	  			case "peraturan komisi":
	  	  	  				$part->regulationOrder = 411;
	  	  	  				break;
	  	  	  			case "instruksi komisi":
	  	  	  				$part->regulationOrder = 421;
	  	  	  				break;
	  	  	  			case "pengumuman komisi":
	  	  	  				$part->regulationOrder = 431;
	  	  	  				break;
	  	  	  			case "surat edaran komisi":
	  	  	  				$part->regulationOrder = 441;
	  	  	  				break;
	  	  	  			case "surat komisi":
	  	  	  				$part->regulationOrder = 451;
	  	  	  				break;
	  	  	  			case "keputusan panitia":
	  	  	  				$part->regulationOrder = 461;
	  	  	  				break;
	  	  	  			case "peraturan panitia":
	  	  	  				$part->regulationOrder = 471;
	  	  	  				break;
	  	  	  			case "instruksi panitia":
	  	  	  				$part->regulationOrder = 481;
	  	  	  				break;
	  	  	  			case "pengumuman panitia":
	  	  	  				$part->regulationOrder = 491;
	  	  	  				break;
	  	  	  			case "surat edaran panitia":
	  	  	  				$part->regulationOrder = 501;
	  	  	  				break;
	  	  	  			case "surat panitia":
	  	  	  				$part->regulationOrder = 511;
	  	  	  				break;
	  	  	  			case "keputusan direktur jenderal":
	  	  	  				$part->regulationOrder = 521;
	  	  	  				break;
	  	  	  			case "surat edaran direktur jenderal":
	  	  	  				$part->regulationOrder = 531;
	  	  	  				break;
	  	  	  			case "surat direktur jenderal":
	  	  	  				$part->regulationOrder = 541;
	  	  	  				break;
	  	  	  			case "instruksi direktur jenderal":
	  	  	  				$part->regulationOrder = 551;
	  	  	  				break;
	  	  	  			case "peraturan direktur jenderal":
	  	  	  				$part->regulationOrder = 561;
	  	  	  				break;
	  	  	  			case "peraturan inspektur jenderal":
	  	  	  				$part->regulationOrder = 571;
	  	  	  				break;
	  	  	  			case "instruksi inspektur jenderal":
	  	  	  				$part->regulationOrder = 581;
	  	  	  				break;
	  	  	  			case "pengumuman inspektur jenderal":
	  	  	  				$part->regulationOrder = 591;
	  	  	  				break;
	  	  	  			case "surat edaran inspektur jenderal":
	  	  	  				$part->regulationOrder = 601;
	  	  	  				break;
	  	  	  			case "surat inspektur jenderal":
	  	  	  				$part->regulationOrder = 611;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat i":
	  	  	  				$part->regulationOrder = 621;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat ii":
	  	  	  				$part->regulationOrder = 631;
	  	  	  				break;
	  	  	  			case "keputusan gubernur":
	  	  	  				$part->regulationOrder = 641;
	  	  	  				break;
	  	  	  			case "peraturan gubernur":
	  	  	  				$part->regulationOrder = 651;
	  	  	  				break;
	  	  	  			case "instruksi gubernur":
	  	  	  				$part->regulationOrder = 661;
	  	  	  				break;
	  	  	  			case "pengumuman gubernur":
	  	  	  				$part->regulationOrder = 671;
	  	  	  				break;
	  	  	  			case "surat edaran gubernur":
	  	  	  				$part->regulationOrder = 681;
	  	  	  				break;
	  	  	  			case "surat gubernur":
	  	  	  				$part->regulationOrder = 691;
	  	  	  				break;
	  	  	  			case "keputusan bupati/walikota":
	  	  	  				$part->regulationOrder = 701;
	  	  	  				break;
	  	  	  			case "peraturan bupati/walikota":
	  	  	  				$part->regulationOrder = 711;
	  	  	  				break;
	  	  	  			case "instruksi bupati/walikota":
	  	  	  				$part->regulationOrder = 721;
	  	  	  				break;
	  	  	  			case "pengumuman bupati/walikota":
	  	  	  				$part->regulationOrder = 731;
	  	  	  				break;
	  	  	  			case "surat edaran bupati/walikota":
	  	  	  				$part->regulationOrder = 741;
	  	  	  				break;
	  	  	  			case "surat bupati/walikota":
	  	  	  				$part->regulationOrder = 751;
	  	  	  				break;
	  	  	  			case "keputusan direksi":
	  	  	  				$part->regulationOrder = 761;
	  	  	  				break;
	  	  	  			case "peraturan direksi":
	  	  	  				$part->regulationOrder = 771;
	  	  	  				break;
	  	  	  			case "instruksi direksi":
	  	  	  				$part->regulationOrder = 781;
	  	  	  				break;
	  	  	  			case "pengumuman direksi":
	  	  	  				$part->regulationOrder = 791;
	  	  	  				break;
	  	  	  			case "surat edaran direksi":
	  	  	  				$part->regulationOrder = 801;
	  	  	  				break;
	  	  	  			case "surat direksi":
	  	  	  				$part->regulationOrder = 811;
	  	  	  				break;
	  	  	  			case "keputusan direktur":
	  	  	  				$part->regulationOrder = 821;
	  	  	  				break;
	  	  	  			case "peraturan direktur":
	  	  	  				$part->regulationOrder = 831;
	  	  	  				break;
	  	  	  			case "instruksi direktur":
	  	  	  				$part->regulationOrder = 841;
	  	  	  				break;
	  	  	  			case "pengumuman direktur":
	  	  	  				$part->regulationOrder = 851;
	  	  	  				break;
	  	  	  			case "surat edaran direktur":
	  	  	  				$part->regulationOrder = 861;
	  	  	  				break;
	  	  	  			case "surat direktur":
	  	  	  				$part->regulationOrder = 871;
	  	  	  				break;
	  	  	  			/*case :
	  	  	  				$part->regulationOrder = 881;
	  	  	  				break;*/
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'ptsJenisLembaga':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		//echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'ma':
	  	  	  			case 'mk':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'pt':
	  	  	  			case 'pttun':
	  	  	  			case 'pta':
	  	  	  			case 'mahmiltinggi':
	  	  	  				$part->regulationOrder = 20;
	  	  	  				break;
	  	  	  			case 'pn':
	  	  	  			case 'ptun':
	  	  	  			case 'pa':
	  	  	  			case 'pniaga':
	  	  	  			case 'mahmil':
	  	  	  				$part->regulationOrder = 30;
	  	  	  				break;
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'docMimeType':
	  	  	  		$part->mimeType = $rowAttr->value;
	  	  	  		$docMimeType = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docOriginalName':
	  	  	  		$part->fileName = $rowAttr->value;
	  	  	  		$docOriginalName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSystemName':
	  	  	  		$docSystemName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSize':
	  	  	  		// $part->fileSize = $rowAttr->value; //TODO conver to float first
	  	  	  		break;
				default:
					if(isset($part->all))
					{
						$part->all .= ' '.$rowAttr->value;
					}
					else 
					{
						$part->all = $rowAttr->value;
					}
			}
			 
	  	}
		//if($row->profileGuid=='kutu_doc')
		if(false)
		{
			//extract text from the file
			$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType);
			//$sContent = $this->clean_string_input($sContent);
			if(isset($part->content))
			{
				$part->content .= ' '.$sContent;
			}
			else 
			{
				$part->content = $sContent;
			}
		}
		
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		if($mode == 'NORMAL')
		{
			if($row->profileGuid !='kutu_doc')
			{
				//$where = "relatedGuid='$row->guid' AND relateAs='RELATED_FILE'";
				//$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
				
				$db = Zend_Db_Table::getDefaultAdapter()->query("select itemGuid from KutuRelatedItem where relatedGuid='$row->guid' AND relateAs='RELATED_FILE'");
				$rowsetRelatedItem = $db->fetchAll(Zend_Db::FETCH_OBJ);
				
				
				$parentGuid = $row->guid;
				
				/*$query = "SELECT itemGuid, A.value as docSystemName, B.value as docOriginalName, C.value as docMimeType FROM `KutuRelatedItem`, KutuCatalogAttribute as A,  KutuCatalogAttribute as B, KutuCatalogAttribute as C  
				WHERE relatedGuid='$row->guid' and relateAs='RELATED_FILE' AND A.catalogGuid=KutuRelatedItem.itemGuid and A.attributeGuid='docSystemName' and B.catalogGuid=KutuRelatedItem.itemGuid and B.attributeGuid='docOriginalName' and C.catalogGuid=KutuRelatedItem.itemGuid and C.attributeGuid='docMimeType'";
				$db = Zend_Db_Table::getDefaultAdapter()->query($query);
				$rowsetRelatedItem = $db->fetchAll(Zend_Db::FETCH_OBJ);
				echo count($rowsetRelatedItem);
				die();*/
				
				
				$numRelatedFiles = count($rowsetRelatedItem);
				
			
				for($i=0;$i<$numRelatedFiles;$i++)
				{
					$row = $rowsetRelatedItem[$i];
					//$row = $rowsetRelatedItem->current();
					$catalogGuid = $row->itemGuid;
					$sContent = '';
					
					$db = Zend_Db_Table::getDefaultAdapter()->query("select value from KutuCatalogAttribute where catalogGuid='$catalogGuid' and attributeGuid='docSystemName'");
					$docSystemName = $db->fetchColumn(0);
					$db = Zend_Db_Table::getDefaultAdapter()->query("select value from KutuCatalogAttribute where catalogGuid='$catalogGuid' and attributeGuid='docOriginalName'");
					$docOriginalName = $db->fetchColumn(0);
					$db = Zend_Db_Table::getDefaultAdapter()->query("select value from KutuCatalogAttribute where catalogGuid='$catalogGuid' and attributeGuid='docMimeType'");
					$docMimeType = $db->fetchColumn(0);
					
					/*//$rowDocSystemName = $tblCatalogAttribute->fetchRow("catalogGuid='$catalogGuid' AND attributeGuid='docSystemName'");
					//$rowDocOriginalName = $tblCatalogAttribute->fetchRow("catalogGuid='$catalogGuid' AND attributeGuid='docOriginalName'");
					//$rowDocMimeType = $tblCatalogAttribute->fetchRow("catalogGuid='$catalogGuid' AND attributeGuid='docMimeType'");
					/*
					
					$docSystemName = $rowDocSystemName->value;
					$docOriginalName = $rowDocOriginalName->value;
					$docMimeType = $rowDocMimeType->value;*/
					
					$sContent = $this->_extractText($catalogGuid, $docSystemName, $docOriginalName, $docMimeType, $parentGuid);
				
					//$sContent = $this->clean_string_input($sContent);
					if(isset($part->content))
					{
						$part->content .= ' '.$sContent;
					}
					else 
					{
						$part->content = $sContent;
					}
					//die($part->content);
					//$rowsetRelatedItem->next();
				}
			}
		}
	  	
		
		return $part;
	}
	public function test1Action()
	{
		
	}
	
	public function reIndexCatalog_ZendDb()
	{
		$this->emptyIndex();
		
		$time_start = microtime(true);
		
		$solr = &$this->_solr;
		
		$tbl = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tbl->fetchAll(); //("profileGuid='kutu_peraturan'");
		  
		$documents = array();
		$rowCount = count($rowset);
		for($iCount=0;$iCount<$rowCount;$iCount++)
		{
			$row = $rowset->current();
//			if($iCount == 100)
//				break;
			echo 'urutan: '.$iCount .'<br>';
			
		  	$documents[] = $this->_createSolrDocument($row);
		  	$rowset->next();
		  	
		  	if($iCount%1000 == 0)
		  	{
			  	try 
			  	{
					$solr->addDocuments( $documents );
					$solr->commit();
					//$solr->optimize();
					$documents = array();
				}
				catch ( Exception $e ) 
				{
					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount;
					throw new Zend_Exception($e->getMessage());
					//echo $e->getMessage();
				}
		  	}
		}
		  
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			throw new Zend_Exception($e->getMessage());
			//echo $e->getMessage();
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		echo'<br>WAKTU EKSEKUSI: '. $time;
		
		
	}
	
	private function _createSolrDocument_ZendDb(&$row)
	{
		
		$part = new Apache_Solr_Document();
	  	$part->id = $row->guid;
	  	$part->shortTitle = $row->shortTitle;
	  	$part->profile = $row->profileGuid;
	  	$part->publishedDate = $this->_translateMySqlDateToSolrDate($row->publishedDate);
	  	$part->expiredDate = $this->_translateMySqlDateToSolrDate($row->expiredDate);
	  	$part->createdBy = $row->createdBy;
	  	$part->createdDate = $this->_translateMySqlDateToSolrDate($row->createdDate);
	  	$part->modifiedBy = $row->modifiedBy;
	  	$part->modifiedDate = $this->_translateMySqlDateToSolrDate($row->modifiedDate);
	  	if(!$row->status==null)
	  		$part->status = $row->status;
	  	$part->url = ''; //TODO what to input here as the URL???
	  	$part->serviceId = '';
	  	
	  	  
	  	$rowsetAttr = $row->findDependentRowsetCatalogAttribute();
	  	
	  	$rowCount = count($rowsetAttr);
	  	//foreach ($rowsetAttr as $rowAttr)
	  	for($i=0;$i<$rowCount;$i++)
	  	{
	  		$rowAttr = $rowsetAttr->current();
			switch ($rowAttr->attributeGuid)
			{
	  	  	  	case 'fixedTitle':
	  	  	  		if(empty($rowAttr->value))
	  	  	  		{
	  	  	  			$part->title = $row->shortTitle;
	  	  	  		}
	  	  	  		else 
	  	  	  		{
	  	  	  			$part->title = $rowAttr->value;
	  	  	  		}
	  	  	  		echo $part->title.'<br>';
	  	  	  		break;
	  	  	  	case 'fixedSubTitle':
	  	  	  		$part->subTitle = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedContent':
	  	  	  		$part->content = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedKeywords':
	  	  	  		$part->keywords = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDescription':
	  	  	  		$part->description = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedComments':
	  	  	  		$part->comments = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedNumber':
	  	  	  	case 'prtNomor':
	  	  	  	case 'ptsNomor':
	  	  	  		$part->number = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedYear':
	  	  	  	case 'ptsTahun':
	  	  	  	case 'prtTahun':
	  	  	  		$part->year = (int)$rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDate':
	  	  	  	case 'prtDisahkan':
	  	  	  	case 'ptsDibacakan':
	  	  	  		$part->date = $this->_translateMySqlDateToSolrDate($rowAttr->value);
	  	  	  		break;
	  	  	  	case 'fixedLanguage':
	  	  	  		$part->language = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'prtJenis':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		//enter the regulation type order. i.e. undang-undang=1, pp=2, dst.
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'konstitusi':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'tap mpr':
	  	  	  				$part->regulationOrder = 11;
	  	  	  				break;
	  	  	  			case 'tus mpr':
	  	  	  				$part->regulationOrder = 21;
	  	  	  				break;
	  	  	  			case 'undang-undang':
	  	  	  			case 'uu':
	  	  	  				$part->regulationOrder = 31;
	  	  	  				break;
	  	  	  			case 'undang-undang darurat':
	  	  	  				$part->regulationOrder = 41;
	  	  	  				break;
	  	  	  			case 'perpu':
	  	  	  				$part->regulationOrder = 51;
	  	  	  				break;
	  	  	  			case 'pp':
	  	  	  				$part->regulationOrder = 61;
	  	  	  				break;
	  	  	  			case 'perpres':
	  	  	  				$part->regulationOrder = 71;
	  	  	  				break;
	  	  	  			case 'penpres':
	  	  	  				$part->regulationOrder = 81;
	  	  	  				break;
	  	  	  			case 'keppres':
	  	  	  				$part->regulationOrder = 91;
	  	  	  				break;
	  	  	  			case 'inpres':
	  	  	  				$part->regulationOrder = 101;
	  	  	  				break;
	  	  	  			case 'konvensi internasional':
	  	  	  				$part->regulationOrder = 111;
	  	  	  				break;
	  	  	  			case 'keputusan bersama':
	  	  	  				$part->regulationOrder = 121;
	  	  	  				break;
	  	  	  			case 'keputusan dewan':
	  	  	  				$part->regulationOrder = 131;
	  	  	  				break;
	  	  	  			case 'kepmen':
	  	  	  				$part->regulationOrder = 141;
	  	  	  				break;
	  	  	  			case 'permen':
	  	  	  				$part->regulationOrder = 151;
	  	  	  				break;
	  	  	  			case 'inmen':
	  	  	  				$part->regulationOrder = 161;
	  	  	  				break;
	  	  	  			case 'pengumuman menteri':
	  	  	  				$part->regulationOrder = 171;
	  	  	  				break;
	  	  	  			case 'surat edaran menteri':
	  	  	  				$part->regulationOrder = 181;
	  	  	  				break;
	  	  	  			case 'surat menteri':
	  	  	  				$part->regulationOrder = 191;
	  	  	  				break;
	  	  	  			case 'keputusan asisten menteri':
	  	  	  				$part->regulationOrder = 201;
	  	  	  				break;
	  	  	  			case 'surat asisten menteri':
	  	  	  				$part->regulationOrder = 211;
	  	  	  				break;
	  	  	  			case "keputusan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 221;
	  	  	  				break;
	  	  	  			case "peraturan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 231;
	  	  	  				break;
	  	  	  			case "instruksi menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 241;
	  	  	  				break;
	  	  	  			case "pengumuman menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 251;
	  	  	  				break;
	  	  	  			case "surat edaran menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 261;
	  	  	  				break;
	  	  	  			case "surat menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 271;
	  	  	  				break;
	  	  	  			case "keputusan lembaga/badan":
	  	  	  				$part->regulationOrder = 281;
	  	  	  				break;
	  	  	  			case "peraturan lembaga/badan":
	  	  	  				$part->regulationOrder = 291;
	  	  	  				break;
	  	  	  			case "instruksi lembaga/badan":
	  	  	  				$part->regulationOrder = 301;
	  	  	  				break;
	  	  	  			case "pengumuman lembaga/badan":
	  	  	  				$part->regulationOrder = 311;
	  	  	  				break;
	  	  	  			case "surat edaran lembaga/badan":
	  	  	  				$part->regulationOrder = 321;
	  	  	  				break;
	  	  	  			case "surat lembaga/badan":
	  	  	  				$part->regulationOrder = 331;
	  	  	  				break;
	  	  	  			case "keputusan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 341;
	  	  	  				break;
	  	  	  			case "peraturan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 351;
	  	  	  				break;
	  	  	  			case "instruksi kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 361;
	  	  	  				break;
	  	  	  			case "pengumuman kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 371;
	  	  	  				break;
	  	  	  			case "surat edaran kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 381;
	  	  	  				break;
	  	  	  			case "surat kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 391;
	  	  	  				break;
	  	  	  			case "keputusan komisi":
	  	  	  				$part->regulationOrder = 401;
	  	  	  				break;
	  	  	  			case "peraturan komisi":
	  	  	  				$part->regulationOrder = 411;
	  	  	  				break;
	  	  	  			case "instruksi komisi":
	  	  	  				$part->regulationOrder = 421;
	  	  	  				break;
	  	  	  			case "pengumuman komisi":
	  	  	  				$part->regulationOrder = 431;
	  	  	  				break;
	  	  	  			case "surat edaran komisi":
	  	  	  				$part->regulationOrder = 441;
	  	  	  				break;
	  	  	  			case "surat komisi":
	  	  	  				$part->regulationOrder = 451;
	  	  	  				break;
	  	  	  			case "keputusan panitia":
	  	  	  				$part->regulationOrder = 461;
	  	  	  				break;
	  	  	  			case "peraturan panitia":
	  	  	  				$part->regulationOrder = 471;
	  	  	  				break;
	  	  	  			case "instruksi panitia":
	  	  	  				$part->regulationOrder = 481;
	  	  	  				break;
	  	  	  			case "pengumuman panitia":
	  	  	  				$part->regulationOrder = 491;
	  	  	  				break;
	  	  	  			case "surat edaran panitia":
	  	  	  				$part->regulationOrder = 501;
	  	  	  				break;
	  	  	  			case "surat panitia":
	  	  	  				$part->regulationOrder = 511;
	  	  	  				break;
	  	  	  			case "keputusan direktur jenderal":
	  	  	  				$part->regulationOrder = 521;
	  	  	  				break;
	  	  	  			case "surat edaran direktur jenderal":
	  	  	  				$part->regulationOrder = 531;
	  	  	  				break;
	  	  	  			case "surat direktur jenderal":
	  	  	  				$part->regulationOrder = 541;
	  	  	  				break;
	  	  	  			case "instruksi direktur jenderal":
	  	  	  				$part->regulationOrder = 551;
	  	  	  				break;
	  	  	  			case "peraturan direktur jenderal":
	  	  	  				$part->regulationOrder = 561;
	  	  	  				break;
	  	  	  			case "peraturan inspektur jenderal":
	  	  	  				$part->regulationOrder = 571;
	  	  	  				break;
	  	  	  			case "instruksi inspektur jenderal":
	  	  	  				$part->regulationOrder = 581;
	  	  	  				break;
	  	  	  			case "pengumuman inspektur jenderal":
	  	  	  				$part->regulationOrder = 591;
	  	  	  				break;
	  	  	  			case "surat edaran inspektur jenderal":
	  	  	  				$part->regulationOrder = 601;
	  	  	  				break;
	  	  	  			case "surat inspektur jenderal":
	  	  	  				$part->regulationOrder = 611;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat i":
	  	  	  				$part->regulationOrder = 621;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat ii":
	  	  	  				$part->regulationOrder = 631;
	  	  	  				break;
	  	  	  			case "keputusan gubernur":
	  	  	  				$part->regulationOrder = 641;
	  	  	  				break;
	  	  	  			case "peraturan gubernur":
	  	  	  				$part->regulationOrder = 651;
	  	  	  				break;
	  	  	  			case "instruksi gubernur":
	  	  	  				$part->regulationOrder = 661;
	  	  	  				break;
	  	  	  			case "pengumuman gubernur":
	  	  	  				$part->regulationOrder = 671;
	  	  	  				break;
	  	  	  			case "surat edaran gubernur":
	  	  	  				$part->regulationOrder = 681;
	  	  	  				break;
	  	  	  			case "surat gubernur":
	  	  	  				$part->regulationOrder = 691;
	  	  	  				break;
	  	  	  			case "keputusan bupati/walikota":
	  	  	  				$part->regulationOrder = 701;
	  	  	  				break;
	  	  	  			case "peraturan bupati/walikota":
	  	  	  				$part->regulationOrder = 711;
	  	  	  				break;
	  	  	  			case "instruksi bupati/walikota":
	  	  	  				$part->regulationOrder = 721;
	  	  	  				break;
	  	  	  			case "pengumuman bupati/walikota":
	  	  	  				$part->regulationOrder = 731;
	  	  	  				break;
	  	  	  			case "surat edaran bupati/walikota":
	  	  	  				$part->regulationOrder = 741;
	  	  	  				break;
	  	  	  			case "surat bupati/walikota":
	  	  	  				$part->regulationOrder = 751;
	  	  	  				break;
	  	  	  			case "keputusan direksi":
	  	  	  				$part->regulationOrder = 761;
	  	  	  				break;
	  	  	  			case "peraturan direksi":
	  	  	  				$part->regulationOrder = 771;
	  	  	  				break;
	  	  	  			case "instruksi direksi":
	  	  	  				$part->regulationOrder = 781;
	  	  	  				break;
	  	  	  			case "pengumuman direksi":
	  	  	  				$part->regulationOrder = 791;
	  	  	  				break;
	  	  	  			case "surat edaran direksi":
	  	  	  				$part->regulationOrder = 801;
	  	  	  				break;
	  	  	  			case "surat direksi":
	  	  	  				$part->regulationOrder = 811;
	  	  	  				break;
	  	  	  			case "keputusan direktur":
	  	  	  				$part->regulationOrder = 821;
	  	  	  				break;
	  	  	  			case "peraturan direktur":
	  	  	  				$part->regulationOrder = 831;
	  	  	  				break;
	  	  	  			case "instruksi direktur":
	  	  	  				$part->regulationOrder = 841;
	  	  	  				break;
	  	  	  			case "pengumuman direktur":
	  	  	  				$part->regulationOrder = 851;
	  	  	  				break;
	  	  	  			case "surat edaran direktur":
	  	  	  				$part->regulationOrder = 861;
	  	  	  				break;
	  	  	  			case "surat direktur":
	  	  	  				$part->regulationOrder = 871;
	  	  	  				break;
	  	  	  			/*case :
	  	  	  				$part->regulationOrder = 881;
	  	  	  				break;*/
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'ptsJenisLembaga':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'ma':
	  	  	  			case 'mk':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'pt':
	  	  	  			case 'pttun':
	  	  	  			case 'pta':
	  	  	  			case 'mahmiltinggi':
	  	  	  				$part->regulationOrder = 20;
	  	  	  				break;
	  	  	  			case 'pn':
	  	  	  			case 'ptun':
	  	  	  			case 'pa':
	  	  	  			case 'pniaga':
	  	  	  			case 'mahmil':
	  	  	  				$part->regulationOrder = 30;
	  	  	  				break;
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'docMimeType':
	  	  	  		$part->mimeType = $rowAttr->value;
	  	  	  		$docMimeType = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docOriginalName':
	  	  	  		$part->fileName = $rowAttr->value;
	  	  	  		$docOriginalName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSystemName':
	  	  	  		$docSystemName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSize':
	  	  	  		// $part->fileSize = $rowAttr->value; //TODO conver to float first
	  	  	  		break;
	  	  	  	default:
					if(isset($part->all))
					{
						$part->all .= ' '.$rowAttr->value;
					}
					else 
					{
						$part->all = $rowAttr->value;
					}
			}
			$rowsetAttr->next();
			 
	  	}
		if($row->profileGuid=='kutu_doc')
		{
			//extract text from the file
			$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType);
			//$sContent = $this->clean_string_input($sContent);
			if(isset($part->content))
			{
				$part->content .= ' '.$sContent;
			}
			else 
			{
				$part->content = $sContent;
			}
		}
	  	return $part;
	}
	
	public function _translateMySqlDateToSolrDate($mysqlDate)
	{
		//if(Zend_Date::isDate($mysqlDate, "yyyy-MM-dd HH:mm:ss"))
		if(true)
		{
			$aDateTime = explode(' ', $mysqlDate);
			if(!empty($aDateTime[0]) && strlen($aDateTime[0])==10)
				$aDateTime[0] .= 'T';
			else 
				$aDateTime[0] = '0000-00-00T';
			if(isset($aDateTime[1]) && !empty($aDateTime[1]))
				$aDateTime[1] .= 'Z';
			else 
				$aDateTime[1] = '00:00:00Z';
				
			$solrDate = $aDateTime[0].$aDateTime[1];
			//echo '<br>'.$solrDate;
			return $solrDate;
		}
		else 
		{
			return '0000-00-00T00:00:00Z';
		}
		
	}
	private function _extractText($guid, $systemName, $fileName, $mimeType, $parentGuid)
	{
	    //$c = $this->_registry->get('config');
	    
		//$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		//$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		
		//$query="SELECT * FROM KutuRelatedItem where itemGuid='$guid' AND relateAs='RELATED_FILE'";
		//$results = $this->_conn->query($query);
		
		//$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		
		//if(count($rowset))
		//{
			//$row = $rowset[0];
			//$parentCatalogGuid = $row->relatedGuid;
			$parentCatalogGuid = $parentGuid;
			
			if(!empty($systemName))
				$fileName = $systemName;
			
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
				
				switch (trim($mimeType))
				{
					case 'application/pdf':
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        //echo 'content PDF: '. $sDir.' ' . strlen($value);
					        if(strlen($value) > 20)
					        	return $this->clean_string_input($value);
					        else 
					        {
					        	//echo 'content file kosong';
					        	return '';
					        }
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
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        //echo $value;
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
		//}
		return '';
	}
	
	private function _extractText_ZendDb($guid, $systemName, $fileName, $mimeType)
	{
	    //$c = $this->_registry->get('config');
	    
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		if(count($rowset))
		{
			$row = $rowset->current();
			$parentCatalogGuid = $row->relatedGuid;
			
			if(!empty($systemName))
				$fileName = $systemName;
			
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
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        //echo 'content PDF: '. $sDir.' ' . strlen($value);
					        if(strlen($value) > 20)
					        	return $this->clean_string_input($value);
					        else 
					        {
					        	//echo 'content file kosong';
					        	return '';
					        }
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
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        //echo $value;
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
	
	public function deleteCatalogFromIndex($catalogGuid)
	{
		$solr = &$this->_solr;
		$solr->deleteById($catalogGuid);
		$solr->commit();
	}
	
	public function optimizeIndex()
	{
		$this->_solr->optimize();
	}
	
	public function emptyIndex()
	{
		$solr = &$this->_solr;
		$solr->deleteByQuery('*:*'); //deletes ALL documents - be careful :)
   		$solr->commit();
	}
	
	public function find($query,$start = 0 ,$end = 2000)
	{
		$solr = &$this->_solr;
		$querySolr = $query;
		//$aParams = array('qt'=>'spellCheckCompRH', 'spellcheck.q'=>$querySolr, 'spellcheck'=>'true','spellcheck.collate'=>'true');
		$aParams = array('qt'=>'spellCheckCompRH', 'spellcheck'=>'true','spellcheck.collate'=>'true');
//  		return $solr->search( $querySolr,0, 2000, $aParams);
  		return $solr->search( $querySolr,$start, $end, $aParams);
	}
	public function findAndSort($query, $start=0, $limit=20, $sortField)
	{
		$solr = &$this->_solr;
		$querySolr = $query;
		//$aParams = array('qt'=>'spellCheckCompRH', 'spellcheck.q'=>$querySolr, 'spellcheck'=>'true','spellcheck.collate'=>'true');
		$s = $sortField;
		$aParams = array('sort'=>$s, 'q.op'=>'OR');
		//array('qt'=>'spellCheckCompRH', 'spellcheck'=>'true','spellcheck.collate'=>'true', 'sort'=>$s);
		//echo $querySolr;
		//die;
  		return $solr->searchByPost( $querySolr,$start, $limit, $aParams);
	}
	
	function clean_string_input($input)
	{
	    $interim = strip_tags($input);
	
	    if(get_magic_quotes_gpc())
	    {
	        $interim=stripslashes($interim);
	    }
	
	    // now check for pure ASCII input
	    // special characters that might appear here:
	    //   96: opening single quote (not strictly illegal, but substitute anyway)
	    //   145: opening single quote
	    //   146: closing single quote
	    //   147: opening double quote
	    //   148: closing double quote
	    //   133: ellipsis (...)
	    //   163: pound sign (this is safe, so no substitution required)
	    // these can be substituted for safe equivalents
	    $result = '';
	    $countInterim = strlen($interim);
	    for ($i=0; $i<$countInterim; $i++)
	    {
	        $char = $interim{$i};
	        $asciivalue = ord($char);
	        if ($asciivalue == 96)
	        {
	            $result .= '\\';
	        }
	        else if (($asciivalue > 31 && $asciivalue < 127) ||
	                 ($asciivalue == 163) || // pound sign
	                 ($asciivalue == 10) || // lf
	                 ($asciivalue == 13)) // cr
	        {
	            // it's already safe ASCII
	            $result .= $char;
	        }
	        else if ($asciivalue == 145) // opening single quote
	        {
	            $result .= '\\';
	        }
	        else if ($asciivalue == 146) // closing single quote
	        {
	            $result .= "'";
	        }
	        else if ($asciivalue == 147) // opening double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 148) // closing double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 133) // ellipsis
	        {
	            $result .= '...';
	        }
	    }
	
	    return $result;
	}
}

?>