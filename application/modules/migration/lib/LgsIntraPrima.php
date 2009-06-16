<?php
class Migration_LgsIntraPrima
{
	function putusan_ptsTkProses()
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("profileGuid='kutu_putusan' AND attributeGuid='ptsTkProses'")->current();

		$json = new Zend_Json();
		$aPrtTkProses = $json->decode($rowProfileAttribute->defaultValues);

		//var_dump($aPrtTkProses);

		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$rowsetCatalogAttribute = $tblCatalogAttribute->fetchAll("attributeGuid='ptsTkProses'");
		foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
		{
			switch ($rowCatalogAttribute->value)
			{
				case 'Peninjauan Kembali':
					$rowCatalogAttribute->value = 'pk';
					$rowCatalogAttribute->save();
					break;
				case 'Tingkat Pertama':
					$rowCatalogAttribute->value = 'pertama';
					$rowCatalogAttribute->save();
					break;
				case 'Proses Khusus':
					$rowCatalogAttribute->value = 'khusus';
					$rowCatalogAttribute->save();
					break;
				case 'Kasasi':
					$rowCatalogAttribute->value = 'kasasi';
					$rowCatalogAttribute->save();
					break;
				case 'Banding':
					$rowCatalogAttribute->value = 'banding';
					$rowCatalogAttribute->save();
					break;
				case 'Putusan Sela':
					$rowCatalogAttribute->value = 'putusan-sela';
					$rowCatalogAttribute->save();
					break;
				case 'Pra Peradilan':
					$rowCatalogAttribute->value = 'pra-peradilan';
					$rowCatalogAttribute->save();
					break;
				case 'Penetapan':
					$rowCatalogAttribute->value = 'penetapan';
					$rowCatalogAttribute->save();
					break;
			}
		}
	}
	function putusan_ptsYuris()
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("profileGuid='kutu_putusan' AND attributeGuid='ptsYuris'")->current();

		$json = new Zend_Json();
		$aPrtTkProses = $json->decode($rowProfileAttribute->defaultValues);

		//var_dump($aPrtTkProses);

		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$rowsetCatalogAttribute = $tblCatalogAttribute->fetchAll("attributeGuid='ptsYuris'");
		foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
		{
			switch ($rowCatalogAttribute->value)
			{
				case 'Ya':
					$rowCatalogAttribute->value = '1';
					$rowCatalogAttribute->save();
					break;
				case 'Tidak Ada Informasi':
					$rowCatalogAttribute->value = 'no-info';
					$rowCatalogAttribute->save();
					break;

			}
		}
	}
	function putusan_ptsAmar()
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("profileGuid='kutu_putusan' AND attributeGuid='ptsAmar'")->current();

		$json = new Zend_Json();
		$aPtsAmar = $json->decode($rowProfileAttribute->defaultValues);

		//var_dump($aPtsAmar);

		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$rowsetCatalogAttribute = $tblCatalogAttribute->fetchAll("attributeGuid='ptsAmar'");
		foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
		{

			foreach ($aPtsAmar as $rowTmp)
			{
				if(strtolower($rowCatalogAttribute->value)==strtolower($rowTmp['label']))
				{
					$rowCatalogAttribute->value = $rowTmp['value'];
					$rowCatalogAttribute->save();
					break;
				}
			}

		}
	}
	function putusan_ptsJenisLembaga()
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("profileGuid='kutu_putusan' AND attributeGuid='ptsJenisLembaga'")->current();

		$json = new Zend_Json();
		$aJson = $json->decode($rowProfileAttribute->defaultValues);

		var_dump($aJson);
		//die();

		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$rowsetCatalogAttribute = $tblCatalogAttribute->fetchAll("attributeGuid='ptsJenisLembaga'");
		foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
		{

			foreach ($aJson as $rowTmp)
			{
				if(strtolower($rowCatalogAttribute->value)==strtolower($rowTmp['label']))
				{
					$rowCatalogAttribute->value = $rowTmp['value'];
					$rowCatalogAttribute->save();
					break;
				}
			}

		}
	}
	function putusan_ptsHakim()
	{

		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$rowsetCatalogAttribute = $tblCatalogAttribute->fetchAll("attributeGuid='ptsHakim'");
		foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
		{
			switch ($rowCatalogAttribute->value)
			{
				case 'Majelis':
					$rowCatalogAttribute->value = 'majelis';
					$rowCatalogAttribute->save();
					break;
				case 'Tunggal':
					$rowCatalogAttribute->value = 'tunggal';
					$rowCatalogAttribute->save();
					break;

			}
		}
	}


	//PERATURAN ----------------------
	function convertPeraturanPrtJenis($sourceValue)
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("attributeGuid='prtJenis'")->current();

		$json = new Zend_Json();
		$aJson = $json->decode($rowProfileAttribute->defaultValues);
		
		switch ($sourceValue)
		{
			case 'Undang-Undang ':
				$sourceValue = 'Undang-Undang';
				break;
			case "Surat Edaran Direktur Jenderal":
				break;
			case "Surat Edaran Mahkamah Agung":
				$sourceValue = "Surat Edaran Lembaga/Badan";
				break;
			case "Tidak Ada":
				$sourceValue = "Lainnya";
				break;
			case "PERPU":
				$sourceValue = 'Peraturan Pemerintah Pengganti Undang-Undang (PERPU)';
				break;
			case "Surat Direktur Jenderal":
				break;
			case "Keputusan Direksi ":
				$sourceValue = 'Keputusan Direksi';
				break;
			case "TAP MPR":
				$sourceValue = 'Ketetapan MPR';
				break;
			case "TUS MPR":
				$sourceValue = 'Keputusan MPR';
				break;
		}

		foreach ($aJson as $rowTmp)
		{
			if(strtolower($sourceValue)==strtolower($rowTmp['label']))
			{
				return $rowTmp['value'];
				break;
			}
		}
		
		echo "<br>NOT FOUND: ". $sourceValue;
		return $sourceValue;

		
	}

	function convertPeraturanPrtPengumuman($sourceValue)
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("attributeGuid='prtPengumuman'")->current();

		$json = new Zend_Json();
		$aJson = $json->decode($rowProfileAttribute->defaultValues);
		
		switch ($sourceValue)
		{
			case 'Lembaran Daerah DKI Jakarta':
				$sourceValue = 'Lembaran Daerah';
				break;
			case "Lembaran Daerah Nusa Tenggara Barat":
				$sourceValue = "Lembaran Daerah";
				break;
			case "Lembaran Daerah Sulawesi Tenggara":
				$sourceValue = "Lembaran Daerah";
				break;
			case "Berita Daerah DKI Jakarta":
				$sourceValue = "Berita Daerah";
				break;
			case "Lembaran Negara ":
				$sourceValue = "Lembaran Negara";
				break;

		}
		
		foreach ($aJson as $rowTmp)
		{
			if(strtolower($sourceValue)==strtolower($rowTmp['label']))
			{
				return $rowTmp['value'];
				break;
			}
		}
		
		echo "<br>NOT FOUND: ". $sourceValue;;
		return $sourceValue;

	}
	function convertPeraturanPrtJenisPengumuman($sourceValue)
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("profileGuid='kutu_peraturan' AND attributeGuid='prtJenisPengumuman'")->current();

		$json = new Zend_Json();
		$aJson = $json->decode($rowProfileAttribute->defaultValues);
		
		switch ($sourceValue)
		{
			case 'Tambahan Lembaran Daerah DKI Jakarta':
				$sourceValue = 'Tambahan Lembaran Daerah';
				break;
			case 'Tambahan Lembaran Negara ':
				$sourceValue = 'Tambahan Lembaran Negara';
				break;
		}

		foreach ($aJson as $rowTmp)
		{	
			if(strtolower($sourceValue)==strtolower($rowTmp['label']))
			{
				return $rowTmp['value'];
				break;
			}
		}
		
		
		echo "<br>NOT FOUND: ". $sourceValue;
		return $sourceValue;

		
	}
	function convertPeraturanPrtRancangan($sourceValue)
	{
		$tblProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
		$rowProfileAttribute = $tblProfileAttribute->fetchAll("attributeGuid='prtRancangan'")->current();

		$json = new Zend_Json();
		$aJson = $json->decode($rowProfileAttribute->defaultValues);
		
		foreach ($aJson as $rowTmp)
		{
			/*switch ($rowCatalogAttribute->value)
			{
				case 'Tambahan Lembaran Daerah DKI Jakarta':
					$rowCatalogAttribute->value = 'Tambahan Lembaran Daerah';
					break;
			}*/

			if(strtolower($sourceValue)==strtolower($rowTmp['label']))
			{
				return $rowTmp['value'];
				break;
			}
		}
		
		echo "<br>NOT FOUND: ". $sourceValue;
		return $sourceValue;

		
	}
}
?>