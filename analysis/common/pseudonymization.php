<?php
require __DIR__ . '/functions.php';
//Global
$pseudo_list = array();

function fetch_pseudonymized_data(){
    global $pseudo_list;
    
    //Fetching the pseudonymize table and buffer it in an array.
    $dbh = pdo_connect();
    // Check if the pseudonymization table is empty.
    $sql = "SELECT COUNT(pseudo_val) from tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    $is_empty = $rec -> fetch(PDO::FETCH_NUM);
    
    // If not empty, store a copy of the table in $pseudo_list
    if ($is_empty != "0") {
    $sql = "SELECT * FROM tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $pseudo_list = $rec->fetch(PDO::FETCH_ASSOC);
    }
    // Close the database connection before returning.
    $dbh = NULL;
    return $pseudo_list;
}

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

// The functions adds a value to the pseudonynmiszation datatable, and add the corresponding value to the other.
function pseudonymize_to_table($data, $key){
    global $pseudo_list;
    // IF the ID is not pseudonymized, add it as a new entry in the pseudonymization table with the existing key+1.
    end($pseudo_list);
    $mask = key($pseudo_list);
    if ($mask != NULL) {
    $pseudo_list[($mask+1)] = $data[$key];
    $data[$key] = ($mask+1);    
    } else {
    $pseudo_list[($mask+1)] = $data[$key];
    $data[$key] = ($mask+1);
    }
    return $data;
}
        
function pseudonymize_field($data, $key) {
    global $pseudo_list;
    
    if (array_key_exists($key, $data)) {
        //Checking if the actual $key already is Pseudonymized.
        $mask = array_search($data[$key], $pseudo_list);
        if ($mask) {
            $data[$key] = $mask;
        } else {
            $data = pseudonymize_to_table($data, $key);    
            }
        }
        return $data;
    }    


function pseudonymize($data) {  
    global $pseudo_list;
    $pseudo_list = fetch_pseudonymized_data();
    
    if (array_key_exists('id', $data)) {
        $key = 'id';
        $data = pseudonymize_field($data, $key);
/*
    if (array_key_exists('id_string', $data)) {
        $key = 'id_string';
        pseudonymize_field($data, $key);

         * $data['id_string'] = "gospongo";
    }
    if (array_key_exists('from_user_id', $data)) {
        $data['from_user_id'] = "gospongo";
    }
    if (array_key_exists('from_user_name', $data)) {
        $data['from_user_name'] = "gospongo";
    }
    if (array_key_exists('from_user_realname', $data)) {
        $data['from_user_realname'] = "gospongo";
    }
    if (array_key_exists('to_user_id', $data)) {
        $data['to_user_id'] = 1337;
    }
    if (array_key_exists('to_user_name', $data)) {
        $data['to_user_name'] = 1337;
    }
    if (array_key_exists('in_reply_to_status_id', $data)) {
        $data['in_reply_to_status_id'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_status_id_str', $data)) {
        $data['in_reply_to_status_id_str'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_user_id', $data)) {
        $data['in_reply_to_user_id'] = "gospongo";
    }
    if (array_key_exists('in_reply_to_screen_name', $data)) {
        $data['in_reply_to_screen_name'] = 1337;
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
    }*/
    return $data;
}
	
	
	
	
	
	
