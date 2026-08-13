<?php
// src/funcs.php

// DB接続関数
function db_conn() {
try {
       // ローカル環境(XAMPP)のDB設定
        // ※本番環境にアップロードする際は、ここをさくらインターネットのDB情報に変更します
        $db_name = 'kadai_09';    // データベース名
        $db_id   = 'root';      // アカウント名
        $db_pw   = '';      // パスワード：XAMPPはパスワード無し（MAMPの場合は 'root'）
        $db_host = 'localhost'; // DBホスト
        // DB接続処理（文字コードは utf8mb4 を指定）
        $pdo = new PDO('mysql:dbname=' . $db_name . ';charset=utf8mb4;host=' . $db_host, $db_id, $db_pw);
        
        // 成功したら $pdo を外に持ち出す
        return $pdo; 
        
    } catch (PDOException $e) {
        exit('DB Connection Error:' . $e->getMessage());
    }
}
// 💡 ログイン状態をチェックする関数
function sschk() {
    // 1. チケットがない、または偽造されている場合はエラーにして止める
    if (!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"] != session_id()) {
        exit("LOGIN ERROR: 不正なアクセスです。");
    } else {
        // 2. 正常な場合は、防犯のために新しいチケットを再発行する
        session_regenerate_id(true);
        $_SESSION["chk_ssid"] = session_id();
    }
}
// 💡 画面をリダイレクト（移動）させる関数
function redirect($file_name) {
    header("Location: " . $file_name);
    exit();
}