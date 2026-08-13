<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once('../src/funcs.php');
sschk(); // ログインチェック
$pdo = db_conn();

$receipt_number = $_GET['receipt_number'] ?? '';
if (!$receipt_number) exit('受付番号が指定されていません。');

// 1. 候補者の回答データを取得
$sql = "SELECT a.question_id, a.score, c.created_at FROM answers a JOIN candidates c ON a.candidate_id = c.id WHERE c.receipt_number = :receipt_number";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':receipt_number', $receipt_number, PDO::PARAM_STR);
$stmt->execute();
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($answers)) exit('回答データが見つかりません。');

// 2. 質問マスタ（questions.json）の読み込み（絶対パス指定に変更）
$json_path = __DIR__ . '/../config/questions.json';
if (!file_exists($json_path)) {
    // どこを探して見つからなかったのかを画面に表示する
    $target_dir = realpath(__DIR__ . '/../config') ?: 'configフォルダが見つかりません';
    exit("<h2 style='color:red;'>⚠️ エラー： questions.json が見つかりません！</h2><p>システムが探した場所: <b>{$target_dir}/questions.json</b></p>");
}

$questions_data = json_decode(file_get_contents($json_path), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    exit("<h2 style='color:red;'>⚠️ JSONの中身エラー： " . json_last_error_msg() . "</h2>");
}
if (!file_exists($json_path)) {
    // どこを探して見つからなかったのかを画面に表示する
    $target_dir = realpath(__DIR__ . '/../src') ?: 'srcフォルダが見つかりません';
    exit("<h2 style='color:red;'>⚠️ エラー： questions.json が見つかりません！</h2><p>システムが探した場所: <b>{$target_dir}/questions.json</b></p><p>ファイル名が隠れ拡張子で「questions.json.txt」などになっていないか、保存場所が間違っていないか確認してください。</p>");
}

$questions_data = json_decode(file_get_contents($json_path), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    exit("<h2 style='color:red;'>⚠️ JSONの中身エラー： " . json_last_error_msg() . "</h2>");
}
$question_map = [];
foreach ($questions_data as $q) {
    $question_map[$q['id']] = $q;
}

// 3. AIプロンプト用のテキストデータ（Q&A）を動的に生成
$txt_content = "【採用診断テスト 回答データ】\n";
$txt_content .= "受付番号: " . $receipt_number . "\n";
$txt_content .= "受検日時: " . $answers[0]['created_at'] . "\n\n";
$txt_content .= str_repeat("=", 40) . "\n\n";

foreach ($answers as $ans) {
    $q_id = $ans['question_id'];
    $score = (int)$ans['score'];
    $q_info = $question_map[$q_id] ?? null;
    
    $txt_content .= "■ 設問 ({$q_id})\n";
    if ($q_info) {
        $txt_content .= $q_info['text'] . "\n";
        $selected_text = "（不明な回答）";
        // 点数から選んだ選択肢のテキストを逆引きする
        foreach ($q_info['options'] as $opt) {
            if ($opt['score'] === $score) {
                $selected_text = $opt['text'];
                break;
            }
        }
        $txt_content .= "👉 【回答】({$score}点) " . $selected_text . "\n\n";
    } else {
        $txt_content .= "👉 【回答】スコア: " . $score . "\n\n";
    }
}

// 4. ZIPファイルの作成準備
$zip = new ZipArchive();
// 💡 修正箇所：Macのシステムフォルダを避け、確実に権限がある uploads フォルダの中に作る
$zipFileName = '../uploads/temp_export_' . $receipt_number . '_' . time() . '.zip';

if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit('ZIPファイルの作成に失敗しました。');
}

// 生成したテキストをZIPに追加（UTF-8なのでAIとの相性バツグンです）
$zip->addFromString("QA_Data_{$receipt_number}.txt", $txt_content);

// 録音ファイルをZIPに追加
$audio_file = '../uploads/record_' . $receipt_number . '.webm';
if (file_exists($audio_file)) {
    $zip->addFile($audio_file, "Interview_Audio_{$receipt_number}.webm");
} else {
    // 万が一録音がない場合はエラーテキストを入れておく親切設計
    $zip->addFromString("Audio_Not_Found.txt", "録音ファイルが見つかりませんでした（または録音が実施されていません）。");
}

$zip->close();

// 5. ZIPファイルをブラウザへダウンロードさせるHTTPヘッダー
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="AI_Analysis_Data_' . $receipt_number . '.zip"');
header('Content-Length: ' . filesize($zipFileName));
header('Pragma: no-cache');

readfile($zipFileName);
unlink($zipFileName); // サーバー容量節約のため一時ファイルを削除
exit();