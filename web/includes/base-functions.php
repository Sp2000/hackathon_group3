<?php

	function rawToStructured($raw,$field_sep="\t")
	{
		$d=explode(chr(13),str_replace(array(chr(13).chr(10),chr(10)),chr(13),$raw));
		foreach($d as $key=>$val)
		{
			$d[$key]=explode($field_sep,$val);
		}
		return $d;
	}

	function connectDb()
	{
		$conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PASS);
		return $conn;
	}