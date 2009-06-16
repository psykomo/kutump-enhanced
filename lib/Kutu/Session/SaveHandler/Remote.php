<?php

/**
 * module Remote Session for Application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Session_SaveHandler_Remote implements Zend_Session_SaveHandler_Interface 
{
	/**
	 * Open Session - retrieve resources
	 *
	 * @param string $save_path
	 * @param string $name
	 */
	public function open($save_path, $name)
	{
		return true;
	}
	
	/**
	 * Close Session - free resources
	 *
	 */
	public function close()
	{
		return true;
	}
	
	/**
	 * Read session data
	 *
	 * @param string $id
	 */
	public function read($id)
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$url = $config->session->config->remote->savehandler->url;
		
		$client = new Zend_Http_Client();
        $client->setUri($url."/read");
        $client->setParameterPost(array(
            'key' => $id
        ));
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
		
		try 
		{
			$response = $client->request(Zend_Http_Client::POST);
			
			if($response->isError())
			{
				throw new Zend_Exception('SESSION Server reachable, but there was an error');
			}
			
			return $sResponse = $response->getBody();
			
		} catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Exception('Failed contacting SESSION Server');
        }
	}
	
	/**
	 * Write Session - commit data to resource
	 *
	 * @param string $id
	 * @param mixed $data
	 */
	public function write($id, $data)
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$url = $config->session->config->remote->savehandler->url;
		
		$client = new Zend_Http_Client();
        $client->setUri($url."/write");
        $client->setParameterPost(array(
            'key' => $id,
            'value' => $data
        ));
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
		
		try 
		{
			$response = $client->request(Zend_Http_Client::POST);
			
			if($response->isError())
			{
				throw new Zend_Exception('SESSION Server reachable, but there was an error');
			}
			
			//return $sResponse = $response->getBody();
			return true;
			
		} catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Exception('Failed contacting SESSION Server');
        }
	}
	
	/**
	 * Destroy Session - remove data from resource for
	 * given session id
	 *
	 * @param string $id
	 */
	public function destroy($id)
	{
		Zend_Loader::loadClass('Zend_Http_Client');
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$url = $config->session->config->remote->savehandler->url;
		
		$client = new Zend_Http_Client();
        $client->setUri($url."/destroy");
        $client->setParameterPost(array(
            'key' => $id
        ));
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
		
		try 
		{
			$response = $client->request(Zend_Http_Client::POST);
			
			if($response->isError())
			{
				throw new Zend_Exception('SESSION Server reachable, but there was an error');
			}
			
			//return $sResponse = $response->getBody();
			return true;
			
		} catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Exception('Failed contacting SESSION Server');
        }
	}
	
	/**
	 * Garbage Collection - remove old session data older
	 * than $maxlifetime (in seconds)
	 *
	 * @param int $maxlifetime
	 */
	public function gc($maxlifetime)
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$url = $config->session->config->remote->savehandler->url;
		
		$client = new Zend_Http_Client();
        $client->setUri($url."/gc");
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$client->setHeaders("User-Agent: $userAgent");
		
		try 
		{
			$response = $client->request(Zend_Http_Client::POST);
			
			if($response->isError())
			{
				throw new Zend_Exception('SESSION Server reachable, but there was an error');
			}
			
			//return $sResponse = $response->getBody();
			return true;
			
		} catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Exception('Failed contacting SESSION Server');
        }
	}
}

?>