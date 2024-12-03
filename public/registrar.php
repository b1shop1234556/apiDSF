<?php
require 'config.php';
$prodquery = "SELECT * FROM faculty";
$data = array();
$result = $conn->query($prodquery);
while($row = $result->fetch_object()){
    array_push($data,$row);
}


echo json_encode($data);


?>