<?php
// 1. タイムゾーンを日本時間に設定
date_default_timezone_set('Asia/Tokyo');

// 2. XSS対策用の関数
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// 3. POSTデータの受け取り
$q1_score = isset($_POST['q1_score']) ? h($_POST['q1_score']) : '';
$q2_score = isset($_POST['q2_score']) ? h($_POST['q2_score']) : '';
$q3_score = isset($_POST['q3_score']) ? h($_POST['q3_score']) : '';
$q4_score = isset($_POST['q4_score']) ? h($_POST['q4_score']) : '';
$q5_main  = isset($_POST['q5_main'])  ? h($_POST['q5_main'])  : '';
$q5_sub   = isset($_POST['q5_sub'])   ? h($_POST['q5_sub'])   : '';
$comment  = isset($_POST['comment'])  ? h($_POST['comment'])  : '';

// CSV崩れ防止（カンマと改行の変換）
$comment = str_replace(',', '、', $comment);
$comment = str_replace(array("\r\n", "\r", "\n"), ' ', $comment);

// 4. 保存する日時の取得
$time = date("Y-m-d H:i:s");

// 5. CSVに書き込む1行の文字列を作成
$str = $time . ',' . $q1_score . ',' . $q2_score . ',' . $q3_score . ',' . $q4_score . ',' . $q5_main . ',' . $q5_sub . ',' . $comment . "\n";

// 6. ファイル書き込み処理
$file = @fopen("data/data.csv", "a");

if ($file) {
    fwrite($file, $str);
    fclose($file);
} else {
    // 開けなかった場合は画面にエラーメッセージを表示
    die("【エラー】data.csv に書き込めませんでした。<br>「data.csv」ファイルのアクセス権（パーミッション）が「読み/書き」になっているか確認してください。");
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