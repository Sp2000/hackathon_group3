<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';

	function getChecklists($conn)
	{
		$s = $conn->prepare('select count(*) as total, checklist_code from checklist_species group by checklist_code');
		$s->execute();
		return $s->fetchAll();
	}
	
	$conn=connectDb();
	$lists=getChecklists($conn);
	
	include_once 'includes/html-header.php';

?>
<body>
<div class="title">
    <h2>
        Species 2000 Hackathon March 2015<br/>
        Team 3: Matching distributions
    </h2>
</div>

<a href="upload.php">upload</a>
</p>

<p>
	uploaded checklists:<br />
<?php

	foreach((array)$lists as $key=>$val)
	{
		echo '&#149; ','<a href="browse.php?code='.$val['checklist_code'].'">',$val['checklist_code'],'</a>',' (', $val['total'],' species)<br />';
	}

?>
</p>

</body>
</html>
