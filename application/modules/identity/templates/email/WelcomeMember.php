<?php
class WelcomeMember
{
	public $view;
	
	public function __construct($node)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
	}
	public function render()
	{
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>