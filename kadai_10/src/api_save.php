<?php
// src/api_save.php
require_once 'funcs.php';

// 1. フロントから送信されたJSONデータを受け取る
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "データがありません"]);
    exit;
}

$pdo = db_conn();

// 2. スコアの合計を計算
$total_score = 0;
foreach($data as $ans) {
    $total_score += (int)$ans['score'];
}

// 3. 候補者（candidates）テーブルに仮登録し、受付番号を発行
$receipt_number = uniqid('ICO_'); // 重複しない受付番号を生成（例: ICO_64a1b2...）
$has_alert = 0; // 今回は簡易的に0（後ほどJSONと連携して判定します）

$sql_candidate = "INSERT INTO candidates (receipt_number, initial_score, has_alert) VALUES (:receipt_number, :initial_score, :has_alert)";
$stmt = $pdo->prepare($sql_candidate);
$stmt->bindValue(':receipt_number', $receipt_number, PDO::PARAM_STR);
$stmt->bindValue(':initial_score', $total_score, PDO::PARAM_INT);
$stmt->bindValue(':has_alert', $has_alert, PDO::PARAM_INT);
$status = $stmt->execute();

if ($status == false) {
    $error = $stmt->errorInfo();
    exit(json_encode(["status" => "error", "message" => "DB Error: " . $error[2]]));
}

// 保存した候補者のIDを取得
$candidate_id = $pdo->lastInsertId();

// 4. 回答詳細（answers）テーブルに各設問のスコアを保存
foreach($data as $ans) {
    $sql_ans = "INSERT INTO answers (candidate_id, question_id, score) VALUES (:candidate_id, :question_id, :score)";
    $stmt_ans = $pdo->prepare($sql_ans);
    $stmt_ans->bindValue(':candidate_id', $candidate_id, PDO::PARAM_INT);
    $stmt_ans->bindValue(':question_id', $ans['question_id'], PDO::PARAM_STR);
    $stmt_ans->bindValue(':score', $ans['score'], PDO::PARAM_INT);
    $stmt_ans->execute();
}

// 5. 成功結果をフロントエンドに返す
echo json_encode([
    "status" => "success", 
    "receipt_number" => $receipt_number,
    "message" => "データを保存しました"
]);
?>