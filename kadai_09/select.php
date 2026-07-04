<?php
// 1. DB接続（funcs.phpを読み込んでDBに接続します）
require_once('funcs.php');
$pdo = db_conn();

// 2. データ取得SQL作成（全データを取得し、最新順＝idの降順で並び替えます）
$stmt = $pdo->prepare("SELECT * FROM staff_entry_table ORDER BY id DESC");
$status = $stmt->execute();

// 3. データ表示用の変数を用意
$view = "";
if ($status == false) {
    // SQL実行時にエラーがある場合
    $error = $stmt->errorInfo();
    exit("ErrorMessage:".$error[2]);
} else {
    // 成功した場合、データを1行ずつ取り出してHTMLに埋め込みます
    // ※ fetch(PDO::FETCH_ASSOC) で取得し、ループ処理で回します
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // 【重要】出力する際は、funcs.phpで定義した h() 関数を使って必ずXSS対策（サニタイズ）を行います
        $view .= '<div class="list-item">';
        $view .= '<p class="date">' . h($result['indate']) . '</p>';
        $view .= '<p class="name">' . h($result['name_last']) . ' ' . h($result['name_first']) . ' さん</p>';
        $view .= '<p class="job">希望職種：' . h($result['job_types']) . '</p>';

        // ▼ここを追加：idをURLにくっつけて（GET送信）detail.phpに渡します
        $view .= '<a href="detail.php?id=' . $result['id'] . '" class="edit-link">[編集]</a>';
        
        // ▼ここを追加：削除機能へのリンク
        $view .= ' <a href="delete.php?id=' . $result['id'] . '" class="delete-link" style="color:red; margin-left:10px;">[削除]</a>';
        
        $view .= '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ応募一覧</title>
    <style>
        /* フォームの優しいベージュデザインに合わせています */
        body {
            background-color: #f6f5ef;
            color: #665b53;
            font-family: sans-serif;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 20px;
            border-bottom: 2px solid #cbbba0;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .list-item {
            border-bottom: 1px dashed #cbbba0;
            padding: 15px 0;
        }
        .list-item:last-child {
            border-bottom: none;
        }
        .date {
            font-size: 12px;
            color: #999;
            margin: 0 0 5px 0;
        }
        .name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .job {
            font-size: 14px;
            margin: 0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #cbbba0;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>スタッフ応募データ一覧</h1>
        
        <div>
            <?= $view ?>
        </div>

        <a href="index.php" class="back-link">登録フォームに戻る</a>
    </div>
</body>
</html>