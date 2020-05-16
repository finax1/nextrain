<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if (empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at/login");
    die();
}
?><html lang="de">
<head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
    <title>NexTRAIN</title>
    <link rel="stylesheet" type="text/css" href="css/loading.css">
    <script type="text/javascript" src="js/landing.js" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="https://nextrain.finax1.at/cdn/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://nextrain.finax1.at/cdn/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://nextrain.finax1.at/cdn/icons/favicon-16x16.png">
</head>
    <body>
    <h2>
        <span id="headersp" unselectable="on" onselectstart="return false;" onmousedown="return false;">NexTRAIN</span>
    </h2>
        <div class="train">
            <div class="windows"></div>
            <div class="lights"></div>
        </div>
        <div class="rails">
            <div class="ties"></div>
            <div class="ties"></div>
            <div class="ties"></div>
    </div>
    <?php
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
    $statement->execute(array('country' => $county, 'city' => $city, 'ip' => $ipaddress, 'time' => date("H:i:s")));
    ?>
    <style>
        body {
            align-items: center;
            background-color: #6c7784;
            display: flex;
            flex-flow: column nowrap;
            height: 90vh;
            justify-content: center;
        }
    </style>
    <script>
        document.getElementById("loginBTN").click();
    </script>
</body>
</html>