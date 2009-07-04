<?php
define('KUTU_ROOT_DIR',dirname(__FILE__));
require_once(KUTU_ROOT_DIR.'/lib/Kutu/Core/Util.php');
$KUTUUTIL = new Kutu_Core_Util();
define('KUTU_ROOT_URL',$KUTUUTIL->getRootUrl(KUTU_ROOT_DIR));
//print_r($_GET);

/**
 * manage config, memory usage for application
 * 
 * @author HAP
 * @package Kutu
 * 
 */

class Kutu_BaseInit
{
	public function init()
	{
		//define('KUTU_ROOT_DIR',dirname(__FILE__));
		date_default_timezone_set('Asia/Jakarta');
		error_reporting(E_ALL|E_STRICT); 
		
		//set_include_path('.' . PATH_SEPARATOR . KUTU_ROOT_DIR.'/mix_lib' . PATH_SEPARATOR . get_include_path()); 
		$paths = array(
	    realpath(dirname(__FILE__) . '/lib'),
	    '.'
		);
		set_include_path(implode(PATH_SEPARATOR, $paths));		
		
		include KUTU_ROOT_DIR.'/lib/jcart/jcart.php';
		
		//include "Zend/Loader.php"; 		
		//Zend_Loader::registerAutoload();
		require_once 'Zend/Loader/Autoloader.php';
		$loader = Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
		
		require_once(KUTU_ROOT_DIR.'/lib/phpgacl/gacl.class.php');
		require_once(KUTU_ROOT_DIR.'/lib/phpgacl/gacl_api.class.php');
		
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/config/config.ini', 'general'); 
		$registry = Zend_Registry::getInstance(); 
		$registry->set('config', $config); 
		$registry->set('files', $_FILES);
		
		$db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray()); 
		Zend_Db_Table_Abstract::setDefaultAdapter($db); 
		
		
		$frontendOptions = array(
		'lifetime' => 7200, // cache lifetime of 2 hours
	    'automatic_serialization' => true
	    );
	
		$backendOptions  = array(
		    //'cache_dir'                => KUTU_ROOT_DIR.'/data/cache'
		    );
		
		$cacheDbTable = Zend_Cache::factory('Core',
		                             'Apc',
		                             $frontendOptions,
		                             $backendOptions);
	
	
		// Next, set the cache to be used with all table objects
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cacheDbTable);
		
//		echo "apc: ". ini_get("apc.shm_segments");
//		//ini_set("apc.shm_size", '90');
//		echo "apc: ". ini_get("apc.shm_size");
//		print_r(apc_sma_info());
		
		//phpinfo();
		
		$front = Zend_Controller_Front::getInstance();
		$front->throwExceptions(true);
		$front->addModuleDirectory(KUTU_ROOT_DIR.'/application/modules');

		Zend_Loader::loadClass('Zend_Layout');
		//$layout = new Zend_Layout(KUTU_ROOT_DIR.'/app/servers/identity/modules/default/layouts'); 
		//$layout = new Zend_Layout(null, true);
		Zend_Layout::startMvc();

		$router = $front->getRouter();
		
		$route = new Zend_Controller_Router_Route_Static(
		    'identity/login',
		    array('module'=>'identity','controller' => 'index', 'action' => 'login')
		);
		$router->addRoute('login', $route);
		
		$route = new Zend_Controller_Router_Route_Static(
		    'identity/logout',
		    array('module'=>'identity','controller' => 'index', 'action' => 'logout')
		);
		$router->addRoute('logout', $route);
		
		/*$route = new Zend_Controller_Router_Route(
		    'dms/:node',
		    array('module'=>'dms','controller' => 'index', 'action' => 'browse')
		);
		$router->addRoute('dms1', $route);
		
		$route = new Zend_Controller_Router_Route(
		    'dms/catalog/:guid/*',
		    array('module'=>'dms','controller' => 'index', 'action' => 'browse')
		);
		$router->addRoute('dms', $route);*/
		$route = new Zend_Controller_Router_Route_Static(
		    'dms',
		    array('module'=>'site','controller' => 'dms', 'action' => 'index')
		);
		$router->addRoute('dms-index', $route);
		$route = new Zend_Controller_Router_Route(
		    'dms/:node/*',
		    array('module'=>'site','controller' => 'dms', 'action' => 'index')
		);
		$router->addRoute('dms1', $route);
		
		$route = new Zend_Controller_Router_Route(
		    'dms/catalog/:guid/*',
		    array('module'=>'site','controller' => 'dms', 'action' => 'details')
		);
		$router->addRoute('dms2', $route);
		
		
		$route = new Zend_Controller_Router_Route(
		    'identity/i/:action/*',
		    array('module'=>'identity','controller' => 'index')
		);
		$router->addRoute('test', $route);
		
		$route = new Zend_Controller_Router_Route(
		    'pages/*',
		    array('module'=>$config->route->pages->module,'controller' => $config->route->pages->controller, 'action' => $config->route->pages->action)
		);
		$router->addRoute('pages', $route);
		
		$route = new Zend_Controller_Router_Route(
		    'download/:guid/*',
		    array('module'=>'site','controller' => 'download', 'action' => 'index')
		);
		$router->addRoute('downloadfile', $route);
		
		
		
		//print_r ($front->getParam('action'));
		//die();
		
		//$front->setParam('noViewRenderer', true);
		$front->dispatch();
		
	}
	
	/**
	 * Raise the memory limit when it is lower than the needed value
	 *
	 * @param string $setLimit Example: 16M
	 * 
	 */
	
	function ext_RaiseMemoryLimit( $setLimit ) {
		$memLimit = @ini_get('memory_limit');
		
		if( stristr( $memLimit, 'k') ) {
			$memLimit = str_replace( 'k', '', str_replace( 'K', '', $memLimit )) * 1024;
		}
		elseif( stristr( $memLimit, 'm') ) {
			$memLimit = str_replace( 'm', '', str_replace( 'M', '', $memLimit )) * 1024 * 1024;
		}
		
		if( stristr( $setLimit, 'k') ) {
			$setLimitB = str_replace( 'k', '', str_replace( 'K', '', $setLimit )) * 1024;
		}
		elseif( stristr( $setLimit, 'm') ) {
			$setLimitB = str_replace( 'm', '', str_replace( 'M', '', $setLimit )) * 1024 * 1024;
		}
		
		if( $memLimit < $setLimitB ) {
			@ini_set('memory_limit', $setLimit );
		}	
	}
}


