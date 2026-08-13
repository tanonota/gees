<?php
session_start();
require_once('../src/funcs.php');
sschk(); // ログインチェック

// 💡 権限チェック：管理者(1)以外はエラーではじく（RBACの実践）
if ($_SESSION['kanri_flg'] !== 1) {
    exit('アクセス権限がありません。');
}

$pdo = db_conn();

// 1. すべての回答データを候補者情報と一緒に取得（JOIN）
$sql = "SELECT c.id AS candidate_id, c.receipt_number, c.created_at, a.question_id, a.score 
        FROM candidates c 
        JOIN answers a ON c.id = a.candidate_id 
        ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. データの集計用変数の準備
$candidates_map = []; // 候補者ごとの集計データ
$total_skill = 0;
$total_mind = 0;
$skill_axes_total = [0, 0, 0, 0, 0];
$mind_axes_total = [0, 0, 0, 0, 0];
$total_answers_count = 0;

// 3. ループで全データを集計
foreach ($all_data as $row) {
    $cid = $row['candidate_id'];
    $q_id = $row['question_id'];
    $score = (int)$row['score'];

    // 候補者ごとのデータ枠を初期化
    if (!isset($candidates_map[$cid])) {
        $candidates_map[$cid] = [
            'receipt_number' => $row['receipt_number'],
            'created_at' => $row['created_at'],
            'skill_score' => 0, 'mind_score' => 0,
            'skill_alerts' => 0, 'mind_alerts' => 0
        ];
    }

    $total_answers_count++;

    // スキルとマインドの集計
    if (strpos($q_id, 'skill_q') === 0) {
        $candidates_map[$cid]['skill_score'] += $score;
        $total_skill += $score;
        if ($score === 0) $candidates_map[$cid]['skill_alerts']++;
        
        $num = (int)str_replace('skill_q', '', $q_id);
        if ($num > 0 && $num <= 10) {
            $skill_axes_total[ceil($num / 2) - 1] += $score;
        }
    } elseif (strpos($q_id, 'mind_q') === 0) {
        $candidates_map[$cid]['mind_score'] += $score;
        $total_mind += $score;
        if ($score === 0) $candidates_map[$cid]['mind_alerts']++;
        
        $num = (int)str_replace('mind_q', '', $q_id);
        if ($num > 0 && $num <= 10) {
            $mind_axes_total[ceil($num / 2) - 1] += $score;
        }
    }
}

// 4. 統計データの算出
$total_candidates = count($candidates_map);
$avg_skill = $total_candidates > 0 ? round($total_skill / $total_candidates, 1) : 0;
$avg_mind = $total_candidates > 0 ? round($total_mind / $total_candidates, 1) : 0;

$alert_candidates = 0; // 重大アラート（0点）を出した候補者の数
foreach ($candidates_map as $c) {
    if ($c['skill_alerts'] > 0 || $c['mind_alerts'] > 0) {
        $alert_candidates++;
    }
}
$safe_candidates = $total_candidates - $alert_candidates;

// グラフ用データ（平均値）の作成
$skill_axes_avg = array_map(function($val) use ($total_candidates) { return $total_candidates > 0 ? round($val / $total_candidates, 1) : 0; }, $skill_axes_total);
$mind_axes_avg = array_map(function($val) use ($total_candidates) { return $total_candidates > 0 ? round($val / $total_candidates, 1) : 0; }, $mind_axes_total);

$skill_json = json_encode($skill_axes_avg);
$mind_json = json_encode($mind_axes_avg);

// ランク判定関数（共通）
function determineRank($score, $alerts) {
    if ($alerts > 0 || $score <= 13) return ['rank' => 'C', 'color' => '#fdf0f0', 'text' => '#e74c3c']; // 危険色
    if ($score >= 14 && $score <= 19) return ['rank' => 'B', 'color' => '#fef5e7', 'text' => '#f39c12']; // 注意色
    if ($score >= 20 && $score <= 25) return ['rank' => 'A', 'color' => '#eaf2f8', 'text' => '#3498db'];
    return ['rank' => 'S', 'color' => '#eafaf1', 'text' => '#2ecc71']; // 良好色
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>総合管理ダッシュボード</title>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'M PLUS Rounded 1c', sans-serif; background-color: #f4f7f8; color: #333; padding: 30px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header-wrap { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-title { font-size: 1.5rem; color: #2c5263; font-weight: bold; }
        .btn-logout { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        
        /* 上部のサマリーカード */
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .summary-card { background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-top: 4px solid #2c5263; }
        .summary-card h3 { font-size: 0.9rem; color: #666; margin: 0 0 10px 0; font-weight: normal;}
        .summary-card .value { font-size: 2rem; font-weight: bold; color: #333; }
        .summary-card .value span { font-size: 1rem; color: #999; }

        /* 中段のグラフ領域 */
        .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .chart-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .chart-card h3 { font-size: 1rem; color: #555; text-align: center; margin-bottom: 20px; }
        .canvas-container { position: relative; width: 100%; max-width: 400px; margin: 0 auto; }

        /* ヒートマップ風テーブル */
        .table-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: center; margin-top: 10px; }
        th { background-color: #f8f9fa; color: #555; padding: 12px; font-size: 0.9rem; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        .td-rank { font-weight: bold; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-wrap">
        <div class="header-title">管理ダッシュボード（全体統計）</div>
        <div>
            <!-- 💡 CSVエクスポートボタンを追加 -->
            <a href="export_csv.php" style="background: #27ae60; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-right: 10px;">CSVダウンロード</a>
            <a href="logout.php" class="btn-logout">ログアウト</a>
        </div>
    </div>

    <!-- サマリーカード -->
    <div class="summary-grid">
        <div class="summary-card" style="border-top-color: #eaf2f8;">
            <h3>総受検者数</h3>
            <div class="value"><?= $total_candidates ?> <span>名</span></div>
        </div>
        <div class="summary-card">
            <h3>スキル 平均スコア</h3>
            <div class="value"><?= $avg_skill ?> <span>/ 30点</span></div>
        </div>
        <div class="summary-card">
            <h3>マインド 平均スコア</h3>
            <div class="value"><?= $avg_mind ?> <span>/ 30点</span></div>
        </div>
        <div class="summary-card" style="border-top-color: #fdf0f0;">
            <h3>アラート発生者数</h3>
            <div class="value" style="color: #e74c3c;"><?= $alert_candidates ?> <span>名</span></div>
        </div>
    </div>

    <!-- グラフ領域 -->
    <div class="chart-grid">
        <div class="chart-card">
            <h3>全体スコアバランス（平均値）</h3>
            <div class="canvas-container">
                <canvas id="averageRadarChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>ネガティブ要因（アラート）の割合</h3>
            <div class="canvas-container" style="max-width: 300px;">
                <canvas id="alertPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- カテゴリ別 ヒートマップ分析 -->
    <div class="table-card">
        <h3 style="margin-top:0; color:#2c5263;">受検者別 ヒートマップ分析</h3>
        <p style="font-size: 0.85rem; color: #666;">
            色分け基準：<span style="background:#fdf0f0; color:#e74c3c; padding:2px 6px;">Cランク(危険)</span>
            <span style="background:#fef5e7; color:#f39c12; padding:2px 6px;">Bランク(注意)</span>
            <span style="background:#eaf2f8; color:#3498db; padding:2px 6px;">Aランク(良好)</span>
            <span style="background:#eafaf1; color:#2ecc71; padding:2px 6px;">Sランク(プロ)</span>
        </p>
        <table>
            <tr>
                <th>受付番号</th>
                <th>受検日時</th>
                <th>スキル得点</th>
                <th>スキル評価</th>
                <th>マインド得点</th>
                <th>マインド評価</th>
                <th>詳細</th>
            </tr>
            <?php foreach ($candidates_map as $c): 
                $s_res = determineRank($c['skill_score'], $c['skill_alerts']);
                $m_res = determineRank($c['mind_score'], $c['mind_alerts']);
            ?>
            <tr>
                <td><?= htmlspecialchars($c['receipt_number']) ?></td>
                <td><?= date('Y/m/d H:i', strtotime($c['created_at'])) ?></td>
                <td><?= $c['skill_score'] ?>点</td>
                <td class="td-rank" style="background-color: <?= $s_res['color'] ?>; color: <?= $s_res['text'] ?>;">
                    <?= $s_res['rank'] ?> <?= $c['skill_alerts']>0 ? '⚠️' : '' ?>
                </td>
                <td><?= $c['mind_score'] ?>点</td>
                <td class="td-rank" style="background-color: <?= $m_res['color'] ?>; color: <?= $m_res['text'] ?>;">
                    <?= $m_res['rank'] ?> <?= $c['mind_alerts']>0 ? '⚠️' : '' ?>
                </td>
                <td>
                    <!-- 面接官用個別ダッシュボードへのリンク -->
                    <a href="dashboard.php?receipt_number=<?= htmlspecialchars($c['receipt_number']) ?>" style="color: #3498db; text-decoration: none;">詳細表示</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script>
    // 💡 Chart.js によるグラフ描画
    // レーダーチャート（全体平均）
    const skillData = <?= $skill_json ?>;
    const mindData = <?= $mind_json ?>;
    
    new Chart(document.getElementById('averageRadarChart'), {
        type: 'radar',
        data: {
            labels: ['軸1', '軸2', '軸3', '軸4', '軸5'], // 今回は簡易的に軸番号を表示
            datasets: [
                {
                    label: 'スキル平均',
                    data: skillData,
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                },
                {
                    label: 'マインド平均',
                    data: mindData,
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                }
            ]
        },
        options: { scales: { r: { min: 0, max: 6, ticks: { stepSize: 2 } } } }
    });

    // ドーナツグラフ（ネガティブ要因の割合）
    new Chart(document.getElementById('alertPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['アラートあり(懸念)', 'アラートなし(安全)'],
            datasets: [{
                data: [<?= $alert_candidates ?>, <?= $safe_candidates ?>],
                backgroundColor: ['#ff9999', '#c2e5d3'],
                borderWidth: 0
            }]
        },
        options: { cutout: '60%' }
    });
</script>

</body>
</html>
