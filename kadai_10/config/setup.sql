-- データベースの作成
CREATE DATABASE IF NOT EXISTS iconico_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iconico_db;

-- 1. 候補者（テスト受付）テーブル
CREATE TABLE candidates (
    id INT(12) AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,  -- 受付番号（個人情報を持たせない）
    initial_score INT(3) DEFAULT 0,              -- 初期スコア（最大60点）
    has_alert TINYINT(1) DEFAULT 0,              -- 重大アラート有無（0:なし, 1:あり）
    audio_file_path VARCHAR(255) DEFAULT NULL,   -- 録音データの保存先パス
    final_rank VARCHAR(10) DEFAULT NULL,         -- 最終ランク（S, A, B, C）
    final_hourly_wage INT(10) DEFAULT NULL,      -- 決定時給
    evaluator_comment TEXT DEFAULT NULL,         -- 面接官の評価・ヒアリングメモ
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. 候補者の回答詳細テーブル
CREATE TABLE answers (
    id INT(12) AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT(12) NOT NULL,               -- candidatesテーブルのidと紐付け
    question_id VARCHAR(50) NOT NULL,            -- JSONの設問ID（例: "skill_q1"）
    score INT(2) NOT NULL,                       -- 獲得スコア（0, 1, 3）
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);