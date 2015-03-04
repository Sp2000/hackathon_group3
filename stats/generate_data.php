<?php 
	include_once("config.inc.php");
	include_once("stats.inc.php");

	$taxonKey=["2481139", "5229490", "4409003", "2498009", "2381987"];

	$totalOccurrences = getTotalOccurrences("NL", "2481139");
	echo $totalOccurrences." ";
	getOccurrencesByBasisOfRecords("NL", "2481139");
	getOccurrencesByDates("NL", "2481139");

?>