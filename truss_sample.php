<?php
ini_set("auto_detect_line_endings", true);
date_default_timezone_set('America/New_York');  //set to eastern timezone

// Open the CSV File
$handle = fopen("sample-with-broken-utf8.csv", "r+");

$csvFileData = array();
$headerData = array();
$counter = 0;

// Parse the data
while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
	$num = count($data);
	$rowData = array();
	$totalDuration = 0.00;
	
	for ($c=0; $c < $num; $c++) {
	    //Get the headers
    	if($counter == 0){
    	    $headerData[] = $data[$c];
    	}
    	else {
    	    switch($headerData[$c]) {
    	        case 'Timestamp': {
    	            $easternTime = new DateTime($data[$c], new DateTimeZone('America/Los_Angeles'));
                    $easternTime->setTimezone(new DateTimeZone('America/New_York'));
                    $data[$c] = $easternTime->format("n/j/o G:i:s");
    	        }break;
    	        case 'Address': {
    	            setlocale(LC_ALL, 'en_GB');                     // set encoding locale
    	            $trans_sentence = mb_convert_encoding($data[$c], 'ISO-8859-15', 'UTF-8');
    	            $data[$c] = $trans_sentence . PHP_EOL;
    	        }break;
    	        case 'ZIP': {
    	            $totalLen = 5;
    	            $diffLen = $totalLen - strlen($data[$c]);
    	            $finalZipStr = "";
    	            for($i=0;$i<$diffLen;$i++){
    	                $finalZipStr .= "0";
    	            }
    	            $finalZipStr .= $data[$c];
    	            $data[$c] = "\t".$finalZipStr;
    	        }break;
    	        case 'FullName': {
    	            setlocale(LC_ALL, 'en_GB');                     // set encoding locale
    	            $trans_sentence = mb_convert_encoding($data[$c], 'ISO-8859-15', 'UTF-8');
    	            
    	            // Can also convert the string to html, then replace all entities with known symbols before printing back to CSV
    	            //$htmlSentence = mb_convert_encoding($data[$c], 'HTML-ENTITIES', "UTF-8");

    	            $data[$c] = strtoupper($trans_sentence . PHP_EOL);
    	        }break;
    	        case 'FooDuration': {
	                $timeParts = explode(':', $data[$c]);
	                $hours = ((float)$timeParts[0] * 60) * 60;
	                $minutes = (float)$timeParts[1] * 60;
	                $secondParts = explode(".", $timeParts[2]);
	                $seconds = (float)$secondParts[0];
	                $milliseconds = (float)$secondParts[1];
	                
                    $fooDur = $hours + $minutes + $seconds;
                    $finalFloat = $fooDur.".".$milliseconds;

	                $totalDuration += (float)$finalFloat;
    	        }break;
    	        case 'BarDuration': {
                    $timeParts = explode(':', $data[$c]);
	                $hours = ((float)$timeParts[0] * 60) * 60;
	                $minutes = (float)$timeParts[1] * 60;
	                $secondParts = explode(".", $timeParts[2]);
	                $seconds = (float)$secondParts[0];
	                $milliseconds = (float)$secondParts[1];
	                
                    $fooDur = $hours + $minutes + $seconds;
                    $finalFloat = $fooDur.".".$milliseconds;

	                $totalDuration += (float)$finalFloat;
    	        }break;
    	        case 'TotalDuration': {
    	            $data[$c] = $totalDuration;
    	        }break;
    	        case 'Notes': {
    	            setlocale(LC_ALL, 'en_GB');                     // set encoding locale
    	            $trans_sentence = mb_convert_encoding($data[$c], 'ISO-8859-15', 'UTF-8');
    	            
    	            // Can also convert the string to html, then replace all entities with known symbols before printing back to CSV
    	            //$htmlSentence = mb_convert_encoding($data[$c], 'HTML-ENTITIES', "UTF-8");

    	            $data[$c] = strtoupper($trans_sentence . PHP_EOL);
    	        }break;
    	        default: {}break;
    	    }
    	}
	    
		$rowData[] = (string)$data[$c];
		
	}
	$csvFileData[] = $rowData;
	$counter++;
}
fclose($handle);
ini_set("auto_detect_line_endings", false);

// Create the CSV file
$fileName = "Truss_normalized_sample.csv";
header("Content-type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=$fileName");

$fp = fopen('php://output', 'w+');
foreach($csvFileData AS $singleRow){
    fputcsv($fp, $singleRow);
}
fclose($fp);
    
?>

