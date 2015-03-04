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
		foreach ($GLOBALS["basisOfRecords"] as $basisOfRecord)
	    {
			$url = API."occurrence/count?country=".$country."&taxonKey=".$taxonKey."&basisOfRecord=".$basisOfRecord;
			$output = getCurl($url);
			//$curReq = array($basisOfRecord => $requests["total"]."&basisOfRecord=" . $basisOfRecord);
			echo " ";
			echo $basisOfRecord." - ".$output;
	    }
	}

	//Get the total number of occurrences by range of dates for a specific taxonKey and a specific country
	function getOccurrencesByDates($country, $taxonKey){
		$dateMin = $GLOBALS["dateMin"];
		while ($GLOBALS["dateMax"] > $dateMin){
			$url=API."occurrence/search?country=".$country."&taxonKey=".$taxonKey."&year=".$dateMin.",".$GLOBALS["dateMax"]."&limit=1";
			$output = getCurl($url);
			$json =  json_decode($output);
			echo " ";
			echo $dateMin." - ".$json->count;
			$dateMin += 10;
		}
	}


?>
