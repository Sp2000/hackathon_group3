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

<p>
	uploaded checklists:
    <ul>
<?php

	foreach((array)$lists as $key=>$val)
	{
		echo '<li>','<a href="browse.php?code='.$val['checklist_code'].'">',$val['checklist_code'],'</a>',' (', $val['total'],' species)','</li>';
	}
?>
	</ul>
</p>

<p>
	<a href="upload.php">upload a checklist</a>
</p>

</div>
</body>
</html>
