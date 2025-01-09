<?
#$filename = date('YmdHis') . '.txt';

$filename = file_get_contents('LastFileName.txt') . '_res';

header('Content-disposition: attachment; filename=' . $filename);

$file = 'output/output.txt';

echo file_get_contents($file);

?>