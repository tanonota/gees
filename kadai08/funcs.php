<?php
// XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// DB接続関数
function db_conn() {
    try {
        // ==========================================
        // データベース接続設定
        // ==========================================
        $db_name = 'kadai_08';    // データベース名
        $db_id   = 'root';        // アカウント名
        $db_pw   = '';            // パスワード（ローカルは空っぽ）
        $db_host = 'localhost';   // ホスト名

        $pdo = new PDO('mysql:dbname='.$db_name.';charset=utf8;host='.$db_host, $db_id, $db_pw);
        return $pdo;
    } catch (PDOException $e) {
        exit('DBConnectError:'.$e->getMessage());
    }
}
?>