<?php
class IndexController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		
    }
    public function indexAction()
    {
 		//$sso = new Kutu_Sso_Session();
		//$sso->start();
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
		//$this->_helper->layout->disableLayout();
		
		$sReturn = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$sReturn = urlencode($sReturn);
		$this->view->returnTo = $sReturn;
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		$this->view->loginUrl = $config->identity->login->url;
		$this->view->logoutUrl = $config->identity->logout->url;
		
		$auth =  Zend_Auth::getInstance();
		
		if($auth->hasIdentity())
		{
			$username = $auth->getIdentity()->username;
			echo $username;
		}
		else
		{
			echo 'tidak ada';
		}
		
    }
	public function testAction()
	{
		$time_start = microtime(true);

		$params = array(
		    'host'     => '127.0.0.1',
		    'username' => 'root',
		    'password' => 'root',
		    'dbname'   => 'langithp'
		);

		$db = Zend_Db::factory('PDO_MYSQL', $params);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

		require_once '/Users/n/Documents/Work/Zend/kutump/test/CatalogAttribute.php';

		$tbl = New CatalogAttribute();

		$rows = $tbl->fetchAll();

		$num=count($rows);


		echo "<b><center>Database Output</center></b><br><br>";

		$i=0;
		for($i=0;$i<$num;$i++)
		{
			//$tmpGuid = mysql_result($result,$i,"guid");
			$row = $rows->current();
			$tmpGuid = $row->title;
			echo '<br>'.$tmpGuid;
			echo '<br>'.$i;
			$rows->next();
			//$i++;
		}

		$dbh = null;


		echo '<br>Total: '. $i;

		$time_end = microtime(true);
		$time = $time_end - $time_start;

		echo'<br>WAKTU EKSEKUSI: '. $time;
		//die('hiho');
	}
	
	function signupAction()
	{
		$this->_helper->layout()->setLayout('layout-empty');
	}
	
}
?>