<?php
class Admin_ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
    }
	public function restrictedAction()
	{
		$this->_helper->layout()->setLayout('layout-fb2');
		
	}
}
?>