<?php

/**
 * manage ControllerUrl for Application
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_View_Helper_GetControllerUrl
{
	public function getControllerUrl()
    {
		$util = new Kutu_Core_Util();
		return $util->getControllerUrl();
    }
}

?>