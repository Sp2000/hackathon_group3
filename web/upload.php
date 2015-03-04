<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';
	
	$errors=[];

	function deleteChecklist($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		
		if (!$conn || empty($code)) return;

		$s = $conn->prepare('delete from checklist_species where checklist_code = :code');
		$s->bindValue(':code', $code, PDO::PARAM_STR);
		$s->execute();
	}

	function saveChecklist($p)
	{
		global $errors;
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		$lines=isset($p['lines']) ? $p['lines'] : null;
		
		if (!$conn || empty($code) || empty($lines)) return;

		$s = $conn->prepare('insert into checklist_species (checklist_code,scientific_name,checklist_species_key) values (:checklist_code,:scientific_name,:checklist_species_key)');
		$s->bindValue(':checklist_code', $code, PDO::PARAM_STR);
		$i=$j=0;
		foreach((array)$lines as $key=>$val)
		{
			if (empty($val[0])) continue;
			$checklist_species_key=isset($val[1]) ? $val[1] : null;
			$s->bindValue(':scientific_name', $val[0], PDO::PARAM_STR);
			$s->bindValue(':checklist_species_key', $checklist_species_key, PDO::PARAM_STR);
			if ($s->execute())
			{
				$i++; 
			}
			else 
			{
				$d=$s->errorInfo();
				$errors[]=$d[2];
				$j++;
			}
		}
		return [$i,$j];
	}	

	if (isset($_POST["checklist_code"]) && (isset($_POST["checklist"]) || isset($_FILES["checklist_file"]["tmp_name"])))
	{
		$code=$_POST["checklist_code"];
		
		$raw='';
		if (isset($_FILES["checklist_file"]["tmp_name"]) && file_exists($_FILES["checklist_file"]["tmp_name"]))
		{
			$raw.=file_get_contents($_FILES["checklist_file"]["tmp_name"]);
		}
		if (isset($_POST["checklist"]))
		{
			$raw.=$_POST["checklist"];
		}

		$delete_existing=isset($_POST["delete_existing"]) ? $_POST["delete_existing"]=='on' : false;
		$lines=rawToStructured($raw);
		$conn=connectDb();
		if ($delete_existing) deleteChecklist(['conn'=>$conn,'code'=>$code]);
		$res=saveChecklist(['conn'=>$conn,'code'=>$code,'lines'=>$lines]);
	}

	include_once 'includes/html-header.php';

?>


<h2>Upload checklist</h2>
<form  method="post" enctype="multipart/form-data">
<p>
    checklist code (for instance, "NL"): <input type="text" name="checklist_code" placeholder="checklist code" value="<?php echo @$code; ?>" /> * <br />
	<input type="checkbox" name="delete_existing" /> delete existing for this checklist code<br />
</p>
<p>
select text file (one record per line):	
<input type="file" name="checklist_file" /><br />
</p>
<p>    or manually add checklist data (same format):<br />
    <textarea name="checklist" style="width:750px;height:250px" placeholder="Abacoproeces saltuum (L. Koch, 1872)	http://www.nederlandsesoorten.nl/nsr/concept/0AHCYFOOGKTT"></textarea><br />
</p>
<p>
	format: <code>&lt;species name&gt;([TAB]&lt;species checklist code&gt;) (one per line)</code>
</p>
<p>
<input type="submit" value="upload & save" />

</p>
</form>
<?php

	if (@$res)
	{
		echo '<div>',sprintf("saved %s, failed %s lines.",$res[0],$res[1]),'</div>';
		if (!empty($errors))
		{
			echo 'errors:';
			echo '<pre>';
			print_r($errors);
			echo '</pre>';
		}
	}

?>
<p class="nav-menu">
	<a href="index.php">index</a>
</p>


</body>
</html>
