<?php
// エラーを表示させる設定（トラブルシューティング用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. XSS対応
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 2. DB接続関数
function db_conn() {
    try {
        // データベース名を kadai_09 に変更
        $db_name = 'kadai_09';    
        $db_id   = 'root';          // アカウント名（XAMPPのデフォルト）
        $db_pw   = '';              // パスワード（XAMPPのデフォルトは空欄）
        $db_host = 'localhost';     // DBホスト

        // PDOを使ってデータベースに接続
        $pdo = new PDO('mysql:dbname='.$db_name.';charset=utf8mb4;host='.$db_host, $db_id, $db_pw);
        return $pdo;
    } catch (PDOException $e) {
        // DB接続に失敗した場合はここでエラーを表示
        exit('DB Connection Error:'.$e->getMessage());
    }
}
?>