<?php

	include_once 'includes/config.php';
	include_once 'includes/base-functions.php';

	function getChecklistSpecies($p)
	{
		$conn=isset($p['conn']) ? $p['conn'] : null;
		$code=isset($p['code']) ? $p['code'] : null;
		$start=isset($p['start']) ? (int) $p['start'] : 0;
		$per_page=isset($p['per_page']) ? (int)$p['per_page'] : SPECIES_PER_PAGE;

		$s = $conn->prepare('select SQL_CALC_FOUND_ROWS * from checklist_species where checklist_code = :code LIMIT :limit OFFSET :offset');
		$s->bindValue(':code', $code, PDO::PARAM_STR);
		$s->bindValue(':limit', $per_page, PDO::PARAM_INT);
		$s->bindValue(':offset', $start, PDO::PARAM_INT);
		$s->execute();

		$obj = new StdClass;
		$obj->totalcount=$conn->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
		$obj->data=$s->fetchAll();
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

<p>
	<h4>Checklist <?php echo '"',$code, '" (',$results->totalcount,' records)'; ?></h4>
    
<?php

	print_paginator($results);

?>
    
    <table>
        <thead>
            <tr>
                <th>Taxon</th>
                <th>Code</th>
                <th>Something else</th>
            </tr>
        </thead> 
        <tbody>   
<?php

	foreach((array)$results->data as $key=>$val)
	{
		echo 
			'<tr>',
				'<td>','<a href="'.$val['checklist_species_key'].'" target="_detail">',$val['scientific_name'],'</a>','</td>',
			'</tr>',chr(10);
	}

?>
		</tbody> 
	</table>
</p>
<?php

	print_paginator($results);

?>
<p>
	<a href="index.php">index</a>
</p>

</body>
</html>
