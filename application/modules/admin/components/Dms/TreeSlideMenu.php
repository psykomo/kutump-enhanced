<?php
class Admin_Dms_TreeSlideMenu
{
	public $view;
	
	public function __construct()
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
	}
	public function render()
	{
		//$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>