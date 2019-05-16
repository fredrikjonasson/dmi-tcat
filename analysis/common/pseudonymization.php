<?php
require __DIR__ . '/functions.php';

// The function queries the database to determine however the chosen dataset is flagged for pseudonymization or not.
function is_pseudonymized($dataset) {
	
	$dbh = pdo_connect();
	$sql = "SELECT pseudonymization FROM tcat_query_bins WHERE querybin ='".$dataset."';";
	$rec = $dbh->prepare($sql);
    $rec->execute();
	$boolindicator = $rec -> fetch(PDO::FETCH_NUM);	
	$dbh = NULL;
        
	return $boolindicator;
	}

function pseudonymize($data) {
    
    if (array_key_exists('id', $data)) {
        $data['id'] = 1337;
    }
    if (array_key_exists('fenix', $data)) {
        $data['fenix'] = "gospongo";
    } // List options for all your keys here.
    
    return $data;
	}
	
	
	
	
	
	
