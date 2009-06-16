<?php
class Test_IndexController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		
    }
    public function passarrayAction()
    {
		//try this:
		// http://localhost/kutump-enhanced/test/index/passarray/param1/1/param2/2/param1/1a
		
		$r = $this->getRequest();
		print_r($r->getParams());
		die();
	}
	public function testcookieAction()
	{
		Zend_Session::start();
		
		setcookie('PHPSESSID','test',0,'/','localhost');
		setcookie('aku','test2',time()+3600,'/','.test.com');
		setcookie('PHPSESSID','test',0,'/','127.0.0.1');
		setcookie('PHPSESSID','test',0,'/','10.0.1.194');
		die();
	}
	public function setcookieremoteAction()
	{
		$url = 'http://10.0.1.194/kutump-enhanced/test/index/setsession';
		$ret = '<script language="javascript" type="text/javascript" src="' . $url . '?' .
		                    'PHPSESSID=' . 'iniphpsessid';
		echo $ret;
		die();
	}
	public function setsessionAction()
	{
		header('Content-Type: text/javascript; charset=' . 'iso-8859-1');
		
		$r - $this->getRequest();
		$sessid = $r->getParam('PHPSESSID');
		Zend_Session::setId($sessid);
		Zend_Session::start();
		die();
	}
}
?>