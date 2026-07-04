<?php
// エラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. GETデータ取得（URLの ?id=〇〇 の部分を受け取ります）
$id = $_GET['id'];

// 2. DB接続
require_once('funcs.php');
$pdo = db_conn();

// 3. データ取得SQL作成（選ばれたidのデータだけを抽出）
$stmt = $pdo->prepare("SELECT * FROM staff_entry_table WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT); // 数値として安全に渡す
$status = $stmt->execute();

// 4. データ表示
if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorMessage:".$error[2]);
} else {
    // データは1件だけなのでループ(while)は不要
    $row = $stmt->fetch();
}

// 複数選択されていた職種を配列に戻す（チェックボックスの復元用）
$job_array = explode(',', $row['job_types']);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>スタッフ情報編集</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <div class="form-wrapper">
        <h2>スタッフ情報編集</h2>
        <form method="POST" action="update.php">
            
            <div class="form-group">
                <label>お名前 <span class="required">必須</span></label>
                <div class="flex-inputs">
                    <span>姓</span><input type="text" name="name_last" value="<?= h($row['name_last']) ?>" required>
                    <span>名</span><input type="text" name="name_first" value="<?= h($row['name_first']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>性別 <span class="required">必須</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="男" <?= ($row['gender'] == '男') ? 'checked' : '' ?> required> 男</label>
                    <label><input type="radio" name="gender" value="女" <?= ($row['gender'] == '女') ? 'checked' : '' ?> required> 女</label>
                </div>
            </div>

            <div class="form-group">
                <label>ご希望の職種(複数選択可)</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="job_types[]" value="家事代行サービス" <?= in_array('家事代行サービス', $job_array) ? 'checked' : '' ?>> 家事代行サービス</label>
                    <label><input type="checkbox" name="job_types[]" value="ベビー・キッズシッター" <?= in_array('ベビー・キッズシッター', $job_array) ? 'checked' : '' ?>> ベビー・キッズシッター</label>
                    </div>
            </div>

            <div class="form-group">
                <label>お持ちの資格・経験</label>
                <textarea name="skills" rows="5"><?= h($row['skills']) ?></textarea>
            </div>

            <input type="hidden" name="id" value="<?= h($row['id']) ?>">

            <button type="submit" class="submit-btn">更新する</button>

        </form>
    </div>
</body>
</html>