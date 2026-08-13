<?php
session_start();
require_once('../src/funcs.php');
sschk();

// 音声ファイルが送信されたかチェック
if (isset($_FILES['audio']) && isset($_POST['receipt_number'])) {
    $receipt_number = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['receipt_number']); // 安全対策
    $upload_dir = '../uploads';

    // フォルダがなければ作成する
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // ファイル名の決定（例: record_受付番号.webm）
    $file_path = $upload_dir . '/record_' . $receipt_number . '.webm';

    // 一時ファイルから正規の場所へ移動して保存
    if (move_uploaded_file($_FILES['audio']['tmp_name'], $file_path)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received.']);
}