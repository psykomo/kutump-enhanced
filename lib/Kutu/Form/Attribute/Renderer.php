<?php

/**
 * manage Form Template Attribute for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Form_Attribute_Renderer
{
	var $tblGuid;
	var $name;
	var $value;
	var $type;
	var $attribs;
	var $profileGuid;
	
	public function __construct($attributeGuid=null, $value=null, $type=null, $attribs=null, $profileGuid=null, $other=null)
	{
		$this->name = $attributeGuid;
		$this->value = $value;
		$this->type = $type;
		$this->attribs = $attribs;
		$this->profileGuid = $profileGuid;
		$this->other = $other;
	}
	
	public function render()
	{
		$sReturn = '';
		
		switch($this->type) 
		{
			default:
	        case 0:     // field type = single line
	        	
	        	$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 1, 'cols' =>50, 'style'=>"width:250px;height:22px;padding:5px;");
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea.phtml');
	            break;
	
	        case 1:     // field type = textarea paragraph
	        	
	        	$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 5, 'cols' =>50);
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea.phtml');
	            
	            break;
	
	        case 2:     // field type = html paragraph
				
				require_once('FCKeditor/fckeditor.php');
				$oFCKeditor = new FCKeditor($this->name) ;
				$oFCKeditor->BasePath = KUTU_ROOT_URL.'/lib/FCKeditor/';
				$oFCKeditor->Value = $this->value;
				$oFCKeditor->Width  = '100%' ;
				$oFCKeditor->Height = '400' ;
	
				$sReturn = $oFCKeditor->CreateHtml() ;
	            
	            return $sReturn;
	            break;
	            
	        case 3:     // field type = hidden
	            $n = "<input type='hidden' name='$this->name' value='$this->value'>";
	            return $n;
	            break;
	            
	        case -4:
		        //$value = $this->convertDate($fieldValue);
		        $value = $fieldValue;
				echo '<script language="Javascript" src="calendar/calendar.js"></script>';
				echo '<input type="text" name="' . $attributeId . '" value="' . $value . '">';
				$fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
				echo '&nbsp;<a href="javascript: void(0);" onclick="return getCalendar(document.forms[0].'.$attributeId.');" onChange="AddCurrentTime(document.forms[0].'.$attributeId.');">Pilih Tanggal</a>';
				
				break;
			
	        case -41:	
	        	$n = '<textarea id="html" name="jTagEditor" class="jTagEditor">'.$this->value.'</textarea>';
	        	return $n;
	        break;
	        
			//datetime field	
			case 4:
				/*echo 
				'<link rel="stylesheet" type="text/css" media="all" href="calendar2/calendar-mos.css" title="green" />	
				<script type="text/javascript" src="calendar2/calendar.js"></script>
				<script type="text/javascript" src="calendar2/lang/calendar-en.js"></script>
				<script language="javascript" src="calendar2/mambojavascript.js"></script>';*/
				
				$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('datetime.phtml');
				
				/*$fieldTblGuid = $attributeId.'_guid';
				echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
				echo '<input class="inputbox" type="text" name="'.$attributeId.'" id="'.$attributeId.'" size="25" maxlength="25" value="'.$fieldValue.'" />';
	          	
				echo '<input type="reset" class="button" value="..." onClick="return showCalendar'."('$attributeId', 'dd/mm/Y')".';">';*/
				break;
				
			case -5:     // field type = Image Area
	            $frm = new FormInputImageAreaUc();
				$frm->fieldName=$attributeId;
				$frm->fieldValue=$fieldValue;
				$frm->renderMe();
				$fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;
	        
	        case -6:     // field type = LABEL
	         	echo $fieldValue;
	            echo "<input type='hidden' name='$attributeId' value='$fieldValue'>";
	            $fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;
	            
	        case 7:     // field type = MULTI VALUE (SELECT:OPTIONS)
	        
	        	/*$oAttGenerator = new UiFormInputAttributeGenerator();
	        	$s = $oAttGenerator->generateFormInputAttributeByDmsProfileGuidAndAttributeGuid($this->dmsProfileGuid,$attributeId,$attributeId,$fieldValue);
	        	
	        	echo $s;
	            //echo "<textarea name='$attributeId' rows='0' cols='50'>$fieldValue</textarea>";
	            
	            $fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;*/
	        	$tblProAtt = new Kutu_Core_Orm_Table_ProfileAttribute();
	        	$rowset = $tblProAtt->fetchAll("profileGuid='$this->profileGuid' AND attributeGuid='$this->name'");
	        	$defaultValues = array();
	        	if(count($rowset)==1)
	        	{
	        		$row = $rowset->current();
	        		$defaultValues =  Zend_Json::decode($row->defaultValues);
	        		
	        		if(is_array($defaultValues))
	        		{
	        			//var_dump($defaultValues);
	        			
	        		}
	        		else 
	        		{
	        			$defaultValues = array();
	        		}
	        	}
	        	
	        	$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $defaultValues;
				/*if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 5, 'cols' =>50);*/
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
	            
	            break;
			
	        case 8 :
	        	
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowset = $tblCatalog->fetchAll("profileGuid='$this->other'");
				$i = 0;
				$a = array();
				$data = array();
				foreach ($rowset as $row)
				{
					$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute();
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
					$a[$i]['label']= ((is_object($rowCatalogAttribute)) ? $rowCatalogAttribute->value : '');
					$a[$i]['value']= "$row->guid";
					$a[$i]['selected']= ($i==0)? "true" : "false";
					$i++;
				}
				
				$data =  Zend_Json::decode(Zend_Json::encode($a));      	
				
	        	$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $data;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
				
	        break;
	        case 9 :	        
	            $n = "<input type='text' class='txt' name='$this->name' value='$this->value' size='5'>";
	            return $n;
	        break;
        }
	}
}

?>