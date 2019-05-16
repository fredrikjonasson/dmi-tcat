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
    if (array_key_exists('id_str', $data)) {
        $data['id_str'] = "gospongo";
    }
    if (array_key_exists('from_user_name', $data)) {
        $data['from_user_name'] = "gospongo";
    }
    if (array_key_exists('from_user_id', $data)) {
        $data['from_user_id'] = 1337;
    }
    if (array_key_exists('from_user_name', $data)) {
        $data['from_user_name'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_status_id', $data)) {
        $data['in_reply_to_status_id'] = 1337;
    }
    if (array_key_exists('in_reply_to_status_id_str', $data)) {
        $data['in_reply_to_status_id_str'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_screen_name', $data)) {
        $data['in_reply_to_screen_name'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_user_id', $data)) {
        $data['in_reply_to_user_id'] = 1337;
    }
    if (array_key_exists('from_user_realname', $data)) {
        $data['from_user_realname'] = "gospongo";
    }
    if (array_key_exists('to_user_id', $data)) {
        $data['to_user_id'] = 1337;
    }
    if (array_key_exists('to_user_name', $data)) {
        $data['to_user_name'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_status_id', $data)) {
        $data['in_reply_to_status_id'] = 1337;
    }
    if (array_key_exists('quoted_status_id', $data)) {
        $data['quoted_status_id'] = 1337;
    }
    if (array_key_exists('retweeted_status', $data)) {
        $data['retweeted_status'] = "gospongo";
    }
    if (array_key_exists('retweeted', $data)) {
        $data['retweeted'] = "gospongo";
    }
    if (array_key_exists('retweet_id', $data)) {
        $data['retweet_id'] = 1337;
    }

    return $data;
	}
	
	
	
	
	
	
