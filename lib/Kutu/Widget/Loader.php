<?php
class Kutu_Widget_Loader
{
	function loadWidget($widgetUrl, $widgetAuthActionUrl)
	{
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Kutu_Crypt_Password');
		
		$auth = Zend_Auth::getInstance();
		$password = '';
		$userName = '';
		if($auth->hasIdentity())
		{
			$crypt = new Kutu_Crypt_Password();
		
			$password = $crypt->decryptPassword($auth->getIdentity()->password);
			$userName = $auth->getIdentity()->username;
		}
		
		$client = new Zend_Http_Client($widgetUrl, array(
        'keepalive' => true
   		 ));
   		 
   		$client->setCookieJar();
        $client->setUri($widgetAuthActionUrl);
        $client->setParameterPost(array(
            'username' => $userName,
            'password' => $password
        ));
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
        
		$response = $client->request(Zend_Http_Client::POST);
        $client->setUri($widgetUrl);
        $response = $client->request(Zend_Http_Client::GET);
    	return $response->getBody();
	}
	
	function loadWidget2($widgetUrl, $test)
	{
		Zend_Loader::loadClass('Zend_Http_Client');
		$client = new Zend_Http_Client($widgetUrl, array(
        'keepalive' => true
   		 ));
   		 
   		 $_SESSION['testaja'] = 'testaja';
   		 
        $client->setUri($widgetUrl);
        /*$client->setParameterGet(array(
            'PHPSESSID' => Zend_Session::getId()
        ));*/
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
        
		$response = $client->request(Zend_Http_Client::GET);
        
    	return $response->getBody();
	}
	function loadWidget3($widgetUrl)
	{
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Zend_Session');
		$client = new Zend_Http_Client($widgetUrl, array(
        'keepalive' => true
   		 ));
   		 
   		//$_SESSION['testaja'] = 'testaja';
   		 
        $client->setUri($widgetUrl);
        $client->setParameterGet(array(
            'PHPSESSID' => Zend_Session::getId()
        ));
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
        
		$response = $client->request(Zend_Http_Client::GET);
        
    	return $response->getBody();
	}
	function loadWidget4($action, $controller, $module = null, $params = array())
	{
		$front         = Zend_Controller_Front::getInstance(); 
        $request       = clone $front->getRequest(); 
        $response      = clone $front->getResponse(); 
        $dispatcher    = clone $front->getDispatcher(); 
        $defaultModule = $front->getDefaultModule();
        
        $params0 = $request->getUserParams(); 
        foreach (array_keys($params0) as $key) { 
            $request->setParam($key, null); 
        } 
 
        /*$response->clearBody() 
             ->clearHeaders() 
             ->clearRawHeaders(); */
             
        $response->clearBody();
        $response->clearHeaders() 
                       ->clearRawHeaders();
             
        if (null === $module) { 
            $module = $defaultModule; 
        } 
        $request->setParams($params) 
             ->setModuleName($module) 
             ->setControllerName($controller) 
             ->setActionName($action) 
             ->setDispatched(true); 
 
        $dispatcher->dispatch($request, $response); 
 
        if (!$request->isDispatched() 
            || $response->isRedirect()) 
        { 
            // forwards and redirects render nothing 
            return ''; 
        } 
 
        return $response->getBody(); 
	}
	
	
	
}
?>