<?php
class Dms_ViewCatalogsInFolder
{
	private $_node;
	public $view;
	
	public function __construct($node)
	{
		$time_start = microtime(true);
		
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__).'/views');
		
    	$folderGuid = ($node)? $node : 'root';
    	
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper');
    	
    	Zend_Loader::loadClass('Kutu_Core_Orm_Table_Catalog');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		
		
		if($folderGuid=='root')
		{
			
			$time = time();
			$date = date("Y-m-d H:i:s", $time);
			$rowset = $tblCatalog->fetchAll("createdDate < '$date' and profileGuid = 'kutu_peraturan'", 'createdDate DESC', 12, 0);
			
			$a = array();
			//$a['totalCount'] = count($rowset);
			$a['totalCount'] = 0;
			$a['folderGuid'] = $folderGuid;
			$solrNumFound = 0;
		}
		else 
		{
			$a = array();
			//$a['totalCount'] = $tblCatalog->countCatalogsInFolder($folderGuid);
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
    		$solrAdapter = Kutu_Search::factory('solr',array('host'=>'localhost', 'port'=>'8983','homedir'=>'/solr/core0'));
    		
			$numi = count($rowset);
			
			
			
    		$sSolr = "id:(";
    		for($i=0;$i<$numi;$i++)
    		{
    			$row = $rowset[$i];
    			$sSolr .= $row->guid .' ';
    		}
    		$sSolr .= ')';
    		
    		if(!$numi)
    			$sSolr="id:(hfgjhfdfka)";
    		
    		$start = 0;
    		$a['totalCount'] = $numi;	
    		$limit = 20;
			$a['limit'] = $limit;
    		$solrResult = $solrAdapter->findAndSort($sSolr,$start,$limit, array('regulationOrder asc'));
    		$solrNumFound = $solrResult->response->numFound;
			//die('hi'.$solrNumFound);
    		
		}
		
		
		$ii=0;
		//for($i=$start;$i<($limit+$start);$i++)
		if($solrNumFound==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][$ii]['modifiedDate']= '';
		}
		else 
		{
			if($solrNumFound>$limit)
				$numRowset = $limit ; //$solrNumFound;
			else 
				$numRowset = $solrNumFound;
				
			for($ii=0;$ii<$numRowset;$ii++)
			{
				$row = $solrResult->response->docs[$ii];
				$a['catalogs'][$ii]['guid']= $row->id;
				$a['catalogs'][$ii]['title']= $row->title;
				$a['catalogs'][$ii]['subTitle']= $row->subTitle; 
				$a['catalogs'][$ii]['createdDate']= $row->createdDate;
				$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
			}
		}
			
		$this->view->aData = $a;
		
		//$sNav = $this->renderNavigation($a['totalCount'], $limit);
		
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		
		echo'<br>WAKTU EKSEKUSI: '. $time;
	}
	public function render()
	{
		return $this->view->render(strtolower(get_class($this)).'.phtml');
	}
	
	function renderNavigation($totalCount, $limit=15)
	{
		$numberOfPage = ceil($totalCount/$limit);
		for($i=0;$i<$numberOfPage;$i++)
		{
			echo $i*$limit. ' ';
		}
		//die();
		
	}
}
?>