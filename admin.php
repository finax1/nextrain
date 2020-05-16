<?php
    session_start();
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(isset($_GET['login'])&&!isset($_SESSION['authorized'])) {
        $userpasswd = $_POST['password'];
        if(password_verify($userpasswd, '$2y$10$Cl0lgxuGFW3dGaRUpaksYOHbsjFs1CVDT8uDHSyRwCTWOjuMTuIz.')){
            $_SESSION['authorized'] = "true";
            header("Location: https://nextrain.finax1.at/admin");
            die();
        }else{
            echo "Falsches Paswort!";
        }
    }
    elseif (isset($_GET['activate'])&&isset($_SESSION['authorized'])){
        $num = array_search("", $_POST);
        $statement = $pdo->prepare("UPDATE users SET aktiviert = 1 WHERE id = :id");
        $res=$statement->execute(array("id"=>$num));
    }
    elseif(isset($_GET['delete'])&&isset($_SESSION['authorized'])){
        $num = array_search("", $_POST);
        $statement = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $statement->execute(array("id"=>$num));
        $result = $statement->fetchAll();
        $mail = $result[0]["email"];
        $statement = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $statement->execute(array("id"=>$num));
        $statement = $pdo->prepare("INSERT INTO blockedmail (email) VALUES (:mail)");
        $statement->execute(array("mail"=>$mail));
    }
    $uri = $_SERVER['REQUEST_URI'];
    $realurl = "/admin";
    if ($uri != $realurl)
    {
        Header( "HTTP/1.1 301 Moved Permanently" );
        Header( "Location: $realurl");
        die();
    }
?>
    <!DOCTYPE html>
    <html lang="de">
    <head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
        
        <title>NexTRAIN - Admin</title>
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
            <input disabled type="search" id="inputfield" name="q" autocomplete="false" placeholder="Suchen...">
        </form>
    </div>
</div>
<div id="infodiv" class="opacityable">
    <div id="icon"><?php
        echo '<img src="https://nextrain.finax1.at/cdn/icons/admin.png" class="infoicon unselectable" style="margin-right: 3px" alt="icon1">';
        ?></div>
    <div id="fullName" onclick="location.reload()">
        Admin
    </div>
</div>
<dialog role="dialog" aria-labelledby="dialog-heading">
    <>
</dialog>
<?php
    if(!isset($_SESSION['authorized'])) {
        echo '<form action="?login=1" method="post" id="adminform">
            <input type="password" name="password" id="adminpassword">
            <button id="loginbtn" type="submit">Absenden</button>
            </form>';
        die();
    }
    $statement = $pdo->prepare("SELECT * FROM users");
    $statement->execute();
    $results = $statement->fetchAll();
    $countAct = 0;
    $countNonAct = 0;
    foreach ($results as $res){
        if($res['aktiviert']=="1"){
            $countAct++;
        }else{
            $countNonAct++;
        }
    }
    ?>
<div id="statistics">
    <?php
    echo "<b>Registrierte Nutzer:</b> $countAct <br>";
    echo "<b>Nicht registrierte Nutzer:</b> $countNonAct <br>";
    echo "<b>Gesamt:</b> ". (intval($countNonAct)+intval($countAct)) ."<br>";
    ?>
</div>
<div id="logins">
<?php
    $statement = $pdo->prepare("SELECT * FROM users WHERE aktiviert = 0 ORDER BY created_at DESC");
    $statement->execute();
    $results = $statement->fetchAll();
    echo "<table id='activatetable'>";
    if(count($results)==0){
        echo "Keine Einträge";
    }
    $js='return confirm("'.'Bist du sicher?'.'")';
    foreach ($results as $res){
        echo "<tr class='activatetr' data-num='".$res['id']."'><td>".$res['username']."</td><td>".$res['email']."</td><td>".$res['created_at']."</td><td><form action='?activate=1' onsubmit='".$js."' method='post'><button type='submit' name='" .$res['id']."' class='activateBtns'>aktivieren</button></form></td><td><form onsubmit='".$js."' action='?delete=1' method='post'><button type='submit' name='".$res['id']."' class='activateBtns'>löschen</input></button></td></tr>";
    }
    echo "</table>";
?>
</div>