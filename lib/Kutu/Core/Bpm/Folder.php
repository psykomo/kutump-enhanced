<?php
class Kutu_Core_Bpm_Folder
{
	public function delete($folderGuid)
	{
		$tbl = new Kutu_Core_Orm_Table_Folder();
		$rowset = $tbl->find($folderGuid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$row->delete();
		}
	}
}
?>