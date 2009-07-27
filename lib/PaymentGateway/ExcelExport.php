<?php
class PaymentGateway_ExcelExport {
	function toExcell($data_array='', $filename='excel'){
	$headers = ''; // Nama/Header Kolom
	$data = ''; // Data Kolom
    
    
	/*$data_array[]=array('A' => 'A1','B' => 'B1','C' => 'C1');
    $data_array[]=array('A' => 'A2','B' => 'B2','C' => 'C2');*/
	
		if(count($data_array) == 0){
			echo '<p>Tidak ada data untuk diexport</p>';
		}else{
			$n_count=0;
			foreach($data_array as $row){
				$line = '';
				foreach($row as $field=>$value){
				if($n_count==0){
					$headers .= '"'. $field . '"' . "\t";
				}
				if((!isset($value)) || ($value == "")){
					$value = "\t";
				}else{
					$value = str_replace('"', '""', $value);
					$value = '"' . $value . '"' . "\t";
				}
				$line .= $value;
				}
				$n_count++;
				$data .= trim($line)."\n";
			}
			
			$data = str_replace("\r","",$data);
					header("Content-type: application/x-msdownload");
					header("Content-Disposition: attachment; filename=$filename.xls");
					echo "$headers\n$data";  
		}
	}
}
?>