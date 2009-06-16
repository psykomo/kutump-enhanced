<?php
class Dms_Terbaru
{
	public $view;
	
	public function __construct($profile='kutu_peraturan', $start=0)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__).'/views');
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchAll("profileGuid='$profile'",'createdDate DESC',5,$start);
		
		$content = 0;
		$data = array();
		
		foreach ($rowset as $row)
		{
			$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute(); 
			$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
			$data[$content][0] = $rowCatalogAttribute->value;
			$data[$content][1] = strftime("%H:%M",strtotime($row->createdDate));
			$data[$content][2] = $row->guid;
			$content++;
		}
		
		$num_rows = count($rowset);
		
		$this->view->numberOfRows = $num_rows;
		$this->view->data = $data;
	}
	public function render()
	{
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>