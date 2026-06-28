-- データベース：kadai_08
-- テーブル：kadai_08_table の作成用SQL

CREATE TABLE IF NOT EXISTS `kadai_08_table` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `q1_score` int(1) NOT NULL,
  `q2_score` int(1) NOT NULL,
  `q3_score` int(1) NOT NULL,
  `q4_score` int(1) NOT NULL,
  `q5_main` varchar(64) NOT NULL,
  `q5_sub` varchar(64) NOT NULL,
  `comment` text DEFAULT NULL,
  `indate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;