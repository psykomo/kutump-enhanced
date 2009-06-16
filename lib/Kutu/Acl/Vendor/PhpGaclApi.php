<?php

/**
 * module Access Control List (ACL)
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

require_once('phpgacl/gacl_api.class.php');

class Kutu_Acl_Vendor_PhpGaclApi extends gacl_api 
{
	function getGroups($group_type='ARO') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		//Grab all groups from the database.
		$query  = 'SELECT id, parent_id, value FROM '. $table .' ORDER BY parent_id, value';
		$rs = $this->db->Execute($query);

		if (!is_object($rs)) {
			$this->debug_db('sort_groups');
			return false;
		}

		/*
		 * Save groups in an array sorted by parent. Should be make it easier for later on.
		 */
		$sorted_groups = array();

		$i = 0;
		while ($row = $rs->FetchRow()) {
			$id = &$row[0];
			$parent_id = &$row[1];
			$value = &$row[2];

			//$sorted_groups[$parent_id][$id] = $value;
			$sorted_groups[$i]['id'] = $id;
			$sorted_groups[$i]['value'] = $value;
			$i++;
		}

		return $sorted_groups;
	}
}