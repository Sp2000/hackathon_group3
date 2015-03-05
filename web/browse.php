<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';

	function getChecklistSpecies($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		$start=isset($p['start']) ? (int) $p['start'] : 0;
		$per_page=isset($p['per_page']) ? (int)$p['per_page'] : SPECIES_PER_PAGE;

		$s = $conn->prepare("
			select
				SQL_CALC_FOUND_ROWS 
				_a.checklist_species_key as _checklist_species_key,
				_a.scientific_name as _scientific_name,
			if(count(_b.id)=0,'-','Y') as `PRESENT GBIF`,
			if(count(_d.id)=0,'-','Y') as `PRESENT CoL`,
				(_c.basisOfRecord_HUMAN_OBSERVATION +
				_c.basisOfRecord_OBSERVATION +
				_c.basisOfRecord_PRESERVED_SPECIMEN +
				_c.basisOfRecord_UNKNOWN +
				_c.basisOfRecord_FOSSIL_SPECIMEN +
				_c.basisOfRecord_LIVING_SPECIMEN +
				_c.basisOfRecord_MACHINE_OBSERVATION +
				_c.basisOfRecord_LITERATURE +
				_c.basisOfRecord_MATERIAL_SAMPLE) as `TOTAL OCCURRENCES`,

				_c.basisOfRecord_HUMAN_OBSERVATION 		as `BoR HUMAN OBSERVATION`,
				_c.basisOfRecord_OBSERVATION 			as `BoR OBSERVATION`,
				_c.basisOfRecord_PRESERVED_SPECIMEN 	as `BoR PRESERVED SPECIMEN`,
				_c.basisOfRecord_UNKNOWN 				as `BoR UNKNOWN`,
				_c.basisOfRecord_FOSSIL_SPECIMEN 		as `BoR FOSSIL SPECIMEN`,
				_c.basisOfRecord_LIVING_SPECIMEN 		as `BoR LIVING SPECIMEN`,
				_c.basisOfRecord_MACHINE_OBSERVATION 	as `BoR MACHINE OBSERVATION`,
				_c.basisOfRecord_LITERATURE 			as `BoR LITERATURE`,
				_c.basisOfRecord_MATERIAL_SAMPLE 		as `BoRI MATERIAL SAMPLE`,

				_c.date_NoDate as `no date`,
				_c.date_All as `all dates`,
				_c.date_1970_2020 as `1970-2020`,
				_c.date_2010_2020 as `2010-2020`
					
			from 
				checklist_species _a

			left join checklist_matches _b 
				on _a.id=_b.checklist_species_id
				and _b.match_provider='GBIF'

			left join checklist_matches _d 
				on _a.id=_d.checklist_species_id
				and _d.match_provider='CoL'

			left join checklist_gbif_matches _c
				on _a.id=_c.checklist_species_id
			where 
				_a.checklist_code = :code 
			group by
				_a.id
			LIMIT :limit 
			OFFSET :offset
		");
		$s->bindValue(':code', $code, PDO::PARAM_STR);
		$s->bindValue(':limit', $per_page, PDO::PARAM_INT);
		$s->bindValue(':offset', $start, PDO::PARAM_INT);
		$s->execute();

		$obj = new StdClass;
		$obj->totalcount=$conn->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
		$obj->data=$s->fetchAll(PDO::FETCH_CLASS);
		$obj->code=$code;
		$obj->start=$start;
		$obj->per_page=$per_page;

		return $obj;
	}
	
	function print_paginator($results)
	{
		$pages=ceil($results->totalcount/$results->per_page);
		$current=($results->start/$results->per_page);
		$prev=1;
		
		echo '<div class="paginator">','pages: ';
		
		for($i=0;$i<$pages;$i++)
		{
			if ($i==0 || ($i>$current-2 && $i<$current+2) || $i==$pages-1)
			{
				if ($i==($pages-1) && $current<($pages-3)) echo '....';
				if ($i==$current)
				{
					echo '<span class="page">'.($i+1),'</span>';
				}
				else
				{
					echo '<a class="page" href="?code='.$results->code.'&start='.($i*$results->per_page).'">',($i+1),'</a>';
				}				
				if ($i==0 && $current>2) echo '....';
			}
		}
		
		echo '</div>';
	}
	


	if (isset($_REQUEST["code"]))
	{
		$code=$_REQUEST["code"];
		$start=isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
		$conn=connectDb();
		$results=getChecklistSpecies(['conn'=>$conn,'code'=>$code,'start'=>$start]);
	}
	
	include_once 'includes/html-header.php';

?>
<style>
table, tr, th, td {
	font-size:0.9em;
}

.markdown-body table th, .markdown-body table td {
    border: 1px solid #ddd;
    padding: 3px 6px;
}

.markdown-body table td {
	text-align:right;
}

.markdown-body table tr:hover {
	background-color:#CFF;
}

</style>

<p class="nav-menu">
	<a href="index.php">index</a>
</p>    
<p>
	<h4>Checklist <?php echo '"',$code, '" (',$results->totalcount,' records)'; ?></h4>
    <p class="nav-menu">
        <a href="addmatch.php?code=<?php echo $code; ?>">add match data</a>
    </p>  
<?php

	print_paginator($results);

?>
    
    <table>
        <thead>
            <tr>
                <th>Taxon</th>
<?php

	foreach((array)$results->data[0] as $key=>$val)
	{
		if (strpos($key,'_')===0) continue;
		echo '<th>',str_replace(array('_'),array(' '),$key),'</th>',chr(10);
	}

?>
            </tr>
        </thead> 
        <tbody>   
<?php

	foreach((array)$results->data as $key=>$val)
	{
		echo 
			'<tr>',
				'<td style="text-align:left">','<a href="'.$val->_checklist_species_key.'" target="_detail">',$val->_scientific_name,'</a>','</td>';
				
				foreach((array)$val as $key=>$bla)
				{
					if (strpos($key,'_')===0) continue;
					echo '<td>',$bla,'</td>',chr(10);
				}
				
		echo '</tr>',chr(10);


	}

?>
		</tbody> 
	</table>
</p>
<?php

	print_paginator($results);

?>


</body>
</html>
