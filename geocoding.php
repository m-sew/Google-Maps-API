<?php

global $dbReadOnly;
global $db;

$array = array();

$sql = "SELECT * FROM locations";

if ($result = $dbReadOnly->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $array[] = $row;
        }
    }
    $res = true;
} else {
    $res = false;
}

$count = 1;

$ranoutofrequests = "";

foreach ($array as $arr) {
    $address = str_replace(" ", "+", $arr['locationname']);
    $locationid = $arr['locationid'];

    $geocodeGoogle = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $address . "&key=[YOUR KEY HERE]");
    $outputGoogle = json_decode($geocodeGoogle, true);

    if ($outputGoogle['status'] == "OK") {
        $lat = $outputGoogle['results'][0]['geometry']['location']['lat'];
        $lon = $outputGoogle['results'][0]['geometry']['location']['lng'];
		
		
            $sqlUpdate = "UPDATE vend1.`locations` SET lat = ".$lat.", lon = ".$lon." WHERE locationid = ".$locationid." ";
     
        echo $count . " Sucess via Google!! Lat:" . $lat . " Lon:" . $lon . "\r\n";
    } else if ($outputGoogle['status'] != "OK") {
        $ranoutofrequests .= $locationid . ", ";
        if ($outputGoogle['status'] == 'ZERO_RESULTS') {
            $sqlUpdate = "UPDATE vend1.`locations` SET lat = 0, lon = 0 WHERE locationid = ".$locationid." ";
        }
        echo $count . " Fail Google! - " . $outputGoogle['status'] . "\r\n";
    } else {
         $sqlUpdate = "";
        echo $count . " Fail Google!\r\n";
    }
 
        if ($sqlUpdate != "") {
            $db->query($sqlUpdate);
        }
    $count++;
}