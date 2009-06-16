<?php
class Admin_Dms_Catalog_DetailsViewer
{
	public $view;
	public $catalogGuid;
	public $folderGuid;
	
	public function __construct($catalogGuid, $folderGuid)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->catalogGuid = $catalogGuid;
		$this->folderGuid = $folderGuid;
		
		$this->view();
	}
	public function view()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');

		$catalogGuid =($this->catalogGuid)? $this->catalogGuid : '';
		$node =($this->folderGuid)? $this->folderGuid : 'root';
		
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Catalog');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		if(!empty($catalogGuid))
		{
			$rowCatalog = $tblCatalog->find($catalogGuid)->current();
			$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			Zend_Loader::loadClass('Kutu_Core_Orm_Table_ProfileAttribute');
			$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
			$profileGuid = $rowCatalog->profileGuid;
			$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
			$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
			
			$aAttribute = array();
			$i = 0;
			Zend_Loader::loadClass('Kutu_Core_Orm_Table_Attribute');
			$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
			foreach ($rowsetProfileAttribute as $rowProfileAttribute)
			{
				if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
				{
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);
					
					$rowsetAttribute = $tblAttribute->find($rowCatalogAttribute->attributeGuid);
					if(count($rowsetAttribute))
					{
						$rowAttribute = $rowsetAttribute->current();
						$aAttribute[$i]['name'] =  $rowAttribute->name;
					}
					else 
					{
						$aAttribute[$i]['name'] =  '';
					}
					$aAttribute[$i]['value'] = $rowCatalogAttribute->value;
					
				}
				else 
				{
					
				}
				$i++;
			}
		}
		$this->view->aAttribute = $aAttribute;
		$this->view->rowCatalog = $rowCatalog;
		$this->view->rowsetCatalogAttribute = $rowsetCatalogAttribute;
		$this->view->node = $node;
		$this->view->catalogGuid = $catalogGuid;
		
		$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedExpired');
		//set your year, month, daym hour, minute, second you want to cuntdown to, Change the numbers beetwen " and "
		if(!empty($rowCatalogAttribute->value))
		{
			$tDate = $rowCatalogAttribute->value;
			$aDate = explode('-', $tDate);
			$year=$aDate[0];
			$month=$aDate[1];
			$day=$aDate[2];
			$hour="00";
			$minute="00";
			$second="00";
			
			//set what is going to happen than
			$event="My birthday";
			
			//don't change anything below unless you know what you are doing
			
			$time=mktime($hour, $minute, $second, $month, $day, $year);
			
			$timecurrent=date('U');
			$cuntdowntime=$time-$timecurrent;
			$cuntdownminutes=$cuntdowntime/60;
			$cuntdownhours=$cuntdowntime/3600;
			$cuntdowndays=$cuntdownhours/24;
			$cuntdownmonths=$cuntdowndays/30;
			$cuntdownyears=$cuntdowndays/365;
			
			//echo 'sisa hari: ' . $cuntdowndays;
			
			if($cuntdowndays < 0)
			{
				echo "<script>alert('Dokumen perjanjian ini telah berakhir masa berlakunya.');</script>";
				echo "<br><strong>Dokumen perjanjian ini telah berakhir masa berlakunya.</strong>";
			}
			else 
			{
				//echo "<script>alert('Dokumen perjanjian ini akan berakhir masa berlakunya dalam ".round($cuntdowndays)." hari.');</script>";
				echo "<br><strong>Dokumen perjanjian ini akan berakhir masa berlakunya dalam ".round($cuntdowndays)." hari.</strong>";
			}
		}
		
	}
	public function render()
	{
		$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower($aName[count($aName)-1])));
	}
}
?>