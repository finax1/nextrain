<?php
error_reporting(0);
include '../api/simple_html_dom.php';
include 'master_api.php';
header("Content-Type: application/json; charset=UTF-8");
$station = $_GET['station'];
$uhrzeit = $_GET['uhrzeit'];
$datum = $_GET['datum'];
$anzahl = $_GET['anzahl'];
echo '{"error": ';
if($_GET['type'] == 'dep' || $_GET['type'] == 'arr'){
    $type = $_GET['type'];
}
else{
    echo "true}";
    die();
}
echo 'false, "content": ';
$link='http://fahrplan.oebb.at/bin/stboard.exe/dnx?ld=23&M=p2&dpm=0&dsx=0&dgl=1&dw=128&dh=128&input='.$station.'&boardType='.$type.'&time='.$uhrzeit.'&productsFilter=1111110000000000-000000&date='.$datum.'&maxJourneys='.$anzahl.'&start=yes&';
$html = curl_get_contents($link);
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
$jsonvar = str_replace('Bahnhst', '', $jsonvar);
$jsonvar = str_replace(' \/', '/', $jsonvar);
echo "}";