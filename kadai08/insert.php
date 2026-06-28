<?php
// 1. 共通関数の読み込み（funcs.phpを合体させる）
require_once('funcs.php');

// 2. タイムゾーンを日本時間に設定
date_default_timezone_set('Asia/Tokyo');

// 3. POSTデータの受け取り
$q1_score = isset($_POST['q1_score']) ? $_POST['q1_score'] : '';
$q2_score = isset($_POST['q2_score']) ? $_POST['q2_score'] : '';
$q3_score = isset($_POST['q3_score']) ? $_POST['q3_score'] : '';
$q4_score = isset($_POST['q4_score']) ? $_POST['q4_score'] : '';
$q5_main  = isset($_POST['q5_main'])  ? $_POST['q5_main']  : '';
$q5_sub   = isset($_POST['q5_sub'])   ? $_POST['q5_sub']   : '';
$comment  = isset($_POST['comment'])  ? $_POST['comment']  : '';

// 4. データベース接続（funcs.phpの関数を呼び出す）
$pdo = db_conn();

// 5. データ登録SQL作成
// SQLインジェクション対策（無害化）のためプレースホルダ（:変数名）を使用
$sql = "INSERT INTO kadai_08_table (id, q1_score, q2_score, q3_score, q4_score, q5_main, q5_sub, comment, indate) 
        VALUES (NULL, :q1, :q2, :q3, :q4, :q5_main, :q5_sub, :comment, sysdate())";

$stmt = $pdo->prepare($sql);

// 各プレースホルダに実際の値をバインド（安全な形に変換）
$stmt->bindValue(':q1',      $q1_score, PDO::PARAM_INT);
$stmt->bindValue(':q2',      $q2_score, PDO::PARAM_INT);
$stmt->bindValue(':q3',      $q3_score, PDO::PARAM_INT);
$stmt->bindValue(':q4',      $q4_score, PDO::PARAM_INT);
$stmt->bindValue(':q5_main', $q5_main,  PDO::PARAM_STR);
$stmt->bindValue(':q5_sub',  $q5_sub,   PDO::PARAM_STR);
$stmt->bindValue(':comment', $comment,  PDO::PARAM_STR);

// SQLの実行
$status = $stmt->execute();

// エラーハンドリング
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorMessage:" . $error[2]);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>送信完了</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="main-container">
    <h1>送信完了</h1>

    <div class="avatar-wrap">
        <div class="avatar">
            <img src="image/icochara_thanks.jpeg" alt="キャラクター">
        </div>
    </div>

    <div class="step-box active">
        <p class="intro-message" style="text-align: center;">
            アンケートへのご協力、<br>誠にありがとうございました！<br><br>
            いただいた貴重なご意見は、<br>職場環境の改善に活用させていただきます。
        </p>
        <div class="btn-wrap">
            <button onclick="window.close();" class="start-btn" style="font-family: inherit;">閉じる</button>
        </div>
    </div>
</div>

</body>
</html>