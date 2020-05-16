<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if (empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at/login");
    die();
}
include 'master.php';
$query = $_GET['q'];
$postStation = $_POST['station'];
$trainid=decrypt($query);
$query=getZugName($trainid);
$istSbahn =false;
$sbahnnr="";
$ersteStation = "";
$letzteStation = "";
$mittlereStation = "";
if(errortrue($trainid)){
    header('Location: https://nextrain.finax1.at/error/train');
    die;
}
if(substr($query, 0,1)=="S"){
    $istSbahn=true;
    $sbahnnr=zugNrSbahn($trainid);
    $query = str_replace(' ', '', $query);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>NexTRAIN<?php
        echo " - ".$query;
        ?></title>
    <link rel="stylesheet" type="text/css" href="https://nextrain.finax1.at/css/css.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#b20000">
    <link rel="apple-touch-icon" sizes="180x180" href="https://nextrain.finax1.at/cdn/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://nextrain.finax1.at/cdn/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://nextrain.finax1.at/cdn/icons/favicon-16x16.png">
    <script src="https://code.jquery.com/jquery-3.4.1.js"></script>
    <script src="https://nextrain.finax1.at/js/main.js" defer></script>
</head>
<body>
<div id="mobileonly">
    <h2>
        <span style="font-family: Michroma, sans-serif; font-size: 2.3em; -moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on" onselectstart="return false;" onmousedown="return false;">NexTRAIN</span> ist derzeit nur als mobile-App auf deinem Smartphone verfügbar
    </h2>
</div>
<div id="loader">
    <div class="train">
        <div class="windows"></div>
        <div class="lights"></div>
    </div>
    <div class="rails">
        <div class="ties"></div>
        <div class="ties"></div>
        <div class="ties"></div>
    </div>
</div>
<div id="backgr" class="opacityable">
    <div id="headerd">
        <div id="liveuhr">
            <span id="clock"></span>
        </div>
        <div id="logo"  style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
             unselectable="on"
             onselectstart="return false;"
             onmousedown="return false;"><a href="https://nextrain.finax1.at">NexTRAIN</a>
        </div>
    </div>
    <div id="search">
        <form id="searchform" action="https://nextrain.finax1.at/q" method="post">
            <input type="search" id="inputfield" name="q" placeholder="Suchen..." autocomplete="false">
        </form>
    </div>
</div>
<div id="infodiv" class="opacityable">
    <div id="icon"><?php
        echo '<img alt="infoicon" src="https://nextrain.finax1.at/cdn/icons/train.png" class="infoicon unselectable">';
        ?></div>
    <div id="fullName">
        <?php
        if($istSbahn){
            echo $query." (Zug Nr. ".zugNrSbahn($trainid).")";
        }else {
            echo $query;
        }
        ?>
    </div>
</div>
<div id="NXTresult" class="opacityable">
    <?php
    $echoVar = '<table>';
    $i = 0;
    $found = false;
    $jsondec = json_decode(zuglauf($trainid, date("d.m.Y")), true);
    $ersteStation = $jsondec[9]['stationid'];
    $letzteStation = $jsondec[count($jsondec)-1]['stationid'];
    $mittlereStation = $jsondec[abs(count($jsondec)/2)]['stationid'];
    foreach ($jsondec as $elms) {
        if(!$found){
            $clockH = date('H');
            $clockM = date('i');
            if(preg_match_all( "/[0-9]/", $elms['abfahrtEst'])>0){
                $station = $elms['abfahrtEst'];
            }else{
                $station = $elms['abfahrt'];
            }
            try {
                $aktuellClock = new DateTime(preg_replace('/^\p{Z}+|\p{Z}+$/u', '',$clockH . ":" . $clockM));
                $stationClock = new DateTime(preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $station));
            } catch (Exception $e) {
            }
            if($aktuellClock<$stationClock){
                $echoVar.="<tr id='lthr'><td colspan='6'></td></tr>";
                $found=true;
            }
        }
        if(intval($elms['stationid']) == intval(getStationid($postStation))){
            $bahnsteig = $elms['bahnsteig'];
        }
        if ($i % 2 == 0) {
            $echoVar .= '<tr class="zebra1">';
        } else {
            $echoVar .= '<tr class="zebra2">';
        }
        $echoVar .= '<td class="stations"><a href="https://nextrain.finax1.at/ls/';
        $encrypturl= $elms['stationid']."---";
        if(preg_match_all( "/[0-9]/", $elms['ankunft'])==4){
            $encrypturl.=trim($elms['ankunft']);
        }else{
            $encrypturl.=trim($elms['abfahrt']);
        }
        $encrypturl.="---".preg_replace("/[^0-9]/","", $query)."---".$elms['abfahrt'];
        if(preg_match_all( "/[0-9]/", $elms['abfahrtEst'])>1){
            $encrypturl.="---".trim($elms['ankunftEst']);
        }
        $encrypturl=encrypt($encrypturl);
        $echoVar.= $encrypturl.'">' . $elms['station'] . '</a></td><td class="arrivals">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunft'])).'<br><span class="arrivalsEst">';
        if(stripos($elms['ankunftEst'],'erroricon')!=false){
            $echoVar.=str_replace('&nbsp;', '', $elms['ankunftEst']);
        }else{
            $echoVar.=str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunftEst']));
        }
        $echoVar.= '</span></td><td class="departure">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['abfahrt'])) . '<br><span class="departureEst">';
        if(stripos($elms['abfahrtEst'],'erroricon')!=false){
            $echoVar.=str_replace('&nbsp;', '', $elms['abfahrtEst']);
        }
        else {
            $echoVar .= str_replace('&nbsp;', '', preg_replace('/\s+/', '', $elms['abfahrtEst']));
        }
        $echoVar.= '</span></td><td class="platforms">' . $elms['bahnsteig'] . '</td>';
        $i++;
    }
    $echoVar .= "</table>";
    echo str_replace('&nbsp;', '',$echoVar);
    ?>
</div>
<script type="text/javascript">
    $("#lthr").prevAll().addClass("over");
    $("td").html(function (i, html) {
        return html.replace(/&nbsp;/g, ' ');
    });
</script>
<div id="specialMessages" class="opacityable">
    <?php
    try {
        echo newsTrain($trainid);
    }catch (\Throwable $t){

    }
    ?>
</div>
    <?php

        if($istSbahn){
            $zugnr = $sbahnnr;
        }
        else{
            $zugnr = preg_replace("/[^0-9]/", "", $query);
        }
        $json = file_get_contents("https://live.oebb.at/web/api/train/".$zugnr."/departureDay/".date("Y-m-d")."/departureStation/".$mittlereStation);
        $json = file_get_contents("https://live.oebb.at/web/api/train/".$zugnr."/departureDay/".date("Y-m-d")."/departureStation/".$mittlereStation);
        $json = json_decode($json, true);
        if($json == NULL){
            echo "<div id='garnitur' class='opacityable'>";
            if(!$istSbahn) {
                echo wagenreihung(preg_replace("/[^0-9]/", "", $query));
            }else{
                echo wagenreihung($sbahnnr);
            }
            echo "</div>";
        }
        else{
            $stationStart = json_decode(file_get_contents("https://live.oebb.at/web/api/eva/".$postStation), true)['DB640'];
            $stationEnd = json_decode(file_get_contents("https://live.oebb.at/web/api/eva/".$letzteStation), true)['DB640'];
            echo "<div id='wagenreihung' class='opacityable'><div id='bahnsteig'></div>";
            foreach ($json['wagons'] as $wagerl){
                $reihe = substr($wagerl['uicNummer'], 4,4);
                echo "<div class='wagerl'><div class='wagerlinfo'><div class='rollmaerial'><span class='reihe'>".substr($wagerl['uicNummer'], 4,4)."</span> ".substr($wagerl['uicNummer'], 8,3)."</div>
                    <div class='ziel'>nach ".$wagerl['destination']['destinationName']."</div><div class='icons'>";
                if($wagerl['speisewagen']){
                    echo "<img src='https://nextrain.finax1.at/cdn/wagenicons/restaurant.svg' alt='icon'>";
                }
                if($wagerl['rollstuhlgerecht']){
                    echo "<img src='https://nextrain.finax1.at/cdn/wagenicons/rollstuhl.svg' alt='icon'>";
                }
                if($wagerl['fahrradmitnahme']){
                    echo "<img src='https://nextrain.finax1.at/cdn/wagenicons/radabstellplatz.svg' alt='icon'>";
                }
                if($wagerl['ruhebereich']){
                    echo "<img src='https://nextrain.finax1.at/cdn/wagenicons/ruhezone.svg' alt='icon'>";
                }
                if($wagerl['infoPoint']){
                    echo "<img src='https://nextrain.finax1.at/cdn/wagenicons/infopoint.svg' alt='icon'>";
                }
                if($wagerl['abgesperrt']){
                    echo "<span class='abgesperrt'>ABGESPERRT</span>";
                }
                echo "</div></div>";
                echo "<div class='wagerlimg'><img src='https://nextrain.finax1.at/cdn/wagenzuege/".$reihe.".gif' alt='trainicon'>";
                echo "</div></div>";
            }

        }
    ?>
</div>
<script>
    let length = $("#garnitur").children().length;
    if(length===1){
        $(".tw").width("88vw");
    }else {
        $(".tw").width("44vw");
    }
    document.querySelectorAll(".erroricon").forEach(function(elm){
        elm.parentNode.parentNode.style.textDecoration="line-through";
        elm.parentNode.parentNode.style.fontStyle="italic";
    });
</script>
<div id="trainProperties">
</div>
<br>
</body>
</html>