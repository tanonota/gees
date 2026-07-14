<?php
session_start();
require_once('../src/funcs.php');
sschk(); // ログインチェック

// 管理者(1)以外はアクセス不可
if ($_SESSION['kanri_flg'] !== 1) {
    exit('アクセス権限がありません。');
}

$pdo = db_conn();

// データの取得と集計（ダッシュボードと同じロジック）
$sql = "SELECT c.id AS candidate_id, c.receipt_number, c.created_at, a.question_id, a.score 
        FROM candidates c 
        JOIN answers a ON c.id = a.candidate_id 
        ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$candidates_map = [];
foreach ($all_data as $row) {
    $cid = $row['candidate_id'];
    $q_id = $row['question_id'];
    $score = (int)$row['score'];

    if (!isset($candidates_map[$cid])) {
        $candidates_map[$cid] = [
            'receipt_number' => $row['receipt_number'],
            'created_at' => $row['created_at'],
            'skill_score' => 0, 'mind_score' => 0,
            'skill_alerts' => 0, 'mind_alerts' => 0
        ];
    }
    if (strpos($q_id, 'skill_q') === 0) {
        $candidates_map[$cid]['skill_score'] += $score;
        if ($score === 0) $candidates_map[$cid]['skill_alerts']++;
    } elseif (strpos($q_id, 'mind_q') === 0) {
        $candidates_map[$cid]['mind_score'] += $score;
        if ($score === 0) $candidates_map[$cid]['mind_alerts']++;
    }
}

// 💡 ここからがCSV出力の魔法（HTTPヘッダーの変更）
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="candidates_result.csv"');

// メモリ上でファイルを書き込むための準備
$fp = fopen('php://output', 'w');

// 1行目：見出し行（Excel用にSJIS-winに変換）
$headers = ['受付番号', '受検日時', 'スキル得点', 'スキル重大アラート数', 'マインド得点', 'マインド重大アラート数'];
mb_convert_variables('SJIS-win', 'UTF-8', $headers);
fputcsv($fp, $headers);

// 2行目以降：データ行
foreach ($candidates_map as $c) {
    $row = [
        $c['receipt_number'],
        date('Y/m/d H:i', strtotime($c['created_at'])),
        $c['skill_score'],
        $c['skill_alerts'],
        $c['mind_score'],
        $c['mind_alerts']
    ];
    mb_convert_variables('SJIS-win', 'UTF-8', $row);
    fputcsv($fp, $row);
}

fclose($fp);
exit();