<?
require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/CSV.class.php';
require_once __DIR__ . '/common/pseudonymization.php';

// Dirty
$send_text = "simple";
$file = 'simplesanity.txt';
file_put_contents($file, $send_text);

$dbh = pdo_connect();
pdo_unbuffered($dbh);
$stream_to_open  =  export_start("Pseudonymization table", 'csv');

$csv = new CSV($stream_to_open, 'csv');

$sql = "SELECT * FROM tcat_pseudonymized_data;";

$rec = $dbh -> prepare($sql);
$rec -> execute();

while ($data = $rec->fetch(PDO::FETCH_ASSOC)) {
    // Dirty
    $send_text = serialize($t);
    $file = 'pseudomodtest.txt';
    file_put_contents($file, $send_text);
    break;
}