<?php
class CatalogPager
{
	private $_node;
	public $view;
	
	public function __construct($node)
	{
		$time_start = microtime(true);
		
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
    	$folderGuid = ($node)? $node : 'root';
    	
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		if($folderGuid=='root')
		{	
			$a = array();
			$a['totalCount'] = 0;
			$a['folderGuid'] = $folderGuid;
			$limit = 20;
			$a['limit'] = $limit;
		}
		else 
		{
			$a = array();
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
			$a['totalCount'] = count($rowset);	
    		$limit = 10;
			$a['limit'] = $limit;
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