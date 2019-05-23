<?php
require_once __DIR__ . '/functions.php';

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

    $sql = "SELECT pseudo_val ,original_data, fieldtype FROM tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    
    while ($row = $rec -> fetch(PDO::FETCH_ASSOC)) {
        $pseudo_list[$row['pseudo_val']] = array('original_data'=>$row['original_data'], 'fieldtype'=>$row['fieldtype']);
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

        $pseudo_val = $key;
        $original_data=$value['original_data']; 
        $fieldtype=$value['fieldtype'];

        $sql = "INSERT INTO tcat_pseudonymized_data (pseudo_val, original_data, fieldtype) VALUES (?, ?, ?);";
        $stmt = $dbh -> prepare($sql);
        $stmt->execute([$pseudo_val, $original_data, $fieldtype]);        
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

function pseudonymize_field($pseudo_list, $data, $datakey, $last_pseudo_index){
    if (array_key_exists($datakey, $data) && ($data[$datakey] != NULL)) {
        $mask = array_search($data[$datakey],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data[$datakey] = $mask;
        } else {
            $newData = array(
                'original_data' => $data[$datakey],
                'fieldtype' => $datakey
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[$datakey]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    $argument_array = array(
        'last_pseudo_index' => $last_pseudo_index,
        'data' => $data,
        'pseudo_list' => $pseudo_list 
    );
    return $argument_array;
}

function pseudonymize($data) {  
    $pseudo_list = fetch_pseudonymized_data();
    end($pseudo_list);
    $insert_start_value = $last_pseudo_index = key($pseudo_list);
    // If the key to the $data is in the $key_array below, then it will be handled by the foreach.
    $key_array=array('id', 'tweetid', 'id_string', 'from_user_id', 'from_user_name', 'from_user_realname', 'user_from_name', 'user_from_id', 'user_to_id', 'user_to_name', 'to_user_id', 'to_user_name', 'in_reply_to_status_id', 'in_reply_to_status_id_str', 'in_reply_to_user_id', 'in_reply_to_screen_name', 'quoted_status_id', 'retweeted_status', 'retweeted', 'retweet_id' );
    foreach ($data as $key => $value) {
        if (array_key_exists($key, $data) && ($value != NULL) && in_array($key, $key_array)) {
            $argument_array =pseudonymize_field($pseudo_list, $data, $key, $last_pseudo_index);
            $last_pseudo_index = $argument_array['last_pseudo_index'];
            $data = $argument_array['data'];
            $pseudo_list = $argument_array['pseudo_list']; 
            }        
    }
    if (array_key_exists('from_user_profile_image_url', $data) && ($data['from_user_profile_image_url'] != NULL)) {
        $data['from_user_profile_image_url'] = "Omitted, see original table";
    }
    if (array_key_exists('from_user_url', $data) && ($data['from_user_url'] != NULL)) {
        $data['from_user_url'] = "Omitted, see original table";
    }
    // Check if there exists any key for the $data array and that key isn't null.
    if (array_key_exists('text', $data) && ($data['text'] != NULL)) {
        $regexp = '/([@][\w_-]+)/';
        // Search for all occurrences of the regexp in the data['text'] field. Return the matching strings in the array $matches
        $matches = array();
        preg_match_all($regexp, $data['text'], $matches);
        // For every match given in the $matches array.
        foreach ($matches[0] as $key => $value) {
            // Search if the match(now saved as $value) is already pseudonymized.
            $mask = array_search($value, array_column($pseudo_list, 'original_data' ));
            // If it is pseudonymized, then use the already existing pseudonymization key again.
            if ($mask) {
                    $data['text'] = str_replace($value, "@".($last_pseudo_index), $data['text'])    ;            
                } else {
                    // If not already existing in the pseudonymisation table, add it and psseudonymize it.
                    $newData = array(
                        'original_data' => $value,
                        'fieldtype' => 'Mention in text'
                    );
                    $pseudo_list[($last_pseudo_index+1)] = $newData;
                    $data['text'] = str_replace($value, "@".($last_pseudo_index+1), $data['text']);         
                    $last_pseudo_index = ($last_pseudo_index+1);
                }
            }
    }
    save_pseudonymized_data($pseudo_list, $insert_start_value , $last_pseudo_index);
    return $data;   
}
	
	
	
