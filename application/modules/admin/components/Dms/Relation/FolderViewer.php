<?php
class Admin_Dms_Relation_FolderViewer
{
	public $view;
	public $catalogGuid;
	
	public function __construct($catalogGuid)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->catalogGuid = $catalogGuid;
		$this->view->catalogGuid = $catalogGuid;
		
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		$bpm = new Kutu_Core_Bpm_Catalog();
		$this->view->rowsetFolder = $bpm->getFolders($catalogGuid);
	}
	public function render()
	{
		$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>