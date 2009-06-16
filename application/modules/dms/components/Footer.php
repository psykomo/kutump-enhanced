<?php
class Dms_Footer
{
	public $view;
	
	public function __construct()
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__).'/views');
	}
	public function render()
	{
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>