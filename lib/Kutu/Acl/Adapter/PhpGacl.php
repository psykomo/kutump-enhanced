<?php
/**
 * module Access Control List (ACL)
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Acl_Adapter_PhpGacl
{
	private $_aclEngine;
	private $_acl;
	
	function __construct()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
//		switch ($config->acl->config->db->adapter)
//		{
//			case subs
//		}
		
		$host = $config->acl->config->db->param->host;
		$username = $config->acl->config->db->param->username;
		$password = $config->acl->config->db->param->password;
		$dbname = $config->acl->config->db->param->dbname;
		
		$gacl_options['debug'] = '';
		$gacl_options['db_type'] = 'mysql' ;
		$gacl_options['db_host'] = $host;
		$gacl_options ['db_user'] = $username;
		$gacl_options['db_password'] = $password;
		$gacl_options['db_name'] = $dbname;
		$gacl_options['db_table_prefix'] = 'gacl_'; 
		$gacl_options['caching'] = ''; 
		$gacl_options['force_cache_expire'] = 1; 
		$gacl_options['cache_dir'] = "/tmp/phpgacl_cache"; 
		$gacl_options['cache_expire_time'] = 600; 
		$gacl_options['items_per_page'] = 100; 
		$gacl_options['max_select_box_items'] = 100; 
		$gacl_options['max_search_return_items'] = 200; 
		$gacl_options['smarty_dir'] = "smarty/libs"; 
		$gacl_options['smarty_template_dir'] = "templates"; 
		$gacl_options['smarty_compile_dir'] = "templates_c";
		
		$this->_aclEngine = new Kutu_Acl_Vendor_PhpGaclApi($gacl_options);
		$this->_acl = new Kutu_Acl_Vendor_PhpGacl($gacl_options);
	}
	function getUsers()
	{
		$aResult = $this->_aclEngine->get_objects('user', $return_hidden = 1, $object_type = 'aro');
		return $aResult['user'];
	}
	
	function addUser($username, $groupValue=NULL)
	{
		$result = $this->_aclEngine->add_object('user', $username, $username, 0, 0, 'aro');
		
		//must also assign group: everyone
		$aroGroupId = $this->_aclEngine->get_group_id('everyone', 'everyone', 'aro');
		$this->_aclEngine->add_group_object($aroGroupId, 'user', $username, 'aro');
		
		if(!empty($groupValue))
		{
			//$aroGroupId = null;
			$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
			$this->_aclEngine->add_group_object($aroGroupId, 'user', $username, 'aro');
		}
	}
	function deleteUser($username)
	{
		$id = $this->_aclEngine->get_object_id('user', $username, 'aro');
		return $this->_aclEngine->del_object($id, 'aro', TRUE);
	}
	function getUserGroupIds($username)
	{
		$id = $this->_aclEngine->get_object_id('user', $username, 'aro');
		
		//return value : Array ( [0] => 10 [1] => 11 )
		$aReturn = $this->_aclEngine->get_object_groups($id, $object_type = 'ARO');
		
		for ($i=0; $i < count($aReturn); $i++)
		{
			$aTmp = $this->_aclEngine->get_group_data($aReturn[$i], 'ARO');
			$aReturn[$i] = $aTmp[2];
		}
		
		//return value : Array ( [0] => member [1] => everyone )
		return $aReturn;
		
	}
	function addUserToGroup($username, $groupValue)
	{
		$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
		return $this->_aclEngine->add_group_object($aroGroupId, 'user', $username, 'aro');
	}
	
	/**
	 * removeUserFromGroup()
	 *
	 * Removes an Object from a group.
	 *
	 * @return bool Returns TRUE if successful, FALSE otherwise
	 *
	 * @param string username
	 * @param string groupValue
	 */
	function removeUserFromGroup($username, $groupValue)
	{
		//must also assign group: everyone
		$aroGroupId = $this->_aclEngine->get_group_id('everyone', 'everyone', 'aro');
		$this->_aclEngine->del_group_object($aroGroupId, 'user', $username, $group_type='ARO');
		
		if(!empty($groupValue))
		{
			//$aroGroupId = null;
			$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
			return $this->_aclEngine->del_group_object($aroGroupId, 'user', $username, $group_type='ARO');
		}
//		$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
//		return $this->_aclEngine->del_group_object($aroGroupId, 'user', $username, $group_type='ARO');
	}
	function getGroups()
	{
		return $this->_aclEngine->getGroups($group_type='ARO');
	}
	
	function allow($username=NULL, $groupValue=NULL, $action, $section='content', $itemGuid)
	{
		// insert the axo object first
		// if the object is already in DB, this command will not add more row, instead, it will return false
		$this->_aclEngine->add_object($section, $itemGuid, $itemGuid, 0, 0, 'axo');
		
		//check if ACL row for content>itemGuid exist
		if(empty($username))
		{
			$aroValue = false;
			$aroSectionValue = false;
		}
		else
		{
			$aroValue = $username;
			$aroSectionValue = 'user';
		}
			
		if(empty($groupValue))
			$aroGroupValue = false;
		else 
		{
			$aroGroupValue = $groupValue;
			$aroValue = false;
			$aroSectionValue = false;
			$username = null;
			
		}
		if(empty($username) && empty($groupValue))
			return false;
		
		$aAclId = $this->searchAcl('action', false, $aroSectionValue, $aroValue, $aroGroupValue, $section, $itemGuid);
		
		if(count($aAclId)>0)
		{
			$aclId = $aAclId[0];
			$aclRow = $this->_aclEngine->get_acl($aclId);
			$aAco = $aclRow['aco'];
			$aAco['action'][count($aAco['action'])] = $action;
			
			$aAro = $aclRow['aro'];
			if(!empty($username))
			{
				if(isset($aAro['user']))
					$aAro['user'][count($aAro['user'])] = $username;
				else 
					$aAro['user'][0] = $username;
			}
			
			$aAroGroup = $aclRow['aro_groups'];
			if(!empty($groupValue))
			{
				$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
				$aAroGroup[count($aAroGroup)] = $aroGroupId;
			}
			$aAxo = $aclRow['axo'];
			
			return $this->_aclEngine->edit_acl($aclId, $aAco, $aAro, $aAroGroup, $aAxo, null, 1, 1);
		}
		else 
		{
			//prepare 
			$aAxo[$section][0] = $itemGuid;
			$aAco['action'][0] = $action; //'read', 'write', etc...
			
			if(empty($username))
			{
				$aAro = array();
				$aroSectionValue = null;
			}
			else
			{
				$aAro['user'][0] = $username;
				$aroSectionValue = 'user';
			}
				
			if(empty($groupValue))
				$aAroGroup = array();
			else 
			{
				$aAroGroup[0] = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
			}
			
			return $this->_aclEngine->add_acl($aAco, $aAro, $aAroGroup, $aAxo);
		}
	}
	function removeAllow($username=NULL, $groupValue=NULL, $action, $section='content', $itemGuid)
	{
		//check if ACL row for content>itemGuid exist
		if(empty($username))
		{
			$aroValue = false;
			$aroSectionValue = false;
		}
		else
		{
			$aroValue = $username;
			$aroSectionValue = 'user';
		}
			
		if(empty($groupValue))
			$aroGroupValue = false;
		else 
		{
			$aroGroupValue = $groupValue;
		}
		
		$aAclId = $this->searchAcl('action', false, $aroSectionValue, $aroValue, $aroGroupValue, $section, $itemGuid);
		
		if(count($aAclId)>0)
		{
			$aclId = $aAclId[0];
			$aclRow = $this->_aclEngine->get_acl($aclId);
			$aAco = $aclRow['aco'];
			//$aAco['action'][count($aAco['action'])] = $action;
			
			$tmpI=0;
			$aTmpAco['action'] = array();
			
			for($i=0;$i<count($aAco['action']);$i++)
			{
				if($aAco['action'][$i] != $action)
				{
					$aTmpAco['action'][$tmpI] = $aAco['action'][$i];
					$tmpI = $tmpI + 1;
				}
			}
			if(count($aTmpAco['action']) > 0)
				$aAco = $aTmpAco;
			else 
			{
				//berarti acl harus dihapus karena action kosong
				return $this->deleteAcl($aclId);
			}
			
			$aAro = $aclRow['aro'];
			if(!empty($username))
			{
				if(isset($aAro['user']))
					$aAro['user'][count($aAro['user'])] = $username;
				else 
					$aAro['user'][0] = $username;
			}
			
			$aAroGroup = $aclRow['aro_groups'];
			if(!empty($groupValue))
			{
				$aroGroupId = $this->_aclEngine->get_group_id($groupValue, $groupValue, 'aro');
				$aAroGroup[count($aAroGroup)] = $aroGroupId;
			}
			$aAxo = $aclRow['axo'];
			
			return $this->_aclEngine->edit_acl($aclId, $aAco, $aAro, $aAroGroup, $aAxo, null, 1, 1);
		}
		return false;
	}
	
	/**
	 * this function will return action array: a[0]='read', a[1]='delete'
	 *
	 * @param unknown_type $username
	 * @param unknown_type $groupValue
	 * @param unknown_type $itemGuid
	 */
	function getPermissionsOnContent($username=NULL, $groupValue=NULL, $itemGuid)
	{
		if(empty($username))
		{
			$aroValue = false;
			$aroSectionValue = false;
		}
		else
		{
			$aroValue = $username;
			$aroSectionValue = 'user';
		}
			
		if(empty($groupValue))
			$aroGroupValue = false;
		else 
		{
			$aroGroupValue = $groupValue;
		}
		
		$aAclId = $this->searchAcl('action', false, $aroSectionValue, $aroValue, $aroGroupValue, 'content', $itemGuid);
		
		if(count($aAclId)>0)
		{
			$aclId = $aAclId[0];
			$aclRow = $this->_aclEngine->get_acl($aclId);
			$aAco = $aclRow['aco'];
			return $aAco['action'];
		}
		else 
		{
			return array();
		}
	}
	
	protected function _traverseFolderPermission($username, $itemGuid, $action)
	{
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
		$rowset = $tblFolder->find($itemGuid);
		if(count($rowset)>0)
		{
			$row = $rowset->current();
			//echo $row->title . '/';
			$aAclId = $this->searchAcl('action', false, false, false, false, 'content', $itemGuid);
			if(count($aAclId)>0)
			{
				//echo 'here';
				return $this->checkAcl('action', $action, 'user', $username, 'content', $itemGuid);
			}
			else 
			{
				if($row->guid != $row->parentGuid)
					return $this->_traverseFolderPermission($username, $row->parentGuid, $action);
				
			}
		}
		return false;
	}
	function isAllowed($username, $itemGuid, $action, $section='content')
	{
		if($this->checkAcl("site",'all','user', $username, false,false))
				return true;
		
		if($section == 'content')
		{
			if($this->checkAcl("dms",'all','user', $username, false,false))
				return true;
				
				
			switch ($action)
			{
				case 'create':
					if($this->checkAcl("dms",'createCatalog','user', $username, false,false))
						return true;
				case 'read':
					if($this->checkAcl("dms",'readCatalog','user', $username, false,false) || $acl->checkAcl("dms",'updateCatalog','user', $username, false,false))
						return true;
				case 'update':
					if($this->checkAcl("dms",'updateCatalog','user', $username, false,false))
						return true;
				case 'delete':
					if($this->checkAcl("dms",'deleteCatalog','user', $username, false,false))
						return true;
			}
			
				
			if ($this->checkAcl('action', $action, 'user', $username, 'admin', 'content'))
				return true;
			
			//check if itemGuid exist in table KutuCatalog
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$rowset = $tblCatalog->find($itemGuid);
			if(count($rowset) > 0)
			{
				$row = $rowset->current();
				if($row->profileGuid != 'kutu_folder')
				{
					//if user was the creator of the item, allow everything
					if($row->createdBy == $username)
						return true;
						
					$aAclId = $this->searchAcl('action', false, false, false, false, 'content', $itemGuid);
					if(count($aAclId)>0)
					{
						return $this->checkAcl('action', $action, 'user', $username, 'content', $itemGuid);
					}
					else 
					{
						//check permission of the folder which this catalog belongs to
						$rowset1 = $row->findDependentRowset('Kutu_Core_Orm_Table_CatalogFolder');
						$flagFolderPermission = false;
						foreach ($rowset1 as $row1)
						{
							if ($this->_traverseFolderPermission($username, $row1->folderGuid, $action))
							{
								$flagFolderPermission = true;
							}
						}
						return $flagFolderPermission;
					}
				}
			}
			
			//check if itemGuid exist in table KutuFolder
			$tblFolder = new Kutu_Core_Orm_Table_Folder();
			$rowset = $tblFolder->find($itemGuid);
			if(count($rowset) > 0)
			{
				return $this->_traverseFolderPermission($username, $itemGuid, $action);
			}
		}
		
		//check at section
		$aAclId = $this->searchAcl('action', false, false, false, false, $section, $itemGuid);
		if(count($aAclId)>0)
		{
			return $this->checkAcl('action', $action, 'user', $username, $section, $itemGuid);
		}
		
		//check at feature section
		/*$aAclId = $this->searchAcl('action', false, false, false, false, 'feature', $itemGuid);
		if(count($aAclId)>0)
		{
			return $this->checkAcl('action', $action, 'user', $username, 'feature', $itemGuid);
		}*/
		
		return false;
	}
	function checkAcl($acoSectionValue, $acoValue, $aroSectionValue, $aroValue, $axoSectionValue, $axoValue)
	{
		return $this->_acl->acl_check($acoSectionValue, $acoValue, $aroSectionValue, $aroValue, $axoSectionValue, $axoValue); //, $root_aro_group=NULL, $root_axo_group=NULL)
	}
	
	function searchAcl($aco_section_value=NULL, $aco_value=NULL, $aro_section_value=NULL, $aro_value=NULL, $aro_group_name=NULL, $axo_section_value=NULL, $axo_value=NULL, $axo_group_name=NULL, $return_value=NULL)
	{
		return $this->_aclEngine->search_acl($aco_section_value, $aco_value, $aro_section_value, $aro_value, $aro_group_name, $axo_section_value, $axo_value, $axo_group_name, $return_value);
	}
	
	function deleteAcl($acl_id)
	{
		return $this->_aclEngine->del_acl($acl_id);
	}
	
	
	function enableAcl($intBool)
	{
		
	}
}
?>