<?php

class Kutu_Form_Helper_PromosiGenerator
{
	function generateFormAdd()
	{
		$aBaseAttributes = array();
		
		$aBaseAttributes['disc']['description'] = 'Disc (%)';
		$aBaseAttributes['disc']['form'] = "<input type='text' name='disc' id='disc' size='5'>";
		$aBaseAttributes['bln']['description'] = 'Monthly added';
		$aBaseAttributes['bln']['form'] = "<input type='text' name='bln' id='bln' size='5'>";
		
		$type = "<select name='type' id='type'>";
		$type .= "<option value='' selected>--- Choose type ---</option>";
		$type .= "<option value='1'>Organisasi</option>";
		$type .= "<option value='2'>Periode</option>";
		$type .= "<option value='3'>Umum</option>";
		$aBaseAttributes['type']['description'] = 'Type';
		$aBaseAttributes['type']['form'] = $type;
		
		$aBaseAttributes['max']['description'] = 'Maximum User';
		$aBaseAttributes['max']['form'] = "<input type='text' name='max' id='max' size='5'>";
		
		$aBaseAttributes['periode1']['description']	= 'Period';
		$aBaseAttributes['periode1']['form'] = "<input type='text' name='periode1' readonly class='dateRange' id='dFrom'/> To <input type='text' name='periode2' readonly class='dateRange' id='dTo'>";	
		$aBaseAttributes['kode']['description'] = 'Organization ID';
		$aBaseAttributes['kode']['form'] = "<input type='text' name='kode' id='kode'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		
		return $aReturn;
	}
}