<?php
class Admin_Dms_Relation_SejarahViewer
{
	public $view;
	public $catalogGuid;
	
	public function __construct($catalogGuid)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->catalogGuid = $catalogGuid;
		
		
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->aData = $bpm->getSejarah($catalogGuid);
		//print_r($a2);
		//die();
		$this->view->catalogGuid = $catalogGuid;
		
		
	}
	public function render()
	{
		$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>