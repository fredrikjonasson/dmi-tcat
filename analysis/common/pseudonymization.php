<?php
require __DIR__ . '/functions.php';


function is_pseudonymized() {
	$sql = "SELECT pseudonymization FROM tcat_query_bins WHERE querybin =".$dataset;
	var_dump(serialize($sql));	
	}
