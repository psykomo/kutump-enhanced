<?php
class Admin_Dms_ViewFolder
{
	private $_node;
	public $view;
	
	public function __construct($node='root')
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		$this->_node = $node;
		
		$this->viewFolderKu();
	}
	
	public function render()
	{
		return $this->view->render(strtolower('viewfolder.phtml'));
	}
	
	function viewFolderKu()
	{
		$time_start = microtime(true);
    	
    	$parentGuid = $this->_node;
        
        
        $columns = 4;
		
        //Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
        
        $tblFolder = new Kutu_Core_Orm_Table_Folder();
        $rowsetFolder = $tblFolder->fetchChildren($parentGuid);
        
//        $db = Zend_Db_Table::getDefaultAdapter();
//        if($parentGuid=='root')
//        	$query = "select * from KutuFolder where parentGuid=guid";
//        else
//        	$query = "select * from KutuFolder where parentGuid='$parentGuid' AND NOT parentGuid=guid";
//        $rs = $db->query($query);
//        $rowsetFolder = $rs->fetchAll(PDO::FETCH_OBJ);
		
		$num_rows = count($rowsetFolder);
		$rows = ceil($num_rows / $columns);
		
		if($num_rows < $columns)
			$columns = $num_rows;
		if($num_rows==0)
		{
			//echo 'No folder(s) found';
		}
		
		$kucrut = 0;
		$data = array();
		foreach ($rowsetFolder as $rowFolder)
		//for($kucrut=0;$kucrut<$num_rows;$kucrut++)
		{
			//$rowFolder = $rowsetFolder[$kucrut];
			
			$data[$kucrut][0] = $rowFolder->title;
			
			$data[$kucrut][1] = $rowFolder->description; 
			$data[$kucrut][2] = $rowFolder->guid; 
			
			$data[$kucrut][3] = ''; 
			$kucrut++;
			
		}
		
		$this->view->rows = $rows;
		$this->view->columns = $columns;
		$this->view->data = $data;
		$this->view->numberOfFolders = $num_rows;
		$this->view->node = $parentGuid;
		
		if($parentGuid!='root')
		{
			$rowCurrentNode = $tblFolder->find($parentGuid)->current();
			$this->view->currentNodeTitle = $rowCurrentNode->title;
		}
		else
		{
			$this->view->currentNodeTitle = 'ROOT';
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		//echo'<br>WAKTU EKSEKUSI: '. $time;
	}
}
?>