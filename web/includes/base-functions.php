<?php

	function connectDb()
	{
		$conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PASS);
		return $conn;
	}