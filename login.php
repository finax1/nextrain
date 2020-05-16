<?php
session_start();
$errorMessage = "";
$error = false;
if (!empty($_SESSION['login'])) {
    header("Location: https://nextrain.finax1.at");
    die();
}
    $pdo= new PDO('mysql:host=localhost;dbname=efbcxwxo_nextrain', 'efbcxwxo_public', 'Tw$kFA6%;j,1');
if(isset($_GET['login'])) {
    $username = $_POST['username'];
    $passwort = $_POST['passwort'];
    $statement = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $result = $statement->execute(array('username' => $username));
    $user = $statement->fetch();
    if ($user != false && password_verify($passwort, $user['passwort'])) {
        if($user['aktiviert']==1){
            $_SESSION['username'] = $user['username'];
            $_SESSION['login'] = "IS";
            $_SESSION['email'] = $user['email'];
            setcookie("usernamee",$_SESSION['username'],time()+60*60*24*365);
            header("Location: https://nextrain.finax1.at/");
            die();
        }else{
            $errorMessage = "Dein Benutzer wurde noch nicht aktiviert<br>";
        }
    } else {
        $errorMessage = "Ungültiger Benutzername oder Paswort<br>";
    }
}elseif (isset($_GET['register'])) {
    $error = false;
    $email = $_POST['email'];
    $username = $_POST['username'];
    $passwort = $_POST['passwort'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage= 'Bitte eine gültige E-Mail-Adresse eingeben<br>';
        $error = true;
    }
    if (strlen($passwort) == 0) {
        $errorMessage= 'Bitte ein Passwort angeben<br>';
        $error = true;
    }
    if (strlen($username) == 0) {
        $errorMessage= 'Bitte einen Username angeben<br>';
        $error = true;
    }
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $result = $statement->execute(array('email' => $email));
        $user = $statement->fetch();
        if ($user != false) {
            $errorMessage= 'Diese E-Mail-Adresse ist bereits vergeben<br>';
            $error = true;
        }
    }
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM blockedmail WHERE email = :email");
        $result = $statement->execute(array('email' => $email));
        $user = $statement->fetch();
        if ($user != false) {
            $errorMessage= 'Diese E-Mail-Adresse wurde bereits abgelehnt<br>';
            $error = true;
        }
    }
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $result = $statement->execute(array('username' => $username));
        $user = $statement->fetch();
        if ($user != false) {
            $errorMessage= 'Dieser Username ist bereits vergeben<br>';
            $error = true;
        }
    }
    if (!$error) {
        $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
        $statement = $pdo->prepare("INSERT INTO users (email, passwort, username) VALUES (:email, :passwort, :username)");
        $result = $statement->execute(array('email' => $email, 'passwort' => $passwort_hash, 'username' => $username));
        if ($result) {
            $errorMessage= 'Deine Registriereung wurde abgeschickt! Sobald sie akzeptiert wurde, erhälst du eine Mail.';
            $showFormular = false;
        } else {
            $errorMessage= 'Ein Fehler ist aufgetreten, bitte kontaktiere uns <a href="mailto:developer@finax1.at">hier</a><br>';
        }
    }
}
$uri = $_SERVER['REQUEST_URI'];
$realurl = "/login";
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
    
    <title>NexTRAIN - Login</title>
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
        echo '<img src="https://nextrain.finax1.at/cdn/icons/login.png" class="infoicon unselectable" style="margin-right: 3px" alt="icon1">';
        ?></div>
    <div id="fullName" onclick="location.reload()">
        Login
    </div>
</div>
<div id="settingsconetent" class="opacityable">
    <div id="tabcont">
        <button id="loginBTN" class="tabs" onclick="changeView(event, 'login')">Login</button>
        <button class="tabs" onclick="changeView(event, 'register')">Registrieren</button>
    </div>
    <?php if(isset($errorMessage)){echo '<div id="status">'.$errorMessage.'</div>';} ?>
    <div id="login" class="tabcontent">
        <form action="?login=1" id="loginform" method="post">
            <div class="inputcontents">
                <input type="text" size="40" maxlength="250" name="username" id="loginusername" class="support" placeholder="E-Mail/Benutzername" autocomplete="off"><br>
            </div>
            <div class="inputcontents">
                <input type="password" size="40"  maxlength="250" name="passwort" id="loginpw" class="support" placeholder="Passwort"><br>
            </div>
            <button type="submit" id="loginbtn">Login</button>
        </form>
    </div>
    <div id="register" class="tabcontent">
        <form action="?register=1" id="registerform" method="post">
            <div class="inputcontents">
                <input type="email" size="40" maxlength="250" name="email" id="registeremail" placeholder="E-Mail" autocomplete="off"><br>
            </div>
            <div class="inputcontents">
                <input type="text" size="40" maxlength="250" name="username" id="registerusername" placeholder="Benutzername" autocomplete="off"><br>
            </div>
            <div class="inputcontents">
                <input type="password" size="40"  maxlength="250" name="passwort" id="registerpw" class="support" placeholder="Passwort"><br>
            </div>
            <button type="submit" id="registerbtn">Registrieren</button>
        </form>
    </div>
</div>
<br>
</body>
</html>
