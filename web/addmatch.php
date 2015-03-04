<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';

	$errors=[];


	function extractProvider($p)
	{
		global $ACCEPTED_PROVIDERS;
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$line=isset($p['line']) ? $p['line'] : null;

		$provider=trim($line[0]);
		if (in_array($provider,$ACCEPTED_PROVIDERS))
		{
			return $provider;
		}
	}
	
	function extractKeyColumn($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$line=isset($p['line']) ? $p['line'] : null;

		foreach((array)$line as $key=>$cell)
		{
			$cell=trim($cell,' "');
			if ($cell=='taxonKey')
			{
				return $key;
			}
		}
	}
	
	function resolveTaxon($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		$line=isset($p['line']) ? $p['line'] : null;

		$obj = new StdClass;
		
		$sp_code=null;
		$name=null;
		
		foreach($line as $cell)
		{
			$cell=trim($cell,' "');
			if (strpos($cell,CHECKLIST_SPECIES_ID_PREFIX)===0)
			{
				$sp_code=$cell;
			}
		}
		
		if (empty($sp_code))
		{
			$c=trim($line[0],' "');
			if (!is_numeric($c))
			{
				$name=$c;
			}
			else
			{
				$name=trim($line[1],' "');
			}

		}

		
		if (empty($sp_code) && ((strlen($name)<5) || strpos($name,' ')===false))
		{
			return;
		}

		if (empty($sp_code))
		{
			$obj->match_method='name';

			$s = $conn->prepare('select * from checklist_species where checklist_code = :code and scientific_name = :name');
			$s->bindValue(':code', $code, PDO::PARAM_STR);
			$s->bindValue(':name', $name, PDO::PARAM_STR);
			$s->execute();
			$d=$s->fetch();
			
			if (empty($d))
			{
				$s = $conn->prepare('select * from checklist_species where checklist_code = :code and scientific_name like :name');
				$s->bindValue(':code', $code, PDO::PARAM_STR);
				$s->bindValue(':name', $name.'%', PDO::PARAM_STR);
				$s->execute();
				$d=$s->fetch();
				$obj->exact=false;
			}
			else
			{
				$obj->exact=true;
			}
		}
		else
		{
			$obj->match_method='code';
			$obj->exact=true;

			$s = $conn->prepare('select * from checklist_species where checklist_code = :code and checklist_species_key = :sp_code');
			$s->bindValue(':code', $code, PDO::PARAM_STR);
			$s->bindValue(':sp_code', $sp_code, PDO::PARAM_STR);
			$s->execute();
			$d=$s->fetch();
		}
		
		$obj->id=$d['id'];
		$obj->name=$d['scientific_name'];

		//echo '<pre>';print_r($obj);echo '</pre>';
		
		return $obj;

	}
	
	$code=@$_REQUEST["code"];
	
	if (isset($_REQUEST["code"]) && isset($_REQUEST['file']))
	{
		$file=INCOMING_DATA_FOLDER.$_REQUEST['file'];
		if (file_exists($file))
		{
			$raw=file_get_contents($file);
			$content=json_decode($raw,true);
			
			$conn=connectDb();
			
			if (is_null($content))
			{
				// is JSON, we assume it's not GBIF
				$content=rawToStructured($raw);

				$provider=extractProvider(['conn'=>$conn,'line'=>$content[0]]);
				$keycolumn=extractKeyColumn(['conn'=>$conn,'line'=>$content[1]]);
				
				if (empty($provider))
				{
					$errors[]="no or illegal provider";
				}
				if (empty($keycolumn))
				{
					$errors[]="no key";
				}
				else
				{
					foreach($content as $line)
					{
						$taxon=resolveTaxon(['conn'=>$conn,'code'=>$code,'line'=>$line]);
						if ($taxon->id)
						{
							$res=saveTaxonMatch(['conn'=>$conn,'provider'=>$provider,'taxon'=>$taxon,'keycolumn'=>$keycolumn]);
						}
					}
				}
				
			}
			else
			{
				// is JSON, we assume it's GBIF
				echo print_r($content);
			}
		}
		else
		{
			$errors[]="file doesn't exist";
		}
		
		// judge if GBIF file or not
		
		
		// non-GBIF
		//open file
		//read all lines
			//split line
		//loop lines
			// check taxon
				// create if not exist (lack of code means new)
			// add to checklist_matches
			
		//GBIF
		//open file
		//read all lines
			//split line
		//loop lines
			// check taxon
				// create if not exist (lack of code means new)
			// add to checklist_matches



	}







	$files=scandir(INCOMING_DATA_FOLDER);

	include_once 'includes/html-header.php';

?>
<p class="nav-menu">
	<a href="index.php">index</a>
</p>

<h2>Add match data</h2>

available files:
<div class="file-list">
<?php

	foreach($files as $file)
	{
		if (is_dir(INCOMING_DATA_FOLDER.$file)) continue;
		echo $file,' <a href="?code='.$code.'&file='.urlencode($file).'">process file</a>','<br />';
	}

?>
</div>


<?php

//	if (@$res)
	{
//		echo '<div>',sprintf("saved %s, failed %s lines.",$res[0],$res[1]),'</div>';
		if (!empty($errors))
		{
			echo 'errors:';
			echo '<pre>';
			print_r($errors);
			echo '</pre>';
		}
	}

?>


<br />

<p>
<i>
assumptions:<br />
file with GBIF-occurrences is in JSON with specific structure<br />
file with matches of other providers:
<ul>
<li>first line has the name of the provider</li>
<li>second line has column names</li>
<li>species are resolved based on the checklist-specific key provided in the checklist (i.e., "<?php echo CHECKLIST_SPECIES_ID_PREFIX; ?>xxxxx")</li>
</ul>
</i>
</p>


</body>
</html>
