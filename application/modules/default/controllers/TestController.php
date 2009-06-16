<?php
class TestController extends Zend_Controller_Action
{
	public function testAction()
	{
		$a = array();
		$a['menu'] = true;
		$a['a'] = 'action';
		$a['c'] = 'controller';
		$a['m'] = 'module';
		$a['p'] = array('param1'=>1, 'param2'=>2);
		
		$json = new Zend_Json();
		echo $json->encode($a);
		die;
	}
	
}
?>