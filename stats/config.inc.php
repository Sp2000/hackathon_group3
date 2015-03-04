<?php

	$basisOfRecords = array("HUMAN_OBSERVATION", "OBSERVATION", "PRESERVED_SPECIMEN", "UNKNOWN", "FOSSIL_SPECIMEN",  "LIVING_SPECIMEN", "MACHINE_OBSERVATION", "LITERATURE","MATERIAL_SAMPLE");

	$dateMax = 2020;
	$dateMin = 1900;

	function getCurl($url)
	{
	  	$ch = curl_init();
	  	curl_setopt_array($ch, array(CURLOPT_URL => $url,CURLOPT_RETURNTRANSFER => true));
	  	return curl_exec($ch);
	}

	
?>