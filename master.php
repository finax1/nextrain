<?php
include 'simple_html_dom.php';
date_default_timezone_set('Europe/Vienna');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
function zuglauf($zugid, $datum)
{
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/dn/'.$zugid.'?date='.$datum;
    $html = file_get_html($link);
    $i=0;
    foreach ($html->find('tr') as $k => $row) {
        if($i>0) {
            $table[$i]['station'] = str_replace(' Bahnhof', '', $row->find('td', 1)->plaintext);
            try {
                $table[$i]['stationid'] = $row->find('td')[1]->children()[0]->href;
            } catch (\Throwable $t) {
                $table[$i]['stationid']=null;
            }
            $table[$i]['stationid'] = explode('&',explode('input=', $table[$i]['stationid'])[1])[0];
            $table[$i]['ankunft'] = $row->find('td', 2)->plaintext;
            $table[$i]['ankunftEst'] = $row->find('td', 3)->plaintext;
            $table[$i]['abfahrt'] = $row->find('td', 4)->plaintext;
            $table[$i]['abfahrtEst'] = $row->find('td', 5)->plaintext;
            $table[$i]['bahnsteig'] = $row->find('td', 7)->plaintext;
        }
        $i++;
    }
    for ($j = 0; $j < count($table); $j++) {
        if (trim($table[$j]['abfahrtEst']) == 'p&#252;nktlich  &nbsp;') {
            unset($table[$j]['abfahrtEst']);
        }
        if (trim($table[$j]['ankunftEst']) == 'p&#252;nktlich  &nbsp;') {
            unset($table[$j]['ankunftEst']);
        }
        if(stripos($table[$j]['abfahrtEst'], 'Ausfall')!==false){
            $table[$j]['abfahrtEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['abfahrtEst'], 'Zusatz')!==false){
            $table[$j]['abfahrtEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['abfahrtEst'], 'Ersatz')!==false){
            $table[$j]['abfahrtEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['abfahrtEst'], 'Garnitur')!==false){
            $table[$j]['abfahrtEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/garniturtausch.png" class="erroricon">';
        }
        if(stripos($table[$j]['abfahrtEst'], 'Zugteil')!==false){
            $table[$j]['abfahrtEst']='';
        }
        if(stripos($table[$j]['ankunftEst'], 'Ausfall')!==false){
            $table[$j]['ankunftEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['ankunftEst'], 'Zusatz')!==false){
            $table[$j]['ankunftEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['ankunftEst'], 'Ersatz')!==false){
            $table[$j]['ankunftEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/error.png" class="erroricon">';
        }
        if(stripos($table[$j]['ankunftEst'], 'Garnitur')!==false){
            $table[$j]['ankunftEst']='<img alt="erroricon" src="https://nextrain.finax1.at/cdn/icons/garniturtausch.png" class="erroricon">';
        }
        if(stripos($table[$j]['ankunftEst'], 'Zugteil')!==false){
            $table[$j]['ankunftEst']='';
        }
    }
    for ($j = 0; $j < count($table); $j++) {
        if(stripos($table[$j]['ankunftEst'], 'Bau')!==false){
            $table[$j]['ankunftEst'] = " ";
        }
        if(stripos($table[$j]['abfahrtEst'], 'Bau')!==false){
            $table[$j]['abfahrtEst'] = " ";
        }
    }
    for($j=0;$j<count($table)*2;$j++){
        if($table[$j]['stationid']==""){
            unset($table[$j]);
        }
    }
    try{
        foreach($html->find('.journeyMessageHIM') as $message){
            $error=find('span')[0]->plaintext;
            $errortitle= find('span')[0]->find('strong')[0]->plaintext;
            $error=trim(str_replace($errortitle, '', $error));
            $errortitle = str_replace(' (-)', '', $errortitle);
            $table['last']['title'] = $errortitle;
            $table['last']['detail']=$error;
        }
    }
    catch (\Throwable $t){

    }
    $table = array_values($table);
    $table= json_encode($table, true);
    $table = str_replace('\r\n', ' ', $table);
    $table = html_entity_decode($table);
    $table = str_replace(' (ÖBB)', '', $table);
    $table = str_replace(' Bahnhst', '', $table);
    $table = str_replace(' (Bahnsteige 1-2)', '', $table);
    $table = str_replace('Hbf)', 'Hauptbahnhof', $table);
    return $table;
}
function monitor($station, $uhrzeit, $datum, $anzahl, $type)
{
    $link='http://fahrplan.oebb.at/bin/stboard.exe/dnx?ld=23&M=p2&dpm=0&dsx=0&dgl=1&dw=128&dh=128&input='.$station.'&boardType='.$type.'&time='.$uhrzeit.'&productsFilter=1111110000000000-000000&date='.$datum.'&maxJourneys='.$anzahl.'&start=yes&';
    $html = file_get_contents($link);
    $web = new simple_html_dom();
    $web->load($html);
    $i=0;
    $arr1=array();
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//a/@href');
    $arr2=array();
    $data=array();
    foreach ($web->find('div.HFS_mobile') as $item) {
        foreach($item->find('div') as $element)
        {
            if(stripos($element->plaintext, 'english')==null) {
                $arr1[$i] = $element->plaintext;
                $i++;
                
            }
        }
    }
    $arr2 = array_values($arr2);
    unset($arr1[0]);
    unset($arr1[1]);
    unset($arr1[2]);
    unset($arr1[3]);
    unset($arr1[4]);
    unset($arr1[5]);
    $arr1 = array_values($arr1);
    unset($arr1[0+$anzahl]);
    unset($arr1[1+$anzahl]);
    unset($arr1[2+$anzahl]);
    unset($arr1[3+$anzahl]);
    unset($arr1[4+$anzahl]);
    unset($arr1[5+$anzahl]);
    unset($arr1[6+$anzahl]);
    $arr1 = array_values($arr1);
    for($j=0;$j<count($arr1);$j++){
        $arr1 = str_replace('&#246;', 'ö', $arr1);
        $arr1 = str_replace('&#228;', 'ä', $arr1);
        $arr1 = str_replace('&#252;', 'ü', $arr1);
        $arr1 = str_replace('&#214;', 'Ö', $arr1);
        $arr1 = str_replace('&#196;', 'Ä', $arr1);
        $arr1 = str_replace('&#220;', 'Ü', $arr1);
        $arr1 = str_replace('&#233;', 'é', $arr1);
        $arr1 = str_replace('&#233;', 'é', $arr1);
        $arr1 = str_replace('&#250;', 'ú', $arr1);
        $arr1 = str_replace('&#223;', 'ß', $arr1);
        $arr1 = str_replace('&#225;', 'a', $arr1);
        $arr1 = str_replace('Gl.5-10', '', $arr1);
        $arr1 = str_replace('(Bahnsteige 11-12)', '', $arr1);
        $arr1 = str_replace(' Bahnhst', '', $arr1);
        $arr1 = str_replace(' (Bahnsteige 1-2)', '', $arr1);
        $data[$j]['zug']=str_replace('&nbsp;', ' ', str_replace("Zug-Nr.&nbsp;","" , explode(" &#xBB; ", $arr1[$j])[0]));
        $helpervar1 = explode('0', explode(" &#xBB; ", $arr1[$j])[1])[0];
        $helpervar2 = explode('1', explode(" &#xBB; ", $arr1[$j])[1])[0];
        $helpervar3 = explode('2', explode(" &#xBB; ", $arr1[$j])[1])[0];
        $whichhelper = 0;
        if(strlen($helpervar1)<strlen($helpervar2)&&strlen($helpervar1)<strlen($helpervar3)){
            $data[$j]['ziel']=$helpervar1;
            $whichhelper=1;
        }else if(strlen($helpervar2)<strlen($helpervar3)&&strlen($helpervar2)<strlen($helpervar1)){
            $data[$j]['ziel']=$helpervar2;
            $whichhelper=2;
        }else if(strlen($helpervar3)<strlen($helpervar2)&&strlen($helpervar3)<strlen($helpervar1)){
            $data[$j]['ziel']=$helpervar3;
            $whichhelper=3;
        }else{
            $data[$j]['ziel']="undefined";
        }
        $hlpv = preg_match_all('!\d+!', explode(",", explode(" &#xBB; ", $arr1[$j])[1])[0], $matches);
        $data[$j]['plan'] = implode(':', $matches[0]);
        $data[$j]['real'] = substr(explode( 'vsl.', explode(" &#xBB; ", $arr1[$j])[1])[1], 1, 5);
        if(stripos($arr1[$j], 'ausfa') !== false){
            $data[$j]['real']="achtung";
        }else if($data[$j]['plan']==$data[$j]['real']){
            $data[$j]['real']=false;
        }
        $data[$j]['bahnsteig']=preg_split('/\s+/', explode("Gl.", $arr1[$j])[1])[1];
        if(substr($data[$j]['zug'], 0,1)=='S'){
            $data[$j]['zugname']=substr(explode("(", $data[$j]['zug'])[0], 0,-1);
            $data[$j]['zugnummer']=preg_replace("/[^0-9]/","",explode("(", $data[$j]['zug'])[1]);
            unset($data[$j]['zug']);
        }else{
            $data[$j]['zugnummer']=preg_replace("/[^0-9]/","",$data[$j]['zug']);
            $data[$j]['zugname']=$data[$j]['zug'];
            unset($data[$j]['zug']);
        }
        $data[$j]['zuggattung']="DEFAULT";
        if(substr($data[$j]['zugname'], 0,1)=="R"){
            $data[$j]['zuggattung']="R";
        }if(substr($data[$j]['zugname'], 0,1)=="D"){
            $data[$j]['zuggattung']="D";
        }if(substr($data[$j]['zugname'], 0,1)=="S"){
            $data[$j]['zuggattung']="S";
        }if(substr($data[$j]['zugname'], 0,1)=="M"){
            $data[$j]['zuggattung']="M";
        }if(substr($data[$j]['zugname'], 0,3)=="ICE"){
            $data[$j]['zuggattung']="ICE";
        }if(substr($data[$j]['zugname'], 0,2)=="EC"){
            $data[$j]['zuggattung']="EC";
        }if(substr($data[$j]['zugname'], 0,2)=="IC"){
            $data[$j]['zuggattung']="IC";
        }if(substr($data[$j]['zugname'], 0,4)=="WEST"){
            $data[$j]['zuggattung']="WEST";
        }if(substr($data[$j]['zugname'], 0,2)=="EN"){
            $data[$j]['zuggattung']="EN";
        }if(substr($data[$j]['zugname'], 0,1)=="D"){
            $data[$j]['zuggattung']="D";
        }if(substr($data[$j]['zugname'], 0,2)=="NJ"){
            $data[$j]['zuggattung']="NJ";
        }if(substr($data[$j]['zugname'], 0,3)=="REX"){
            $data[$j]['zuggattung']="REX";
        }if(substr($data[$j]['zugname'], 0,2)=="RJ"){
            $data[$j]['zuggattung']="RJ";
        }if(substr($data[$j]['zugname'], 0,3)=="CAT"){
            $data[$j]['zuggattung']="CAT";
        }if(substr($data[$j]['zugname'], 0,3)=="Bus"){
            $data[$j]['zuggattung']="BUS";
        }
    }
    $x=0;
    for($j=0;$j<60;$j++){
        if(stripos($nodes[$j]->nodeValue, 'ano')==false && $x<19){
            $arr2[$j]=$nodes[$j]->nodeValue;
            $x++;
        }
    }
    $arr2 = array_values($arr2);
    for($j=0;$j<count($arr2);$j++) {
        $teile = explode("/", $arr2[$j]);
        $data[$j]['linkid'] = explode("?", $teile[6] . "/" . $teile[7] . "/" . $teile[8] . "/" . $teile[9] . "/" . $teile[10] . "/")[0];
    }
    $jsonvar = json_encode($data, true);
    $jsonvar = str_replace(' \r\n ', '', $jsonvar);
    $jsonvar = str_replace(' Bahnhof', '', $jsonvar);
    $jsonvar = str_replace(' \/', '/', $jsonvar);
    return $jsonvar;
}
function getZugid($zugnummer){
    $link='http://fahrplan.oebb.at/bin/trainsearch.exe/dn?stationFilter=81&trainname='.$zugnummer.'&date='.date("d.m.Y");
    $html = file_get_contents($link);
    $htmlt = file_get_html($link);
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//a/@href');
    try {
        for ($j = 1; $j < count($htmlt->find('.resultTable')[0]->find('tr')) + 1; $j++) {
            if (stripos(($htmlt->find('.resultTable')[0]->find('tr')[$j]->find('td')[0]->plaintext), "BusSV") === false) {
                return explode('?', explode('dn/', $nodes[$j + 14]->nodeValue)[1])[0];
            }
        }
    }
    catch (\Throwable $t) {
        return "error";
    }
    return "error";
}
function getStationid($stationStr){
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $statement = $pdo->prepare('SELECT * FROM stations WHERE levenshtein(:station, name) BETWEEN 0 AND 9 ORDER BY levenshtein(:station, name)');
    $statement->execute(array('station' => preg_replace('/[0-9]+/', '', $stationStr)));
    $res = $statement->fetch(PDO::FETCH_ASSOC);
    return $res['nummer'];
}
function getZugName($zugid){
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/el/'.$zugid.'?date='.date("d.m.Y");
    $html = file_get_html($link);
    $zuege = array("");
    $num1 = count(explode(' ', trim($html->find('tr')[0]->plaintext)));
    if($num1==8){
        $num=6;
    }else if ($num1==6){
        $num=4;
    }else{
        return "Zug";
    }if(strlen(str_replace(' ','' ,$html->find('tr')[1]->find('td')[$num]->plaintext))>1){
        return $html->find('tr')[1]->find('td')[$num]->plaintext;
    }else{
        return $html->find('tr')[2]->find('td')[$num]->plaintext;
    }
}
function getStationName($stationid){
    $link='http://fahrplan.oebb.at/bin/stboard.exe/dnx?ld=23&M=p2&dpm=0&dsx=0&dgl=1&dw=128&dh=128&input='.$stationid.'&boardType=dep&time=12:00&productsFilter=1111110000000000-000000&date='.date("d.m.Y").'&maxJourneys=10&start=yes&';
    $html = file_get_html($link);
    return str_replace(' Bahnhof','' , $html->find('.querysummary')[0]->find('strong')[0]->plaintext);
}
function whichQuery($input){
    //returns 1 if trainnumber, 2 if station, 3 if bad request
    //wenn nur zahlen
    if(strlen($input)-preg_match_all( "/[0-9]/", $input)<1){
        return 1;
    //wenn
    }else if(preg_match_all( "/[0-9]/", $input)<5 && strlen($input)-preg_match_all( "/[0-9]/", $input)>0){
        return 2;
    }
    else{
        return 3;
    }
}
function encrypt_url($string, $hash) {
    return $hash->encode($string);
}
function decrypt_url($string, $hash) {
    return $hash->decode($string);
}
function encrypt($message)
{
    return base64_encode($message);
}
function decrypt($message)
{
    return base64_decode($message);
}
function contentQuery($getV)
{
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    if (whichQuery($getV) == 1) {
        $echoVar = '<table>';
        $i = 0;
        foreach (json_decode(zuglauf(getZugid(preg_replace("/[^0-9]/", "", $getV)), date("d.m.Y")), true) as $elms) {
            if ($i % 2 == 0) {
                $echoVar .= '<tr class="zebra1">';
            } else {
                $echoVar .= '<tr class="zebra2">';
            }
            $echoVar .= '<td class="stations"><a href="https://nextrain.finax1.at/ls/' . encrypt_url($elms['stationid']) . '">' . $elms['station'] . '</a></td><td class="arrivals">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunft'])) . '</td><td class="arrivalsEst">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunftEst'])) . '</td><td class="departure">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['abfahrt'])) . '</td><td class="departureEst">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['abfahrtEst'])) . '</td><td class="platforms">' . $elms['bahnsteig'] . '</td>';
            $i++;
        }
        $echoVar .= "</table>";
        return $echoVar;
    } else if (whichQuery($getV) == 2) {
        $echoVar = '<table>';
        $i = 0;
        foreach (json_decode(monitor(getStationid($getV), date('H:i:s'),date("d.m.Y"), 20, "dep"), true) as $elms) {
            if ($i % 2 == 0) {
                $echoVar .= '<tr class="zebra1">';
            } else {
                $echoVar .= '<tr class="zebra2">';
            }
            $echoVar .= '<td class="time">'.$elms['plan'].'</td><td class="timeEst">';
            if($elms['real']=="achtung")  {
                $echoVar .= '<img alt="erroricon" src="cdn/icons/error.png" class="erroricon">';
            }else{
                $echoVar .= $elms['real'];
            }
            $echoVar.='</td><td class="traintd"><a href="https://nextrain.finax1.at/lt/'.encrypt_url($elms['linkid']).'"><div class="traindiv '.$elms['zuggattung'].'">'.$elms['zugname'].'</div></a></td><td class="destination">'.$elms['ziel'].'</td><td class="platforms">'.$elms['bahnsteig'].'</td>';
            $i++;
        }
        $echoVar .= "</table>";
        return $echoVar;

    } else {
        return "Des hot ned hinghaut :(";
    }
}
function contentTrain($getV){
    $echoVar = '<table>';
    $i = 0;
    foreach (json_decode(zuglauf($getV, date("d.m.Y")), true) as $elms) {
        if ($i % 2 == 0) {
            $echoVar .= '<tr class="zebra1">';
        } else {
               $echoVar .= '<tr class="zebra2">';
            }
            $echoVar .= '<td class="stations"><a href="https://nextrain.finax1.at/lt/' . encrypt_url($elms['stationid']) . '">' . $elms['station'] . '</a></td><td class="arrivals">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunft'])) . '</td><td class="arrivalsEst">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['ankunftEst'])) . '</td><td class="departure">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['abfahrt'])) . '</td><td class="departureEst">' . str_replace('&nbsp;','',preg_replace('/\s+/', '', $elms['abfahrtEst'])) . '</td><td class="platforms">' . $elms['bahnsteig'] . '</td>';
            $i++;
        }
        $echoVar .= "</table>";
        return $echoVar;
}
function contentStation($getV){
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
        $echoVar = '<table>';
        $i = 0;
        foreach (json_decode(monitor($getV, date("H:i:s"),date("d.m.Y"), 18, "dep"), true) as $elms) {
            if ($i % 2 == 0) {
                $echoVar .= '<tr class="zebra1">';
            } else {
                $echoVar .= '<tr class="zebra2">';
            }
            $echoVar .= '<td class="time">'.$elms['plan'].'</td><td class="timeEst">';
            if($elms['real']=="achtung")  {
                $echoVar .= '<img alt="error" src="cdn/icons/error.png" class="erroricon">';
            }else{
                $echoVar .= $elms['real'];
            }
            $echoVar.='</td><td class="traintd"><a href="https://nextrain.finax1.at/ls/'.encrypt_url($elms['linkid']).'"><div class="traindiv '.$elms['zuggattung'].'">'.$elms['zugname'].'</div></a></td><td class="destination">'.$elms['ziel'].'</td><td class="platforms">'.$elms['bahnsteig'].'</td>';
            $i++;
        }
        $echoVar .= "</table>";
        return $echoVar;
}
function newsStation($station){
    $url = "http://fahrplan.oebb.at/bin/help.exe/dnl?tpl=rss_stations_oebb&stations=".$station;
    $xml = simplexml_load_file($url);
    $i=0;
    $elms=array();
    $retStr="";
    if(count($xml->channel->item)==0){
        return "";
    }
    foreach ($xml->channel->item as $item){
        if(strpos($item->title, 'ausfall')==false) {
            $elms[$i]['title'] = str_replace(']]>', '', str_replace('<![CDATA[', '', $item->title));
            $elms[$i]['description'] = str_replace('<br/><br/>', ' ', str_replace(']]>', '', str_replace('<![CDATA[', '', $item->description)));
            $i++;
        }
    }
    foreach ($elms as $data){
        $retStr.='<div class="message"><div class="messageicon"><div class="messageiconcont"><img class="informicon" src="https://nextrain.finax1.at/cdn/icons/info.png" alt="infoicon"></div></div><div class="messagecontent"> <div class="messageheader">'.$data['title'].'</div><div class="messagebody" style="max-height: 0px;">'.$data['description'].'</div><img alt="flipicon" src="https://nextrain.finax1.at/cdn/icons/open.png" class="flipicon"></div></div>';
    }
    return str_replace('Details finden Sie hier...', '', $retStr);
}
function newsTrain($zugid)
{
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/dn/'.$zugid.'?date='.date("d.m.Y");
    $html = file_get_html($link);
    $retVar = "";
    foreach($html->find('.journeyMessageHIM') as $message) {
        $error = $message->find('span')[0]->plaintext;
        $errortitle = $message->find('span')[0]->find('strong')[0]->plaintext;
        $error = trim(str_replace($errortitle, '', $error));
        $errortitle = str_replace(' (-)', '', $errortitle);
        $retVar.='<hr class="hrline"><div class="messageT"><div class="messageheaderT">'.$errortitle.'</div><div class="messageContentT">'.$error.'</div></div>';
    }
    foreach($html->find('.messageBlockHIM') as $message) {
        $error = $message->find('span')[0]->plaintext;
        $errortitle = $message->find('span')[0]->find('strong')[0]->plaintext;
        $error = trim(str_replace($errortitle, '', $error));
        $errortitle = str_replace(' (-)', '', $errortitle);
        $retVar.='<hr class="hrline"><div class="messageT"><div class="messageheaderT">'.$errortitle.'</div><div class="messageContentT">'.$error.'</div></div>';
    }
    return str_replace('Wir entschuldigen uns für die Unannehmlichkeiten.', '', str_replace('Bitte entschuldigen Sie die Unannehmlichkeiten.', '', $retVar));
}
function infosTrain($zugid){
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/dn/'.$zugid.'?date='.date("d.m.Y");
    $html = file_get_html($link);
    return $html->find('#tq_trainroute_content_table_alteAnsicht > table.resultTable.grey')->plaintext;
}
function countDigits( $str )
{
    return preg_match_all( "/[0-9]/", $str );
}
function errortrue($zugid){
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/el/'.$zugid.'?date='.date("d.m.Y");
    $html = file_get_html($link);
    if(stripos($html->find('#HFSContent', 0)->plaintext, 'Code:')!=false){
        return true;
    }else{
        return false;
    }
}
function get_duplicates($arr) {
    $dups = $new_arr = array();
    foreach ($arr as $key => $val) {
        if (!isset($new_arr[$val])) {
            $new_arr[$val] = $key;
        } else {
            if (isset($dups[$val])) {
                $dups[] = $key;
            } else {
                $dups= array($key);
            }
        }
    }
    return $dups;
}
function wagenreihung($zugname){
    $html = file_get_html('https://as01905.oebb.at/ReportServer?/TIM/FZGSearch/Zugsuche&rs:ClearSession=true&rc:Toolbar=false&rs:Command=Render&Zug='.$zugname.'&Imei=dummyTmp&Guid=4BFB0297-69CE-4D7E-A43B-6F7F6A2D8204');
    $count = 0;
    while (true) {
        if ($html->getElementById('8iT0tr'.$count) == null) {
            break;
        } else {
            $count++;
        }
    }
    $count-=1;
    $array = array();
    for ($i = 1; $i < $count+1; $i++) {
        $array[$i-1] = array();
        for($j=0; $j<7;$j++){
            $array[$i-1]['Tfz']=$html->getElementById('8iT0tr'.$i)->children(0)->plaintext;;
            $array[$i-1]['ZugNr']=$html->getElementById('8iT0tr'.$i)->children(1)->plaintext;;
            $array[$i-1]['Tz']=$html->getElementById('8iT0tr'.$i)->children(2)->plaintext;;
            $array[$i-1]['von']=$html->getElementById('8iT0tr'.$i)->children(3)->plaintext;;
            $array[$i-1]['nach']=$html->getElementById('8iT0tr'.$i)->children(4)->plaintext;;
            $array[$i-1]['Abf']=$html->getElementById('8iT0tr'.$i)->children(5)->plaintext;;
            $array[$i-1]['Ank']=$html->getElementById('8iT0tr'.$i)->children(6)->plaintext;;
        }
    }
    $wagenArr = $array;
    $EC = array("2190","2170", "9570", "2191", "2991", "8890", "8891", "1991", "8191", "2991", "2094");
    $CS = array("8473", "2173");
    $tw=false;
    $tfz = array();
    $onummer = array();
    $wagen="undefiend";
    $triebwagen=array();
    for($i=0; $i<count($wagenArr);$i++){
        $wagerl[$i]['Tfz']= $wagenArr[$i]['Tfz'];
        $wagerl[$i]['ZugNr']=$wagenArr[$i]['ZugNr'];
        $wagerl[$i]['Tz']=$wagenArr[$i]['Tz'];
    }
    $wagerl = array_map("unserialize", array_unique(array_map("serialize", $wagerl)));
    for($i=0; $i<count($wagenArr);$i++) {
        if ($wagerl[$i]['Tfz'] != '') {
            $wagerl[$i]['Tfz'] = substr($wagerl[$i]['Tfz'], 0, 4);
        }
    }
    for($i=0; $i<count($wagerl)-1;$i++){
        if($wagerl[$i]['ZugNr']!=$wagerl[$i+1]['ZugNr']){
            for($i=0; $i<count($wagerl);$i++){
                if($wagerl[$i]['ZugNr']!=$zugname) {
                    unset($wagerl[$i]);
                }
            }
        }
    }
    for($i=0; $i<count($wagerl);$i++){
        if($wagerl[$i]['Tfz']=='9888'){
            unset($wagerl[$i]);
        }
    }
    $wagerl=array_filter($wagerl);
    if(count($wagerl)==0){
        return '';
    }
    for($i=0; $i<count($wagerl);$i++) {
        if ($wagerl[$i]['Tfz'] == '4020'||$wagerl[$i]['Tfz'] == '0427'||$wagerl[$i]['Tfz'] == '2446'||$wagerl[$i]['Tfz'] == '0628'|| $wagerl[$i]['Tfz'] == '5411' ||$wagerl[$i]['Tfz'] == '1425' || $wagerl[$i]['Tfz'] == '4024' || $wagerl[$i]['Tfz'] == '4124' || $wagerl[$i]['Tfz'] == '4023' || $wagerl[$i]['Tfz'] == '4123' || $wagerl[$i]['Tfz'] == '4746' || $wagerl[$i]['Tfz'] == '4744' || $wagerl[$i]['Tfz'] == '5022' || $wagerl[$i]['Tfz'] == '5047') {
            $tw = true;
        }
    }
    if($tw==false) {
        for ($i = 0; $i <= count($wagerl); $i++) {
            if ($wagerl[$i]['Tz'] == 'f'||$wagerl[$i]['Tz'] == 'p') {
                $tfz[count($tfz)] = $wagerl[$i]['Tfz'];
            }
        }
    } else{
        $triebwagen = $wagerl[0]['Tfz'];
    }
    if(!$tw) {
        for ($i = 0; $i < count($wagerl); $i++) {
            if(in_array($wagerl[$i]['Tfz'], $CS)) {
                $wagen="CS";
                break;
            }elseif (in_array($wagerl[$i]['Tfz'], $EC)) {
                $wagen="EC";
                break;
            }elseif($wagerl[$i]['Tfz']=="8090"){
                $wagen="RJ";
                break;
            }elseif($wagerl[$i]['Tfz']=="8091"){
                $wagen="CDRJ";
                break;
            }elseif ($wagerl[$i]['Tfz']=="2633"){
                $wagen="Dosto";
                break;
            }if($wagen=="CS"&&$wagerl[$i+1]['Tfz']=="2633"){
                $wagen="Dosto";
                break;
            }
        }
    }
    for($i=0; $i<count($tfz); $i++){
        if(in_array($i, get_duplicates($onummer))){
            unset($tfz[$i]);
        }
    }
    $returnJSON = array();
    $returnJSON['anzahlTFZ']=count($tfz);
    for($i=0; $i<$returnJSON['anzahlTFZ'];$i++){
        $returnJSON['TFZge'][$i]=$tfz[$i];}
    if($tw){
        $returnJSON['TW']=$triebwagen;
    }
    $returnJSON['istTW']=$tw;
    if(!$tw) {
        $returnJSON['Garnitur'] = $wagen;
    }
    $returnJSON= json_encode($returnJSON, true);
    $lokNEW = "";
    $jsonNEW = json_decode($returnJSON ,true);
    if($jsonNEW["istTW"]==true){
        if($jsonNEW["TW"]=="4020"){
            $twNEW = '<div class="tooltip"><img src="../cdn/trains/4020.gif" class="tw"><span class="tooltiptext">ÖBB 4020</span></div>';
        } else if($jsonNEW["TW"]=="4023" ||$jsonNEW["TW"]=="4024" || $jsonNEW["TW"]=="4123" || $jsonNEW["TW"]=="4124"){
            $twNEW= '<div class="tooltip"><img src="../cdn/trains/4024.gif" class="tw"><span class="tooltiptext">ÖBB '.$jsonNEW["TW"].' (Talent)</span></div>';
        } else if($jsonNEW["TW"]=="4744" || $jsonNEW["TW"]=="4746"){
            $twNEW= '<div class="tooltip"><img src="../cdn/trains/4746.gif" class="tw"><span class="tooltiptext">ÖBB '.$jsonNEW["TW"].' (Cityjet)</span></div>';
        } else if($jsonNEW["TW"]=="5022"){
            $twNEW= '<div class="tooltip"><img src="../cdn/trains/5022.gif" class="tw"><span class="tooltiptext">ÖBB 5022 (Desiro)</span></div>';
        } else if($jsonNEW["TW"]=="5047"){
            $twNEW= '<div class="tooltip"><img src="../cdn/trains/5047.gif" class="tw"><span class="tooltiptext">ÖBB 5047</span></div>';
        }else if($jsonNEW["TW"]=="1425"){
            $twNEW= '<div class="tooltip"><img src="../cdn/trains/1425.gif" class="tw"><span class="tooltiptext">MAV 425</span></div>';
        }else if ($jsonNEW["TW"] == "5411") {
            $twNEW = '<div class="tooltip"><img src="../cdn/trains/ICE.gif" class="tw"><span class="tooltiptext">ÖBB/DB 5411 (ICE T)</span></div>';
        }else if ($jsonNEW["TW"] == "2446"||$jsonNEW["TW"] == "1446") {
            $twNEW = '<div class="tooltip"><img src="../cdn/trains/5147.gif" class="tw"><span class="tooltiptext">GySEV 247 (ÖBB 5147)</span></div>';
        }else if ($jsonNEW["TW"] == "0628") {
            $twNEW = '<div class="tooltip"><img src="../cdn/trains/628.gif" class="tw"><span class="tooltiptext">DB 628</span></div>';
        } else if ($jsonNEW["TW"] == "0427") {
            $twNEW = '<div class="tooltip"><img src="../cdn/trains/427.gif" class="tw"><span class="tooltiptext">BLB 427 (Flirt)</span></div>';
        } else{
            $twNEW= "";
        }
        return $twNEW;
    }else {
        if ($jsonNEW["anzahlTFZ"] != 1) {
            for ($i = 0; $i < $jsonNEW["anzahlTFZ"]; $i++) {
                if ($jsonNEW["TFZge"][0] == "1116" || $jsonNEW["TFZge"][0] == "1216" || $jsonNEW["TFZge"][0] == "1016") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1116.gif" class="tw"><span class="tooltiptext">ÖBB ' . $jsonNEW["TFZge"][0] . ' (Taurus)</span></div>';
                    break;
                } else if ($jsonNEW["TFZge"][0] == "1144") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1144.gif" class="tw"><span class="tooltiptext">ÖBB 1144</span></div>';
                    break;
                } else if ($jsonNEW["TFZge"][0] == "1142") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1142.gif" class="tw"><span class="tooltiptext">ÖBB 1142</span></div>';
                    break;
                } else if ($jsonNEW["TFZge"][0] == "101") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/101.gif" class="tw"><span class="tooltiptext">ÖB/DB 101</span></div>';
                    break;
                } else if ($jsonNEW["TFZge"][0] == "2016") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/2016.gif" class="tw"><span class="tooltiptext">ÖBB 2016</span></div>';
                    break;
                } else if ($jsonNEW["TFZge"][0] == "7380") {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/380.gif" class="tw"><span class="tooltiptext">ČD 380</span></div>';
                    break;
                } else {
                    $lokNEW = '<div class="tooltip"><img src="../cdn/trains/ghostLok.gif" class="tw"><span class="tooltiptext">Lok</span></div>';
                    break;
                }
            }
        } else {
            if ($jsonNEW["TFZge"][0] == "1116" || $jsonNEW["TFZge"][0] == "1216" || $jsonNEW["TFZge"][0] == "1016") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1116.gif" class="tw"><span class="tooltiptext">ÖBB ' . $jsonNEW["TFZge"][0] . ' (Taurus)</span></div>';
            } else if ($jsonNEW["TFZge"][0] == "1144") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1144.gif" class="tw"><span class="tooltiptext">ÖBB 1144</span></div>';
            } else if ($jsonNEW["TFZge"][0] == "1142") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/1142.gif" class="tw"><span class="tooltiptext">ÖBB 1142</span></div>';
            } else if ($jsonNEW["TFZge"][0] == "101") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/101.gif" class="tw"><span class="tooltiptext">ÖB/DB 101</span></div>';
            } else if ($jsonNEW["TFZge"][0] == "2016") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/2016.gif" class="tw"><span class="tooltiptext">ÖBB 2016</span></div>';
            } else if ($jsonNEW["TFZge"][0] == "7380") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/380.gif" class="tw"><span class="tooltiptext">ČD 380</span></div>';
            } else {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/ghostLok.gif" class="tw"><span class="tooltiptext">Lok</span></div>';
            }
        }
        if ($jsonNEW["Garnitur"] == "Dosto") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/8633.gif" class="tw"><span class="tooltiptext">ÖBB Doppelstock-Wagen</span></div>';
        } else if ($jsonNEW["Garnitur"] == "NJ") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/NJ.gif" class="tw"><span class="tooltiptext">ÖBB Nigthjet-Wagen</span></div>';
        } else if ($jsonNEW["Garnitur"] == "EC") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/EC.gif" class="tw"><span class="tooltiptext">ÖBB Eurocity-Wagen</span></div>';
        } else if ($jsonNEW["Garnitur"] == "CS") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/8073.gif" class="tw"><span class="tooltiptext">ÖBB CityShuttle-Wagen</span></div>';
        } else if ($jsonNEW["Garnitur"] == "RJ") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/8090.gif" class="tw"><span class="tooltiptext">ÖBB Railjet (Jetö)</span></div>';
        } else if ($jsonNEW["Garnitur"] == "CDRJ") {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/8091.gif" class="tw"><span class="tooltiptext">ČD Railjet (Jetö)</span></div>';
        } else {
            $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/ghostWagerl.gif" class="tw"><span class="tooltiptext">Wagon</span></div>';
        }
        if($jsonNEW["istTW"]==false){
            if ($lokNEW == "") {
                $lokNEW = '<div class="tooltip"><img src="../cdn/trains/ghostLok.gif" class="tw"><span class="tooltiptext">Lok</span></div>';
            }
            if ($wagerlNEW=="") {
                $wagerlNEW = '<div class="tooltip"><img src="../cdn/trains/ghostWagerl.gif" class="tw"><span class="tooltiptext">Wagon</span></div>';
            }
        }
        return $lokNEW . $wagerlNEW;
    }
}
function zugNrSbahn($zugid){
    $link='http://fahrplan.oebb.at/bin/traininfo.exe/en/'.$zugid.'?date='.date("d.m.Y");
    $html = file_get_html($link);
    return explode(')', explode("Train-No. ", $html->find('div.summary.clearfix')[0]->plaintext)[1])[0];
}
function createShortlink($link)
{
    $uniqid = uniqid('nex', true);
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $statement = $pdo->prepare("INSERT INTO share (shortlink, reallink, lastAccess) VALUES (:shortlink, :reallink, :lastAccess)");
    $result = $statement->execute(array('shortlink' => $uniqid, 'reallink' => $link, 'lastAccess' => date("Y-m-d H:i:s")));
    return "https://nextrain.finax1.at/share/" . $uniqid;
}
function log_ip(){
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    $res = file_get_contents('https://www.iplocate.io/api/lookup/'.$ipaddress);
    $res = json_decode($res, true);
    $county = $res["country"];
    $city = $res["city"];
    $city = $city==null?"unknown":$city;
    $county = $county==null?"unknown":$county;
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $statement = $pdo->prepare("INSERT INTO access (country, city, ip,time) VALUES (:country, :city, :ip, :time)");
    $result = $statement->execute(array('country' => $county, 'city' => $city, 'ip' => $ipaddress, 'time' => date("H:i:s")));
}