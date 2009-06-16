<?php
class MyAccount_Menu
{
	public $view;
	
	public function __construct($username='')
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		
	}
	public function render()
	{
		$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower($aName[count($aName)-1])));
	}
}
?>