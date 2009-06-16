<?php
class Api_UserController extends Zend_Controller_Action
{
	public function isuserexistAction()
	{
		
	}
	
	/* this function shall be used ONLY for form's validation*/
	
	public function isusernameavailableAction()
	{
		$r = $this->getRequest();
		$username = $r->getParam('username');
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowset = $tblUser->fetchAll('username="'.$username.'"');
		if(count($rowset)>0)
			echo "0";
		else
			echo "1";
		die();
	}
	public function getalluserAction()
	{
		$this->_helper->layout()->disableLayout();
		//params: $folderGuid,$start,$limit,orderBy
		
		$r = $this->getRequest();
		$q = ($r->getParam('q'))? base64_decode($r->getParam('q')) : "1=1";
		
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$a = array();
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		//echo $q;die();
		$rowset = $tblUser->fetchAll($q, 'firstname ASC', $limit, $start);
		
		if(count($rowset)==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][0]['modifiedDate']= '';
		}
		else 
		{
			$ii=0;
			foreach ($rowset as $row) 
			{
				$a['catalogs'][$ii]['guid']= $row->guid;
				$a['catalogs'][$ii]['title']= $row->firstname.' '.$row->lastname;
				$a['catalogs'][$ii]['subTitle']= $row->username; 
				$a['catalogs'][$ii]['createdDate']= $row->createdDate;
				$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
				$ii++;
			}
		}
		
		echo Zend_Json::encode($a);
		die();
	}
	public function countuserbyqueryAction()
	{
		$mainQuery = "SELECT count(*) as count from KutuUser where ";
		
		$r = $this->getRequest();
		$q = ($r->getParam('q'))? $r->getParam('q') : '';
		$q = base64_decode($q);
		
		$finalQuery = $mainQuery.$q;
		$db = Zend_Db_Table::getDefaultAdapter()->query($finalQuery);
		
		$row = $db->fetchAll(Zend_Db::FETCH_OBJ);
		echo $row[0]->count;
		die();
	}
}
?>