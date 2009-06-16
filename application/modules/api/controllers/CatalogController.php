<?php
class Api_CatalogController extends Zend_Controller_Action
{
	public function getcatalogsinfolderAction()
	{
		//sleep(2);
		
		
		$this->_helper->layout()->disableLayout();
		//params: $folderGuid,$start,$limit,orderBy
		
		$r = $this->getRequest();
		$folderGuid = $r->getParam('folderGuid');
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$sort = ($r->getParam('sort'))? $r->getParam('sort') : 'regulationType desc, year desc';
		//die($sort);
		
		$a = array();
		//$a['totalCount'] = $tblCatalog->countCatalogsInFolder($folderGuid);
		$a['folderGuid'] = $folderGuid;

		$db = Zend_Db_Table::getDefaultAdapter()->query
		("SELECT catalogGuid as guid from KutuCatalogFolder where folderGuid='$folderGuid'");
		
		
		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
		
		
		$solrAdapter = Kutu_Search::manager();
		
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
			
		$solrResult = $solrAdapter->findAndSort($sSolr,$start,$limit, array('sort'=>$sort));
		//$solrResult = $solrAdapter->findAndSort($sSolr,$start,$limit, array('createdDate desc'));
		$solrNumFound = count($solrResult->response->docs);//$solrResult->response->numFound;
		
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
				//die('hi:'.count($solrResult->response->docs));
				$row = $solrResult->response->docs[$ii];
				if(!empty($row))
				{
					$a['catalogs'][$ii]['guid']= $row->id;
					$a['catalogs'][$ii]['title']= $row->title;
					$a['catalogs'][$ii]['subTitle']= $row->subTitle; 
					$a['catalogs'][$ii]['createdDate']= $row->createdDate;
					$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
				}
			}
		}
		
		echo Zend_Json::encode($a);
	}
}
?>