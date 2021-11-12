<?php
$conn = mysqli_connect("localhost","root","","asteriskcdrdb");

$f = fopen("foreigncallstob24.log","a");
date_default_timezone_set('Europe/Moscow');
fputs($f,date('l jS \of F Y h:i:s A')." Start...\n");
if (!$conn) die("Err mysqli_connect\n");
$ret = mysqli_query($conn,"select * from cdr where UNIX_TIMESTAMP()-UNIX_TIMESTAMP(calldate) <= 660");//DATEDIFF(NOW(),calldate)<1");
//print_r($ret);die();
//if ($ret !== TRUE) die("Err query\n");
$msg = "";
$row = null;
$num=0;
while($row = mysqli_fetch_row($ret)) {
if (($row[16]=='000154100') || ($row[16]=="000149939") || ($row[16]=="509120") || ($row[16]=='000109120')) {
$msg .= "Call from ".$row[2]." on DID ".$row[16].".".$row[11].".Date: ".$row[0]."\n";
$num = $num + 1;
}
}
fputs($f,$msg);
print_r($msg);
print_r("\nCount: ".$num."\n");

if (!$msg) {fputs($f,"Count: 0\nFinish\n\n");die();}
$queryURL = "https://corp.informunity.ru/rest/524/k3uo5r3p1n0pmghl/im.message.add.json";
//$queryURL = "https://informunity.bitrix24.com/rest/34/pqsslf5s30oovznd/im.notify.json";
//$queryData = http_build_query(array("to"=>"34","message"=>$msg));
$queryData = http_build_query(array("DIALOG_ID"=>"chat30514","MESSAGE"=>$msg,"SYSTEM"=>"N"));
$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_SSL_VERIFYPEER => 0,
	CURLOPT_POST => 1,
	CURLOPT_HEADER => 0,
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_URL => $queryURL,
	CURLOPT_POSTFIELDS => $queryData,
));
$result = curl_exec($curl);
if (!$result) die("Err CURL\n");
curl_close($curl);
$result = json_decode($result,1);
if(array_key_exists('error', $result)) {
fputs($f,"Count: ".$num."\nNot sent\nFinish\n\n");
die("Err send msg: ".$result['error_description']."\n");
}
echo "\n\nMessage have been sent.";
mysqli_close($conn);
fputs($f,"Count: ".$num."\nOK\nFinish\n\n");
fclose($f);
?>

