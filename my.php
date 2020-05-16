<?php
session_start();
if (empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at/login");
    die();
}
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
?>
<!DOCTYPE html>
<html lang="de">
<head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
    
    <title>NexTRAIN - userpanel</title>
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
        <div id="logo" style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
             unselectable="on"
             onselectstart="return false;"
             onmousedown="return false;"><a href="https://nextrain.finax1.at">NexTRAIN</a>
        </div>
    </div>
    <div id="search">
        <form id="searchform" action="https://nextrain.finax1.at/q" method="post">
            <input type="search" id="inputfield" name="q" autocomplete="false" placeholder="Suchen...">
        </form>
    </div>
</div>
<div id="infodiv" class="opacityable">
    <div id="icon"><?php
        echo '<img src="https://nextrain.finax1.at/cdn/icons/user.png" class="infoicon unselectable" alt="icon1">';
        ?></div>
    <div id="fullName" onclick="location.reload()">
        <?php
            echo $_SESSION['username'];
        ?>
    </div>
</div>
<div id="settingsconetent" class="opacityable">

</div>
<br>
</body>
</html>