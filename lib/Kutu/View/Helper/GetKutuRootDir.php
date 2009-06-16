<?php
      class Kutu_View_Helper_GetKutuRootDir
      {
          public function getKutuRootDir($jsStringOutput=true)
          {
          		if($jsStringOutput)
          		{
	          		//we need the "\\\\" because this function will be used in javascript string, so we need to escape it
	         		return str_replace('\\', '\\\\', KUTU_ROOT_DIR);
          		}
          		else 
          		{	
              		return KUTU_ROOT_DIR;
          		}
          }
      }
?>