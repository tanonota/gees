<?php
session_start();

//SESSION初期化
$_SESSION = array();

//Cookieに保存してたSessionIDの保存期間を過去にして破棄
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

//SESSION削除
session_destroy();

header("Location: login.php");
exit();
?>