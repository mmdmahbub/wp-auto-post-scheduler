<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wp_auto_post_func (){
	include 'Classes/PHPExcel/IOFactory.php';

	$time_start = microtime(true);
			
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	
	$filename = plugin_dir_path( __FILE__ ).'uploads/import-posts.xlsx';
	
	$inputFileType = PHPExcel_IOFactory::identify($filename);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($inputFileName);	
	//  Get worksheet dimensions
	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();
	$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	echo $data = count($allDataInSheet); 
	//  Loop through each row of the worksheet in turn
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	
	for ($row = 1; $row <= $highestRow; $row++){ 
		//  Read a row of data into an array
		$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL,TRUE,FALSE);

		foreach($rowData as $d){
			echo '<div class="wrap">'.$d[0].": ".$d[1].":".$d[2]."</div>";
		}
		//  Insert row data array into your database of choice here
		
	}
	

		
}