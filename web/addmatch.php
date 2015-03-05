<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';
	
	// mysqli inserts faster than PDO?
	$mysqlConn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_DATABASE);

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
			if ($cell=='taxonKey' || $cell=='col_taxon_id')
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
		$sp_code=isset($p['sp_code']) ? $p['sp_code'] : null;

		//$provider_key=isset($p['provider_key']) ? $p['provider_key'] : null;
		//$match_provider=isset($p['match_provider']) ? $p['match_provider'] : null;

		$obj = new StdClass;
		$name=null;
		
		if (empty($sp_code))
		{
			foreach($line as $cell)
			{
				$cell=trim($cell,' "');
				if (strpos($cell,CHECKLIST_SPECIES_ID_PREFIX)===0)
				{
					$sp_code=$cell;
				}
			}
		}
		
		if (empty($sp_code) && !empty($line))
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



		if (!empty($sp_code))
		{
			$obj->match_method='code';
			$obj->exact=true;

			$s = $conn->prepare('select * from checklist_species where checklist_code = :code and checklist_species_key = :sp_code');
			$s->bindValue(':code', $code, PDO::PARAM_STR);
			$s->bindValue(':sp_code', $sp_code, PDO::PARAM_STR);
			$s->execute();
			$d=$s->fetch();
		}
		/*
		if (empty($d['id']) && !empty($match_provider) && !empty($provider_key))
		{
			$obj->match_method=$match_provider.' key';

			$s = $conn->prepare('select * from checklist_matches where match_provider_key = :provider_key and match_provider = :match_provider');
			$s->bindValue(':provider_key', $provider_key, PDO::PARAM_STR);
			$s->bindValue(':match_provider', $match_provider, PDO::PARAM_STR);
			$s->execute();
			$d=$s->fetch();
			if (!empty($d))
			{


				$obj->exact=true;
			}
		}
		*/

		if (empty($d['id']) && !empty($name))
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

		$obj->checklist_species_id=isset($d['id']) ? $d['id'] : null;
		$obj->name=isset($d['scientific_name']) ? $d['scientific_name'] : null;

		//echo '<pre>';print_r($obj);echo '</pre>';die();
		
		return $obj;

	}
	


	function deletePreviousMatches($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$provider=isset($p['provider']) ? $p['provider'] : null;
		$s = $conn->prepare('delete from checklist_matches where match_provider = :provider');
		$s->bindValue(':provider', $provider, PDO::PARAM_STR);
		$s->execute();
	}

	function saveTaxonMatch($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$provider=isset($p['provider']) ? $p['provider'] : null;
		$taxon=isset($p['taxon']) ? $p['taxon'] : null;
		$keycolumn=isset($p['keycolumn']) ? $p['keycolumn'] : null;
		$line=isset($p['line']) ? $p['line'] : null;

		$match_provider_key=isset($line[$keycolumn]) ? $line[$keycolumn]: null;
		$match_value='1'; // presence in the file is considered a 'true' value
		
		global $mysqlConn;

		$sql="insert into checklist_matches (checklist_species_id,match_provider,match_provider_key,match_value) 
		values (". $taxon->checklist_species_id.",'".$provider."','".$match_provider_key."','".$match_value."')";

		
		mysqli_query($mysqlConn,$sql);
		
		/*
		$s = $conn->prepare('insert into checklist_matches (checklist_species_id,match_provider,match_provider_key,match_value) values (:checklist_species_id,:provider,:match_provider_key,:match_value)');
		$s->bindValue(':checklist_species_id', $taxon->checklist_species_id, PDO::PARAM_INT);
		$s->bindValue(':provider', $provider, PDO::PARAM_STR);
		$s->bindValue(':match_provider_key', $match_provider_key, PDO::PARAM_STR);
		$s->bindValue(':match_value', $match_value, PDO::PARAM_STR);
		
		if ($s->execute())
		{
			return true;
		}
		else 
		{
			//$d=$s->errorInfo();
			//$errors[]=$d[2];
			//$j++;
		}
		*/

	}
	


	function deletePreviousGBIFCounts($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$taxon=isset($p['taxon']) ? $p['taxon'] : null;
		if (empty($taxon->checklist_species_id)) return;
		$s = $conn->prepare('delete from checklist_gbif_matches where checklist_species_id = :taxon');
		$s->bindValue(':taxon', $taxon->checklist_species_id, PDO::PARAM_INT);
		$s->execute();
	}

	function saveGBIFCounts($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$taxon=isset($p['taxon']) ? $p['taxon'] : null;
		$basisOfRecords=isset($p['basisOfRecords']) ? $p['basisOfRecords'] : null;
		$dates=isset($p['dates']) ? $p['dates'] : null;
		$taxonKey=isset($p['taxonKey']) ? $p['taxonKey'] : null;

		global $mysqlConn;

		$sql="insert into checklist_gbif_matches (
				checklist_species_id,
				gbif_key,
				basisOfRecord_HUMAN_OBSERVATION,
				basisOfRecord_OBSERVATION,
				basisOfRecord_PRESERVED_SPECIMEN,
				basisOfRecord_UNKNOWN,
				basisOfRecord_FOSSIL_SPECIMEN,
				basisOfRecord_LIVING_SPECIMEN,
				basisOfRecord_MACHINE_OBSERVATION,
				basisOfRecord_LITERATURE,
				basisOfRecord_MATERIAL_SAMPLE,
				date_NoDate,
				date_All,
				date_1970_2020,
				date_2010_2020
			) 
			values
			(
				:checklist_species_id,
				:gbif_key,
				:basisOfRecord_HUMAN_OBSERVATION,
				:basisOfRecord_OBSERVATION,
				:basisOfRecord_PRESERVED_SPECIMEN,
				:basisOfRecord_UNKNOWN,
				:basisOfRecord_FOSSIL_SPECIMEN,
				:basisOfRecord_LIVING_SPECIMEN,
				:basisOfRecord_MACHINE_OBSERVATION,
				:basisOfRecord_LITERATURE,
				:basisOfRecord_MATERIAL_SAMPLE,
				:date_NoDate,
				:date_All,
				:date_1970_2020,
				:date_2010_2020
			)";

		$s = $conn->prepare($sql);
		$s->bindValue(':checklist_species_id', $taxon->checklist_species_id, PDO::PARAM_INT);
		$s->bindValue(':gbif_key', $taxonKey, PDO::PARAM_STR);
		$s->bindValue(':basisOfRecord_HUMAN_OBSERVATION', $basisOfRecords['HUMAN_OBSERVATION'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_OBSERVATION', $basisOfRecords['OBSERVATION'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_PRESERVED_SPECIMEN', $basisOfRecords['PRESERVED_SPECIMEN'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_UNKNOWN', $basisOfRecords['UNKNOWN'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_FOSSIL_SPECIMEN', $basisOfRecords['FOSSIL_SPECIMEN'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_LIVING_SPECIMEN', $basisOfRecords['LIVING_SPECIMEN'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_MACHINE_OBSERVATION', $basisOfRecords['MACHINE_OBSERVATION'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_LITERATURE', $basisOfRecords['LITERATURE'], PDO::PARAM_INT);
		$s->bindValue(':basisOfRecord_MATERIAL_SAMPLE', $basisOfRecords['MATERIAL_SAMPLE'], PDO::PARAM_INT);
		$s->bindValue(':date_NoDate', $dates['NoDate'], PDO::PARAM_INT);
		$s->bindValue(':date_All', $dates['All'], PDO::PARAM_INT);
		$s->bindValue(':date_1970_2020', $dates['INT_1970-2020'], PDO::PARAM_INT);
		$s->bindValue(':date_2010_2020', $dates['INT_2010-2020'], PDO::PARAM_INT);

		if ($s->execute())
		{
			return true;
		}
		else 
		{
			//$d=$s->errorInfo();
			//$errors[]=$d[2];
			//$j++;
		}

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
			
			set_time_limit(3600);
			
			if (is_null($content))
			{
				// isn't JSON, we assume it's not GBIF
				$content=rawToStructured($raw);

				$provider=extractProvider(['conn'=>$conn,'line'=>$content[0]]);
				$keycolumn=extractKeyColumn(['conn'=>$conn,'line'=>$content[1]]);
				
				
				
				if (empty($provider))
				{
					$errors[]="no or illegal provider";
				} else
				if (empty($keycolumn))
				{
					$errors[]="no key";
				}
				else
				{
					//deletePreviousMatches(['conn'=>$conn,'provider'=>$provider]);
					$added=$failed=0;
					foreach($content as $line)
					{	
						$taxon=resolveTaxon(['conn'=>$conn,'code'=>$code,'line'=>$line]);
						//echo '<pre>';print_r($line);print_r($taxon);echo '</pre>';
						if (isset($taxon->checklist_species_id))
						{
							if(saveTaxonMatch(['conn'=>$conn,'provider'=>$provider,'taxon'=>$taxon,'line'=>$line,'keycolumn'=>$keycolumn]))
							{
								$added++;
							}
							else
							{
								$failed++;
							}
						}
						else
						{
						}
					}
					$res=array($added,$failed);
				}
			}
			else
			{
				// is JSON, we assume it's GBIF
				//echo '<pre>';print_r($content);echo '</pre>';
				$added=$failed=0;
				foreach($content['results'] as $line)
				{
					$taxon=resolveTaxon(['conn'=>$conn,'code'=>$code,'sp_code'=>$line['sourceTaxonId']]);
					//echo '<pre>';print_r($line);print_r($taxon);echo '</pre>';
					if (isset($taxon->checklist_species_id))
					{
						deletePreviousGBIFCounts(['conn'=>$conn,'taxon'=>$taxon]);
						if(saveGBIFCounts(['conn'=>$conn,'taxon'=>$taxon,'basisOfRecords'=>$line['basisOfRecords'],'dates'=>$line['dates'],'taxonKey'=>$line['taxonKey']]))
						{
							$added++;
						}
						else
						{
							$failed++;
						}
					}
					else
					{
					}

					$res=array($added,$failed);

				}
				
			}
		}
		else
		{
			$errors[]="file doesn't exist";
		}
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

	if (@$res)
	{
		echo '<div>',sprintf("saved %s, failed %s lines.",$res[0],$res[1]),'</div>';
	}
	if (!empty($errors))
	{
		echo 'errors:';
		echo '<pre>';
		print_r($errors);
		echo '</pre>';
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
