<?php 
	include_once("config.inc.php");
	include_once("stats.inc.php");
	ini_set('auto_detect_line_endings', true);

	define("searchEngine","http://www.gbif.org/occurrence/search");

	$myFile = fopen($fileName, 'r');

	// Total of taxon in the file
	$lines = file($fileName);
	$n = count($lines)-1; 
	$taxonList=array("count"=>$n, "results"=>array());

	foreach ($lines as $line_num => $line) {
		$taxon = explode("\t", $line);
		if ($line_num == 0){
			$scientificName = array_search("scientificName", $taxon);
			$countryCode = array_search("countryCode", $taxon);
			$taxonKey = array_search("taxonKey", $taxon);
			$sourceTaxonId = array_search("sourceTaxonID", $taxon);
		}else {
	 		$totalOccurrences=getTotalOccurrences($taxon[$countryCode], $taxon[$taxonKey]);
		 	$urlSearch=searchEngine."?TAXON_KEY=".$taxon[$taxonKey]."&COUNTRY=".$taxon[$countryCode];
		 	$basisOfRecords= getOccurrencesByBasisOfRecords($taxon[$countryCode], $taxon[$taxonKey]);
		 	$dates=getOccurrencesByDates($taxon[$countryCode], $taxon[$taxonKey], $totalOccurrences);

		 	$taxons = array("taxonKey"=>$taxon[$taxonKey],
		 					"sourceTaxonId"=>$taxon[$sourceTaxonId],
		 					"scientificName"=>$taxon[$scientificName],
		 					"countryCode"=>$taxon[$countryCode],
		 					"urlSearch"=>$urlSearch,
							"totalOccurrences"=>$totalOccurrences,
		 					"basisOfRecords" => $basisOfRecords,
		 					"dates"=>$dates);

		 	array_push($taxonList["results"],$taxons); 
	 	}
		
	 }

	fclose($myFile);
	$json = json_encode($taxonList);

	write_file($taxonFile,$json);



	// Using a tab for example
	// $objTaxons=[["http://www.nederlandsesoorten.nl/nsr/concept/0AHCYFOOGKTT", "Abacoproeces saltuum (L. Koch, 1872)", "2137144", "NL"], ["http://www.nederlandsesoorten.nl/nsr/concept/0AHCYSI11280", "Abietinaria abietina (Linnaeus, 1758)", "2269258", "NL"], ["http://www.nederlandsesoorten.nl/nsr/concept/0AHCYFCMZMBH", "Aethes francillana (Fabricius, 1794)", "1738334", "NL"]];

	// $count=sizeof($objTaxons);
	// $taxonList=array("count"=>$count, "results"=>array());

	// foreach ($objTaxons as $key => $taxon) {

	// 	$totalOccurrences=getTotalOccurrences($taxon[3], $taxon[2]);
	// 	$urlSearch=searchEngine."?TAXON_KEY=".$taxon[2]."&COUNTRY=".$taxon[3];
	// 	$basisOfRecords= getOccurrencesByBasisOfRecords($taxon[3], $taxon[2]);
	// 	$dates=getOccurrencesByDates($taxon[3], $taxon[2], $totalOccurrences);

	// 	$taxons = array("taxonKey"=>$taxon[2],
	// 					"sourceTaxonId"=>$taxon[0],
	// 					"scientificName"=>$taxon[1],
	// 					"country"=>$taxon[3],
	// 					"urlSearch"=>$urlSearch,
	// 					"totalOccurrences"=>$totalOccurrences,
	// 					"basisOfRecords" => $basisOfRecords,
	// 					"dates"=>$dates);

	// 	array_push($taxonList["results"],$taxons); 
		
	// }
	// $json = json_encode($taxonList);

	// write_file($taxonFile,$json);


?>