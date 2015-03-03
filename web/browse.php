<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';

	function getChecklistSpecies($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		$start=isset($p['start']) ? (int) $p['start'] : 0;
		$per_page=isset($p['per_page']) ? (int)$p['per_page'] : SPECIES_PER_PAGE;

		$s = $conn->prepare('select * from checklist_species where checklist_code = :code LIMIT :limit OFFSET :offset');
		$s->bindValue(':code', $code, PDO::PARAM_STR);
		$s->bindValue(':limit', $per_page, PDO::PARAM_INT);
		$s->bindValue(':offset', $start, PDO::PARAM_INT);
		$s->execute();
		return $s->fetchAll();
	}
	

	if (isset($_REQUEST["code"]))
	{
		$code=$_REQUEST["code"];
		$conn=connectDb();
		$species=getChecklistSpecies(['conn'=>$conn,'code'=>$code]);
		
	}
	
		include_once 'includes/html-header.php';

?>
<body>

<p>
<a href="upload.php">upload</a>
</p>

<p>
	species:<br />
<?php

	foreach((array)$species as $key=>$val)
	{
		echo '&#149; ','<a href="'.$val['checklist_species_key'].'" target="_detail">',$val['scientific_name'],'</a><br />';
	}

?>
</p>

</body>
</html>
