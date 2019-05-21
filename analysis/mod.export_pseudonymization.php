<?php
require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/CSV.class.php';
require_once __DIR__ . '/common/pseudonymization.php';

$dbh = pdo_connect();
pdo_unbuffered($dbh);
$stream_to_open  =  export_start("Pseudonymization table", 'csv');

$csv = new CSV($stream_to_open, 'csv');
$csv->writeheader(array('pseudo_val', 'original_data', 'fieldtype'));

$sql = "SELECT * FROM tcat_pseudonymized_data;";

$rec = $dbh -> prepare($sql);
$rec -> execute();

while ($data = $rec->fetch(PDO::FETCH_ASSOC)) {
    
    $csv->newrow();
    $csv->addfield($data['pseudo_val'], 'integer');
    $csv->addfield($data['original_data'], 'string');
    $csv->addfield($data['fieldtype'], 'string');
    $csv->writerow();    
}
$csv->close();

