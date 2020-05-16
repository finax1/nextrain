<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if (empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at/login");
    die();
}
include 'master.php';
if(isset($_GET['q'])) {
    $uhrzeit=date('H:i:s');
    $tableType="dep";
    $query = $_GET['q'];
    $decrypted=decrypt($query);
    $achtBool = false;
    $achtTime = "";
    if(stripos($decrypted, '-!!arr')){
        $tableType="arr";
        str_replace('-!!arr', '', $decrypted);
    }
    $stationid=str_replace('-!!arr', '', explode("---", $decrypted)[0]);
    if(isset(explode("---", $decrypted)[1])) {
        $uhrzeit = explode("---", $decrypted)[1];
    }
    if(isset(explode("---", $decrypted)[2])) {
        $trainnum = explode("---", $decrypted)[2];
    }
    if(isset(explode("---", $decrypted)[3])) {
        $abfahrt = explode("---", $decrypted)[3];
    }
    if(isset(explode("---", $decrypted)[4])) {
        $achtung = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', explode("---", $decrypted)[4]);
        try {
            $achtungdate = new DateTime(trim($achtung));
            $plandate = new DateTime(str_replace(" ", "",$uhrzeit));
            $diff = ($plandate->diff($achtungdate));
            $minutes = $diff->days * 24 * 60;
            $minutes += $diff->h * 60;
            $minutes += $diff->i;
            date_add($plandate, new DateInterval('PT10M'));
        } catch (Exception $e) {
        }
        if($plandate<$achtungdate){
            $achtBool = true;
            $uhrzeit = trim($achtung);
        }
    }
    try {
        $query = getStationName($stationid);
    }catch (\Throwable $t){
        header("Location: https://nextrain.finax1.at/error/station");
        die();
    }
}else{
    $stationid="8103000";
    $uhrzeit=date('H:i:s');
    $query = "Wien Hauptbahnhof";
    $tableType="dep";
}
$query=str_replace(' Bahnhst', '',$query);
$query=str_replace(' (Bahnsteige 11-12)', '',$query);
$query=str_replace(' (Bahnsteige 1-2)', '',$query);

$lastCLock ="";
?>
<!DOCTYPE html>
<html lang="de">
<head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
    
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
        <div id="headermain">
            <div id="logo" style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
                 unselectable="on"
                 onselectstart="return false;"
                 onmousedown="return false;"><a href="https://nextrain.finax1.at">NexTRAIN</a>
            </div>
            <div id="liveuhr">
                <span id="clock"></span>
            </div>
        </div>
    </div>
    <div id="search">
        <form id="searchform" action="https://nextrain.finax1.at/q" method="post">
            <input type="search" id="inputfield" name="q" autocomplete="false" placeholder="Suchen..." value="<?php if(strlen($uhrzeit)<8){echo $query.$uhrzeit;}?>">
        </form>
    </div>
</div>
<div id="infodiv" class="opacityable">
    <div id="icon"><?php
        echo '<img src="https://nextrain.finax1.at/cdn/icons/station.png" class="infoicon unselectable" alt="icon1">';
        ?></div>
    <div id="fullName" onclick="location.reload()">
        <?php
        echo $query." (".ucwords(json_decode(file_get_contents("https://live.oebb.at/web/api/eva/".$stationid), true)['DB640']).")";
        ?>
    </div>
</div>
<div id="globalMessages" class="opacityable">
    <?php
    if(whichQuery($query)==2) {
        echo newsStation($stationid);
    }
    ?>
</div>
<?php
if(isset($achtung)) {
    echo '<div id="achtung"><div class="message"><div class="messageicon" style="font-size: 0;background: #d32f2f;"><div class="messageiconcont"><img src="https://nextrain.finax1.at/cdn/icons/clock.png" class="clockicon" alt="clockicon"></div></div><div class="messagecontent" style="background-color: #e57373;"><div class="messageheader">Dieser Zug kommt vorraussichtlich erst um ' . $achtung . ' (+'.$minutes.') an!</div></div></div>';
}
?>
<div id="NXTresult" class="opacityable">
    <?php
    $echoVar = '<table>';
    $i = 0;
    $jsonDe =json_decode(monitor($stationid, $uhrzeit ,date("d.m.Y"), 21, $tableType), true);
    $length = count($jsonDe);
    foreach ($jsonDe as $elms) {
        if($elms['ziel']!="undefined" && $i<$length-2 && strlen($elms['ziel'])>0) {
            $bool = false;
            if ($trainnum == preg_replace("/[^0-9]/","", $elms['zugname']) && $abfahrt == $elms['plan']&&preg_match_all( "/[0-9]/", $elms['zugname'])>0) {
                $bool = true;
                $echoVar .= '<tr class="naviTrain">';
            } else if ($i % 2 == 0) {
                $echoVar .= '<tr class="zebra1">';
            } else {
                $echoVar .= '<tr class="zebra2">';
            }
            $echoVar .= '<td class="time">' . $elms['plan'] . '</td>';
            if (!$bool) {
                if ($elms['real'] == "achtung") {
                    $echoVar .= '<td class="timeEst"><img alt="error" src="../cdn/icons/error.png" class="erroricon">';
                } else {
                    $echoVar .= '<td class="timeEst">' . $elms['real'];
                }
            } else {
                $echoVar .= '<td class="timeEst">';
            }
            $echoVar .= '</td><td class="traintd"><a href="https://nextrain.finax1.at/lt/' . encrypt($elms['linkid']) . '"><div class="traindiv ' . $elms['zuggattung'] . '">' . $elms['zugname'] . '</div></a></td><td class="destination">' . $elms['ziel'] . '</td><td class="platforms">' . $elms['bahnsteig'] . '</td>';
            $i++;
        }
    }
    $echoVar .= "</table>";
    echo $echoVar;
    ?>
</div>
<div id="navigation" class="opacityable">
    <?php   $link = 'https://nextrain.finax1.at/ls/' . encrypt($stationid."---".$jsonDe[count($jsonDe)-1]['plan']);
            echo '<a style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
         unselectable="on"
         onselectstart="return false;"
         onmousedown="return false;" id="spaeter" href="'.$link.'">später&gt;</a>'?>
    <div id="share" onclick="sharePage()" style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
         unselectable="on"
         onselectstart="return false;"
         onmousedown="return false;">teilen</div>
    <?php
    if($tableType=="dep"){
        echo '<a style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
         unselectable="on"
         onselectstart="return false;"
         onmousedown="return false;" id="changeTable" href="https://nextrain.finax1.at/ls/'.encrypt($stationid."-!!arr").'">&gt;Ankünfte</a>';
    }else{
        echo '<a style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
         unselectable="on"
         onselectstart="return false;"
         onmousedown="return false;" id="changeTable" href="https://nextrain.finax1.at/ls/'.encrypt($stationid).'">&gt;Abfahrten</a>';
    }
    ?>
</div>
<div id="specialMessages">
</div>
<br>
</body>
</html>