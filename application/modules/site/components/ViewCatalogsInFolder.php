<?php
class Dms_ViewCatalogsInFolder
{
	private $_node;
	public $view;
	
	//alternative sort = "regulationType desc, year desc";
	public function __construct($node='root',$offset=0, $limit=10, $sort="regulationType desc, year desc")
	{
		$time_start = microtime(true);
		
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$this->view->offset = $offset;
		
		$this->view->sort = $sort;
		
		$folderGuid = $node;
    	
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		if($folderGuid=='root')
		{	
			$a = array();
			$a['totalCount'] = 0;
			$a['folderGuid'] = $folderGuid;
			
		}
		else 
		{
			$a = array();
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
			$a['totalCount'] = count($rowset);	
    		$a['limit'] = $limit;
			$a['sort'] = $sort;
			$a['offset'] = $offset;
		}
			
		$this->view->aData = $a;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		
		//echo'<br>WAKTU EKSEKUSI: '. $time;
	}
	public function render()
	{
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>