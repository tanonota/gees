<?php
// DB接続
require_once('../src/funcs.php');
$pdo = db_conn();

// 💡 既存のテストデータを一度空っぽにする（リセット）
$pdo->exec("TRUNCATE TABLE users");

// 1. 面接官アカウントのデータ (kanri_flg = 0)
$lid1 = 'interviewer';
$lpw1 = password_hash('inter1234', PASSWORD_DEFAULT);
$name1 = '面接担当者';

// 2. 総合管理者アカウントのデータ (kanri_flg = 1)
$lid2 = 'admin';
$lpw2 = password_hash('admin1234', PASSWORD_DEFAULT);
$name2 = '総合管理者';

// 登録処理の準備
$stmt = $pdo->prepare("INSERT INTO users(name, lid, lpw, kanri_flg, life_flg) VALUES(:name, :lid, :lpw, :kanri_flg, 0)");

// 面接官を登録
$stmt->bindValue(':name', $name1, PDO::PARAM_STR);
$stmt->bindValue(':lid', $lid1, PDO::PARAM_STR);
$stmt->bindValue(':lpw', $lpw1, PDO::PARAM_STR);
$stmt->bindValue(':kanri_flg', 0, PDO::PARAM_INT);
$stmt->execute();

// 管理者を登録
$stmt->bindValue(':name', $name2, PDO::PARAM_STR);
$stmt->bindValue(':lid', $lid2, PDO::PARAM_STR);
$stmt->bindValue(':lpw', $lpw2, PDO::PARAM_STR);
$stmt->bindValue(':kanri_flg', 1, PDO::PARAM_INT);
$stmt->execute();

echo "<h1>アカウントの分割セットアップ成功！</h1>";
echo "<h3>🧑‍💼 【面接担当者用】</h3><ul><li>ID: <strong>interviewer</strong></li><li>PW: <strong>inter1234</strong></li></ul>";
echo "<h3>👑 【総合管理者用】</h3><ul><li>ID: <strong>admin</strong></li><li>PW: <strong>admin1234</strong></li></ul>";
?>