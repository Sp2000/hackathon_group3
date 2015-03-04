<?php
	
	$taxonFile = dirname(__FILE__) .'/json/taxonStats.json';

	$basisOfRecordsList = array("HUMAN_OBSERVATION", "OBSERVATION", "PRESERVED_SPECIMEN", "UNKNOWN", "FOSSIL_SPECIMEN",  "LIVING_SPECIMEN", "MACHINE_OBSERVATION", "LITERATURE","MATERIAL_SAMPLE");

	$dateMax = 2020;

	function getCurl($url)
	{
	  	$ch = curl_init();
	  	curl_setopt_array($ch, array(CURLOPT_URL => $url,CURLOPT_RETURNTRANSFER => true));
	  	return curl_exec($ch);
	}

	function write_file($file,$data)
	{
	  	$myfile = fopen($file, "w") or die("Unable to open file!");
	  	fwrite($myfile, $data);
	  	fclose($myfile);
	  	echo  "Done ! [Saved in ".$file." ] \n";
	}
?>