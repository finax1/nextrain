<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if (empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at/login");
    die();
}
include 'master.php';
date_default_timezone_set('Europe/Vienna');
$query = $_POST['q'];
$countDigitQ = countDigits($query);
$countLettersQ = strlen($query)-$countDigitQ;
if($countLettersQ<1 && $countDigitQ>1 && $countDigitQ<6){
    $zugid= getZugid($query);
    $link = 'https://nextrain.finax1.at/lt/'.encrypt($zugid);
    if($zugid == "error"){
            $link='https://nextrain.finax1.at/error/train';
    }
}
elseif($countDigitQ<1 && $countLettersQ>0){
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $statement = $pdo->prepare('SELECT * FROM stations WHERE levenshtein(:station, name) BETWEEN 0 AND 9 ORDER BY levenshtein(:station, name)');
    $statement->execute(array('station' => preg_replace('/[0-9]+/', '', $query)));
    $res = $statement->fetch(PDO::FETCH_ASSOC);
    $link = 'https://nextrain.finax1.at/ls/'.encrypt($res['nummer']);
}
elseif($countDigitQ<5 && $countDigitQ>0&& $countLettersQ>0){
    preg_match("^.*?(?=\d)", preg_replace('/[0-9]+/', '', $query), $station);
    $uhrzeit = preg_replace("/[^0-9]/","",$query);
    if(strlen($uhrzeit)>3&&(int)substr($uhrzeit,0,2)<24&&(int)substr($uhrzeit,2,2)<60){
        $uhrzeit=substr($uhrzeit,0,2).":".substr($uhrzeit,2,2);
    }else if(strlen($uhrzeit)>2&&(int)substr($uhrzeit,1,2)<60) {
        $uhrzeit = "0" . substr($uhrzeit, 0, 1) . ":" . substr($uhrzeit, 1, 2);
    }else if(strlen($uhrzeit)==1||strlen($uhrzeit)==2) {
        $uhrzeit = date('H:i:s');
    }else{
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
        $statement = $pdo->prepare('SELECT * FROM stations WHERE levenshtein(:station, name) BETWEEN 0 AND 9 ORDER BY levenshtein(:station, name)');
        $statement->execute(array('station' => preg_replace('/[0-9]+/', '', $query)));
        $res = $statement->fetch(PDO::FETCH_ASSOC);
        $link = 'https://nextrain.finax1.at/ls/'.encrypt($res['nummer']);
        header("Location: ".$link);
        die();
    }
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $statement = $pdo->prepare('SELECT * FROM stations WHERE levenshtein(:station, name) BETWEEN 0 AND 9 ORDER BY levenshtein(:station, name)');
    $statement->execute(array('station' => preg_replace('/[0-9]+/', '', $query)));
    $res = $statement->fetch(PDO::FETCH_ASSOC);
    $link = 'https://nextrain.finax1.at/ls/'.encrypt($res['nummer']."---".$uhrzeit);
}
else{
    $link = 'https://nextrain.finax1.at/error/search';
}
header("Location: ".$link);
die();
?>