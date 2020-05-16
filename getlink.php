<?php
session_start();
$mylink = base64_decode(urldecode($_GET['link']));
$uniqid = uniqid('nex', true);
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
$statement = $pdo->prepare("INSERT INTO share (shortlink, reallink, lastAccess) VALUES (:shortlink, :reallink, :lastAccess)");
$result = $statement->execute(array('shortlink' => $uniqid, 'reallink' => $mylink, 'lastAccess' => date("Y-m-d H:i:s")));
echo "https://nextrain.finax1.at/share/" . $uniqid;
?>