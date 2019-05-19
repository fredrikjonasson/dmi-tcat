<?php
require __DIR__ . '/functions.php';

function fetch_pseudonymized_data(){
    $pseudo_list = array();
    //Fetching the pseudonymize table and buffer it in an array.
    $dbh = pdo_connect();
    // Check if the pseudonymization table is empty.zm
    $sql = "SELECT COUNT(pseudo_val) from tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    $is_empty = $rec -> fetch(PDO::FETCH_NUM);
    
    
    // If not empty, store a copy of the table in $pseudo_list
    if ($is_empty != 0) {

    $sql = "SELECT pseudo_val ,original_data FROM tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    
    while ($row = $rec -> fetch(PDO::FETCH_ASSOC)) {
        $pseudo_list[$row['pseudo_val']]=$row['original_data'];
    }

    }
    // Close the database connection before returning.
    $dbh = NULL;
    
    return $pseudo_list;
}

function save_pseudonymized_data($pseudo_list, $insert_start_value ,$last_pseudo_index){
    $dbh = pdo_connect();
    
    // We only want to add the new pseudovalues and keep the old ones without changing the database. 
    $pseudo_list = array_slice($pseudo_list, $insert_start_value, $last_pseudo_index, TRUE);            
    foreach ($pseudo_list as $key => $value) {

        $sql = "INSERT INTO tcat_pseudonymized_data (pseudo_val, original_data) VALUES (?, ?);";
        $stmt = $dbh -> prepare($sql);
        $stmt->execute([$key, $value]);        
    }
  
    $dbh = NULL;
}

// The function queries the database to determine however the chosen dataset is flagged for pseudonymization or not.
function is_pseudonymized($dataset) {	
	$dbh = pdo_connect();
	$sql = "SELECT pseudonymization FROM tcat_query_bins WHERE querybin ='".$dataset."';";
	$rec = $dbh->prepare($sql);
    $rec->execute();
	$boolindicator = $rec -> fetchcolumn();	
	$dbh = NULL;
        
	return $boolindicator;
}

function pseudonymize($data) {  
    $pseudo_list = fetch_pseudonymized_data();
    end($pseudo_list);
    $insert_start_value = $last_pseudo_index = key($pseudo_list);
    
    if (array_key_exists('id', $data) && ($data['id'] != NULL)) {
        $mask = array_search($data['id'], $pseudo_list);
        if ($mask) {
            $data['id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['id'];
            $data['id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('id_string', $data) && ($data['id_string'] != NULL)) {
        $mask = array_search($data['id_string'], $pseudo_list);
        if ($mask) {
            $data['id_string'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['id_string'];
            $data['id_string'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }    
    if (array_key_exists('from_user_id', $data) && ($data['from_user_id'] != NULL)) {
        $mask = array_search($data['from_user_id'], $pseudo_list);
        if ($mask) {
            $data['from_user_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['from_user_id'];
            $data['from_user_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('from_user_name', $data) && ($data['from_user_name'] != NULL)) {
        $mask = array_search($data['from_user_name'], $pseudo_list);
        if ($mask) {
            $data['from_user_name'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['from_user_name'];
            $data['from_user_name'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('from_user_realname', $data) && ($data['from_user_realname'] != NULL)) {
        $mask = array_search($data['from_user_realname'], $pseudo_list);
        if ($mask) {
            $data['from_user_realname'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['from_user_realname'];
            $data['from_user_realname'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('to_user_id', $data) && ($data['to_user_id'] != NULL)) {
        $mask = array_search($data['to_user_id'], $pseudo_list);
        if ($mask) {
            $data['to_user_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['to_user_id'];
            $data['to_user_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('to_user_name', $data) && ($data['to_user_name'] != NULL)) {
        $mask = array_search($data['to_user_name'], $pseudo_list);
        if ($mask) {
            $data['to_user_name'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['to_user_name'];
            $data['to_user_name'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }        
    }
    if (array_key_exists('in_reply_to_status_id', $data) && ($data['in_reply_to_status_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id'], $pseudo_list);
        if ($mask) {
            $data['in_reply_to_status_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['in_reply_to_status_id'];
            $data['in_reply_to_status_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('in_reply_to_status_id_str', $data) && ($data['in_reply_to_status_id_str'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id_str'], $pseudo_list);
        if ($mask) {
            $data['in_reply_to_status_id_str'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['in_reply_to_status_id_str'];
            $data['in_reply_to_status_id_str'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('in_reply_to_user_id', $data) && ($data['in_reply_to_user_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_user_id'], $pseudo_list);
        if ($mask) {
            $data['in_reply_to_user_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['in_reply_to_user_id'];
            $data['in_reply_to_user_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('in_reply_to_screen_name', $data) && ($data['in_reply_to_screen_name'] != NULL)) {
        $mask = array_search($data['in_reply_to_screen_name'], $pseudo_list);
        if ($mask) {
            $data['in_reply_to_screen_name'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['in_reply_to_screen_name'];
            $data['in_reply_to_screen_name'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('to_user_name', $data) && ($data['to_user_name'] != NULL)) {
        $mask = array_search($data['to_user_name'], $pseudo_list);
        if ($mask) {
            $data['to_user_name'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['to_user_name'];
            $data['to_user_name'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('in_reply_to_status_id', $data) && ($data['in_reply_to_status_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id'], $pseudo_list);
        if ($mask) {
            $data['in_reply_to_status_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['in_reply_to_status_id'];
            $data['in_reply_to_status_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('quoted_status_id', $data) && ($data['quoted_status_id'] != NULL)) {
        $mask = array_search($data['quoted_status_id'], $pseudo_list);
        if ($mask) {
            $data['quoted_status_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['quoted_status_id'];
            $data['quoted_status_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('retweeted_status', $data) && ($data['retweeted_status'] != NULL)) {
        $mask = array_search($data['retweeted_status'], $pseudo_list);
        if ($mask) {
            $data['retweeted_status'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['retweeted_status'];
            $data['retweeted_status'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('retweeted', $data) && ($data['retweeted'] != NULL)) {
        $mask = array_search($data['retweeted'], $pseudo_list);
        if ($mask) {
            $data['retweeted'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['retweeted'];
            $data['retweeted'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    if (array_key_exists('retweet_id', $data) && ($data['retweet_id'] != NULL)) {
        $mask = array_search($data['retweet_id'], $pseudo_list);
        if ($mask) {
            $data['retweet_id'] = $mask;
        } else {
            $pseudo_list[($last_pseudo_index+1)] = $data['retweet_id'];
            $data['retweet_id'] = ($last_pseudo_index+1);
            $last_pseudo_index += 1;
        }
    }
    save_pseudonymized_data($pseudo_list, $insert_start_value ,$last_pseudo_index);
    return $data;
}
	
	
	
