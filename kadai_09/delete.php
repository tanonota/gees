<?php
// エラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. GETデータ取得（URLの ?id=〇〇 の部分を受け取ります）
$id = $_GET['id'];

// 2. DB接続
require_once('funcs.php');
$pdo = db_conn();

// 3. データ削除SQL作成
// DELETE文を使って、指定したidのデータを削除します
$stmt = $pdo->prepare("DELETE FROM staff_entry_table WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// 4. SQL実行
$status = $stmt->execute();

// 5. データ削除処理後
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorMessage:".$error[2]);
} else {
    // 削除成功時は一覧画面（select.php）にリダイレクト
    header('Location: select.php');
    exit();
}
?>