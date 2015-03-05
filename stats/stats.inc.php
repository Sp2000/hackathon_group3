<?php 
	define("API","http://api.gbif.org/v1/");



	//Get the total number of occurrences for a specific taxonKey and a specific country
	function getTotalOccurrences($country, $taxonKey)
	{
		$url = API."occurrence/count?country=".$country."&taxonKey=".$taxonKey;
	  	$output = getCurl($url);
	  	return $output;
	}

	//Get the total number of occurrences by basis of record for a specific taxonKey and a specific country
	function getOccurrencesByBasisOfRecords($country, $taxonKey)
	{
		foreach ($GLOBALS["basisOfRecordsList"] as $basisOfRecord)
	    {
			$url = API."occurrence/count?country=".$country."&taxonKey=".$taxonKey."&basisOfRecord=".$basisOfRecord;
			echo $url."\n";
			$output = getCurl($url);	
			$basisOfRecords[$basisOfRecord]= $output;
			//$curReq = array($basisOfRecord => $requests["total"]."&basisOfRecord=" . $basisOfRecord);
	    }
	    return $basisOfRecords;
	}

	//Get the total number of occurrences by range of dates for a specific taxonKey and a specific country
	function getOccurrencesByDates($country, $taxonKey, $totalOccurrences){
		$dateMin = $GLOBALS["dateMin"];
		//Calculation of PRE - dateMax
		$url=API."occurrence/search?country=".$country."&taxonKey=".$taxonKey."&year=*%2C".$GLOBALS["dateMax"]."&limit=1";
		$output = getCurl($url);
		$json =  json_decode($output);
		$dates["NoDate"] = $totalOccurrences - $json->count;
		$dates["All"] = $json->count;

		$dateMin = $GLOBALS["dateMax"] - 50;
		$url=API."occurrence/search?country=".$country."&taxonKey=".$taxonKey."&year=".$dateMin.",".$GLOBALS["dateMax"]."&limit=1";
		$output = getCurl($url);
		$json =  json_decode($output);
		$dates["INT_".$dateMin."-".$GLOBALS["dateMax"]] = $json->count;
		
		$dateMin = $GLOBALS["dateMax"] - 10;
		$url=API."occurrence/search?country=".$country."&taxonKey=".$taxonKey."&year=".$dateMin.",".$GLOBALS["dateMax"]."&limit=1";
		$output = getCurl($url);
		$json =  json_decode($output);
		$dates["INT_".$dateMin."-".$GLOBALS["dateMax"]] = $json->count;
		return $dates;
	}


?>
