<?php
class MasterStatus
{
	static function getPublishingStatus()
	{
		$a = array();
		$a[0] = 'draft';
		$a[1] = 'approved';
		$a[2] = 'NA';
		$a[99] = 'published';
		
		return $a;
	}
}
?>