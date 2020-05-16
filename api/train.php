<?php
//@TODO database mit replaces
error_reporting(0);
$pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
include '../api/simple_html_dom.php';
include 'master_api.php';
header("Content-Type: application/json; charset=UTF-8;");
header("Access-Control-Allow-Origin: *");
echo '{"error": ';
if(!isset($_GET['zugnummer'])){
    echo "true}";
    die();
}
$zugnummer = $_GET['zugnummer'];
$datum = (isset($_GET['datum']))? $_GET['datum'] : date("d.m.Y");
$link='http://fahrplan.oebb.at/bin/trainsearch.exe/dn?stationFilter=81&trainname='.$zugnummer.'&date='.$datum;
$html = curl_get_contents($link);
$htmlt = file_get_html($link);
$dom = new DOMDocument;
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$nodes = $xpath->query('//a/@href');
try {
    for ($j = 1; $j < count($htmlt->find('.resultTable')[0]->find('tr')) + 1; $j++) {
        if (stripos(($htmlt->find('.resultTable')[0]->find('tr')[$j]->find('td')[0]->plaintext), "BusSV") === false) {
            $zugid = explode('?', explode('dn/', $nodes[$j + 14]->nodeValue)[1])[0];
        }
    }
}
catch (\Throwable $t) {
}
echo 'false, "content": ';
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
        $table[$j]['abfahrtEst']='ausfall';
    }
    if(stripos($table[$j]['abfahrtEst'], 'Zusatz')!==false){
        $table[$j]['abfahrtEst']='anderer';
    }
    if(stripos($table[$j]['abfahrtEst'], 'Ersatz')!==false){
        $table[$j]['abfahrtEst']='ersatz';
    }
    if(stripos($table[$j]['abfahrtEst'], 'Garnitur')!==false){
        $table[$j]['abfahrtEst']='garniturtausch';
    }
    if(stripos($table[$j]['abfahrtEst'], 'Zugteil')!==false){
        $table[$j]['abfahrtEst']='zugteil';
    }
    if(stripos($table[$j]['ankunftEst'], 'Ausfall')!==false){
        $table[$j]['ankunftEst']='ausfall';
    }
    if(stripos($table[$j]['ankunftEst'], 'Zusatz')!==false){
        $table[$j]['ankunftEst']='anderer>';
    }
    if(stripos($table[$j]['ankunftEst'], 'Ersatz')!==false){
        $table[$j]['ankunftEst']='ersatz';
    }
    if(stripos($table[$j]['ankunftEst'], 'Garnitur')!==false){
        $table[$j]['ankunftEst']='garniturtausch';
    }
    if(stripos($table[$j]['ankunftEst'], 'Zugteil')!==false){
        $table[$j]['ankunftEst']='zugteil';
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
foreach ($table as &$stationfe){
    $statement = $pdo->prepare("SELECT * FROM stationnames WHERE standart = :var");
    $statement->execute(array('var' => $stationfe['station']));
    $res = $statement->fetch();
    if($res != false){
        $stationfe['station'] = $res['korrigiert'];
    }
}
$table = array_values($table);
$table= json_encode($table, true);
$table = str_replace('&nbsp;', '', str_replace('\r\n', ' ', $table));
echo $table;
echo "}";
var_dump(html_entity_decode($table));