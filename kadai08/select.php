<?php
// 1. 共通関数の読み込み
require_once('funcs.php');

// 2. タイムゾーンを日本時間に設定
date_default_timezone_set('Asia/Tokyo');

// 3. データベース接続（funcs.phpの関数を呼び出す）
$pdo = db_conn();

// 4. データの取得 (SELECT)
$stmt = $pdo->prepare("SELECT * FROM kadai_08_table ORDER BY id ASC");
$status = $stmt->execute();

if ($status == false) {
    $error = $stmt->errorInfo();
    exit("ErrorQuery:" . $error[2]);
}

// 全データを連想配列として一括取得
$values = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. データ集計用の変数準備
$data_list = array();
$total_count = 0;
$q1_sum = 0; $q2_sum = 0; $q3_sum = 0; $q4_sum = 0;
$alert_list = array();
$neg_counts = [
    'お叱り・ご不満' => 0,
    'やりづらさ・不安' => 0,
    'ヒヤッとしたこと' => 0
];
$heatmap = [
    'お客様のこと' => ['count'=>0, 'q1'=>0, 'q2'=>0, 'q3'=>0, 'q4'=>0],
    '現場・自分のこと' => ['count'=>0, 'q1'=>0, 'q2'=>0, 'q3'=>0, 'q4'=>0],
    '運営について' => ['count'=>0, 'q1'=>0, 'q2'=>0, 'q3'=>0, 'q4'=>0]
];

// 6. データのループ・集計処理
foreach($values as $row) {
    $q1 = (int)$row['q1_score'];
    $q2 = (int)$row['q2_score'];
    $q3 = (int)$row['q3_score'];
    $q4 = (int)$row['q4_score'];
    $main = $row['q5_main'];
    $sub  = $row['q5_sub'];
    $free = trim($row['comment']);
    
    $data_list[] = $row;
    $total_count++;
    
    $q1_sum += $q1; $q2_sum += $q2; $q3_sum += $q3; $q4_sum += $q4;
    
    // アラートの判定
    $is_alert = false;
    if (($q1 > 0 && $q1 <= 2) || ($q2 > 0 && $q2 <= 2) || ($q3 > 0 && $q3 <= 2) || ($q4 > 0 && $q4 <= 2)) {
        $is_alert = true;
    }
    if (array_key_exists($sub, $neg_counts)) {
        $is_alert = true;
        $neg_counts[$sub]++;
    }
    if ($is_alert && $free !== '') {
        $alert_list[] = $row; // コメントがあるものだけアラートに追加
    }
    
    // ヒートマップ用の集計
    if (array_key_exists($main, $heatmap)) {
        $heatmap[$main]['count']++;
        $heatmap[$main]['q1'] += $q1;
        $heatmap[$main]['q2'] += $q2;
        $heatmap[$main]['q3'] += $q3;
        $heatmap[$main]['q4'] += $q4;
    }
}

// 7. 計算とフォーマット
$q1_avg = $total_count > 0 ? round($q1_sum / $total_count, 1) : 0;
$q2_avg = $total_count > 0 ? round($q2_sum / $total_count, 1) : 0;
$q3_avg = $total_count > 0 ? round($q3_sum / $total_count, 1) : 0;
$q4_avg = $total_count > 0 ? round($q4_sum / $total_count, 1) : 0;

// 最新が上に来るように配列を逆順にする
$data_list = array_reverse($data_list);
$alert_list = array_reverse($alert_list);

function getHeatmapColor($score) {
    if ($score == 0) return '#f4f8f9'; 
    if ($score <= 2.9) return '#ffcdd2'; 
    if ($score <= 3.9) return '#fff9c4'; 
    return '#c8e6c9'; 
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理ダッシュボード｜現場の声アンケート</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Kosugi Maru', sans-serif; margin: 0; padding: 30px 20px; background-color: #f4f8f9; color: #333; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2a6170; font-size: 26px; margin-bottom: 30px; text-align: left; }
        h2 { font-size: 18px; color: #2a6170; margin: 0 0 15px 0; border-bottom: 2px solid #A3D6E1; padding-bottom: 5px; display: inline-block; }
        .section-box { margin-bottom: 50px; }

        .summary-wrapper { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .summary-card { background-color: #fff; padding: 20px; border-radius: 10px; flex: 1; min-width: 150px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-top: 4px solid #A3D6E1; text-align: center; }
        .summary-card.total { border-top-color: #2a6170; background-color: #eef7f9; }
        .card-label { font-size: 13px; color: #666; margin-bottom: 5px; font-weight: bold; }
        .card-value { font-size: 26px; font-weight: bold; color: #333; }
        .card-value span { font-size: 14px; font-weight: normal; color: #888; margin-left: 2px; }
        .low-score { color: #de5246; font-weight: bold; }

        .charts-container { display: flex; gap: 20px; margin-bottom: 40px; flex-wrap: wrap; }
        .chart-box { background-color: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; flex: 1; min-width: 300px; }
        .chart-title { text-align: center; font-size: 15px; color: #444; margin-bottom: 15px; font-weight: bold; }

        .alert-panel { background-color: #fff3f3; border: 2px solid #ffcdd2; border-radius: 10px; padding: 20px; box-shadow: 0 4px 15px rgba(222,82,70,0.15); margin-bottom: 40px; }
        .alert-title { color: #d32f2f; font-size: 18px; font-weight: bold; margin-bottom: 15px; }

        .table-responsive { background-color: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto; padding: 10px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; min-width: 900px; }
        th { background-color: #fcfdfe; color: #2a6170; font-weight: bold; padding: 12px; border-bottom: 2px solid #eef2f4; }
        td { padding: 12px; border-bottom: 1px solid #eef2f4; line-height: 1.5; vertical-align: top; }
        tr:hover { background-color: #fafdfd; }
        
        .heatmap-table { min-width: auto; text-align: center; font-size: 14px; }
        .heatmap-table th { text-align: center; }
        .heatmap-cell { font-weight: bold; font-size: 16px; border: 1px solid #fff; }

        .badge { display: inline-block; padding: 4px 8px; border-radius: 15px; font-size: 11px; font-weight: bold; background-color: #e8e8e8; color: #666; }
        .badge-alert { background-color: #ffebee; color: #d32f2f; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>管理ダッシュボード</h1>

    <div class="summary-wrapper">
        <div class="summary-card total">
            <div class="card-label">総回答数</div>
            <div class="card-value"><?php echo $total_count; ?><span>件</span></div>
        </div>
        <div class="summary-card">
            <div class="card-label">Q1.おすすめ度</div>
            <div class="card-value <?php if($q1_avg > 0 && $q1_avg <= 3) echo 'low-score'; ?>"><?php echo $q1_avg; ?><span>/ 5</span></div>
        </div>
        <div class="summary-card">
            <div class="card-label">Q2.現場の安全性</div>
            <div class="card-value <?php if($q2_avg > 0 && $q2_avg <= 3) echo 'low-score'; ?>"><?php echo $q2_avg; ?><span>/ 5</span></div>
        </div>
        <div class="summary-card">
            <div class="card-label">Q3.本部のサポート</div>
            <div class="card-value <?php if($q3_avg > 0 && $q3_avg <= 3) echo 'low-score'; ?>"><?php echo $q3_avg; ?><span>/ 5</span></div>
        </div>
        <div class="summary-card">
            <div class="card-label">Q4.労働環境・待遇</div>
            <div class="card-value <?php if($q4_avg > 0 && $q4_avg <= 3) echo 'low-score'; ?>"><?php echo $q4_avg; ?><span>/ 5</span></div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-box">
            <div class="chart-title">各項目のスコアバランス</div>
            <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
        <div class="chart-box">
            <div class="chart-title">ネガティブ要因の割合（全体：<?php echo array_sum($neg_counts); ?>件）</div>
            <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                <?php if (array_sum($neg_counts) > 0): ?>
                    <canvas id="pieChart"></canvas>
                <?php else: ?>
                    <p style="color: #999; text-align: center; margin-top: 100px;">ネガティブな報告はありません。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section-box">
        <h2>カテゴリ別 ヒートマップ分析</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 10px;">色分け基準：<span style="background:#ffcdd2; padding:2px 5px;">2.9以下(危険)</span> <span style="background:#fff9c4; padding:2px 5px;">3.9以下(注意)</span> <span style="background:#c8e6c9; padding:2px 5px;">4.0以上(良好)</span></p>
        <div class="table-responsive">
            <table class="heatmap-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">発生カテゴリ</th>
                        <th style="width: 10%;">件数</th>
                        <th style="width: 17%;">Q1. おすすめ度</th>
                        <th style="width: 17%;">Q2. 現場の安全性</th>
                        <th style="width: 17%;">Q3. 本部サポート</th>
                        <th style="width: 17%;">Q4. 労働環境</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($heatmap as $cat_name => $data): 
                        $c = $data['count'];
                        $h_q1 = $c > 0 ? round($data['q1'] / $c, 1) : 0;
                        $h_q2 = $c > 0 ? round($data['q2'] / $c, 1) : 0;
                        $h_q3 = $c > 0 ? round($data['q3'] / $c, 1) : 0;
                        $h_q4 = $c > 0 ? round($data['q4'] / $c, 1) : 0;
                    ?>
                    <tr>
                        <td style="text-align: left; font-weight: bold; color:#2a6170;"><?php echo $cat_name; ?></td>
                        <td><?php echo $c; ?> 件</td>
                        <td class="heatmap-cell" style="background-color: <?php echo getHeatmapColor($h_q1); ?>"><?php echo $h_q1 > 0 ? $h_q1 : '-'; ?></td>
                        <td class="heatmap-cell" style="background-color: <?php echo getHeatmapColor($h_q2); ?>"><?php echo $h_q2 > 0 ? $h_q2 : '-'; ?></td>
                        <td class="heatmap-cell" style="background-color: <?php echo getHeatmapColor($h_q3); ?>"><?php echo $h_q3 > 0 ? $h_q3 : '-'; ?></td>
                        <td class="heatmap-cell" style="background-color: <?php echo getHeatmapColor($h_q4); ?>"><?php echo $h_q4 > 0 ? $h_q4 : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-box alert-panel">
        <div class="alert-title">要対応アラート（スコア2以下・ネガティブ報告 ＆ コメントあり）</div>
        <div class="table-responsive" style="box-shadow: none; border: 1px solid #ffcdd2;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%; background: #fff;">回答日時</th>
                        <th style="width: 25%; background: #fff;">低いスコアの項目</th>
                        <th style="width: 20%; background: #fff;">カテゴリ</th>
                        <th style="width: 40%; background: #fff;">詳細コメント</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alert_list)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #666;">現在、対応が必要なアラートはありません。</td></tr>
                    <?php else: ?>
                        <?php foreach ($alert_list as $row): 
                            $date = isset($row['indate']) ? h($row['indate']) : '-';
                            $free = isset($row['comment']) ? h($row['comment']) : '';
                            $sub  = isset($row['q5_sub']) ? h($row['q5_sub']) : '-';
                            
                            $lows = [];
                            if (isset($row['q1_score']) && $row['q1_score'] <= 2 && $row['q1_score'] > 0) $lows[] = "Q1(".$row['q1_score']."点)";
                            if (isset($row['q2_score']) && $row['q2_score'] <= 2 && $row['q2_score'] > 0) $lows[] = "Q2(".$row['q2_score']."点)";
                            if (isset($row['q3_score']) && $row['q3_score'] <= 2 && $row['q3_score'] > 0) $lows[] = "Q3(".$row['q3_score']."点)";
                            if (isset($row['q4_score']) && $row['q4_score'] <= 2 && $row['q4_score'] > 0) $lows[] = "Q4(".$row['q4_score']."点)";
                            $low_str = empty($lows) ? "なし" : implode(", ", $lows);
                        ?>
                        <tr>
                            <td><?php echo $date; ?></td>
                            <td class="low-score"><?php echo $low_str; ?></td>
                            <td><span class="badge badge-alert"><?php echo $sub; ?></span></td>
                            <td><?php echo $free; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-box">
        <h2>全回答データ一覧</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">日時</th>
                        <th style="width: 5%;">Q1</th>
                        <th style="width: 5%;">Q2</th>
                        <th style="width: 5%;">Q3</th>
                        <th style="width: 5%;">Q4</th>
                        <th style="width: 13%;">メインカテゴリ</th>
                        <th style="width: 15%;">サブカテゴリ</th>
                        <th style="width: 40%;">詳細コメント</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" style="text-align: center; padding: 30px;">データがありません。</td></tr>
                    <?php else: ?>
                        <?php foreach ($data_list as $row): 
                            $date = isset($row['indate']) ? h($row['indate']) : '-';
                            $q1   = isset($row['q1_score']) ? h($row['q1_score']) : '-';
                            $q2   = isset($row['q2_score']) ? h($row['q2_score']) : '-';
                            $q3   = isset($row['q3_score']) ? h($row['q3_score']) : '-';
                            $q4   = isset($row['q4_score']) ? h($row['q4_score']) : '-';
                            $main = isset($row['q5_main']) ? h($row['q5_main']) : '-';
                            $sub  = isset($row['q5_sub']) ? h($row['q5_sub']) : '-';
                            $free = isset($row['comment']) ? h($row['comment']) : '';
                        ?>
                        <tr>
                            <td style="color: #999; font-size: 11px;"><?php echo $date; ?></td>
                            <td class="<?php if((int)$q1 > 0 && (int)$q1 <= 3) echo 'low-score'; ?>"><?php echo $q1; ?></td>
                            <td class="<?php if((int)$q2 > 0 && (int)$q2 <= 3) echo 'low-score'; ?>"><?php echo $q2; ?></td>
                            <td class="<?php if((int)$q3 > 0 && (int)$q3 <= 3) echo 'low-score'; ?>"><?php echo $q3; ?></td>
                            <td class="<?php if((int)$q4 > 0 && (int)$q4 <= 3) echo 'low-score'; ?>"><?php echo $q4; ?></td>
                            <td><span class="badge"><?php echo $main; ?></span></td>
                            <td><span class="badge"><?php echo $sub; ?></span></td>
                            <td><?php echo $free !== '' ? $free : '<span style="color:#ccc;">（未記入）</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    if (document.getElementById('radarChart')) {
        const ctxRadar = document.getElementById('radarChart').getContext('2d');
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: ['Q1. おすすめ度', 'Q2. 現場の安全性', 'Q3. 本部サポート', 'Q4. 労働環境'],
                datasets: [{
                    label: '平均スコア',
                    data: [<?php echo $q1_avg; ?>, <?php echo $q2_avg; ?>, <?php echo $q3_avg; ?>, <?php echo $q4_avg; ?>],
                    backgroundColor: 'rgba(163, 214, 225, 0.4)',
                    borderColor: 'rgba(42, 97, 112, 1)',
                    pointBackgroundColor: 'rgba(42, 97, 112, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { r: { angleLines: { display: true }, suggestedMin: 0, suggestedMax: 5, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    <?php if (array_sum($neg_counts) > 0): ?>
    if (document.getElementById('pieChart')) {
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut', 
            data: {
                labels: ['お叱り・ご不満', 'やりづらさ・不安', 'ヒヤッとしたこと'],
                datasets: [{
                    data: [
                        <?php echo $neg_counts['お叱り・ご不満']; ?>,
                        <?php echo $neg_counts['やりづらさ・不安']; ?>,
                        <?php echo $neg_counts['ヒヤッとしたこと']; ?>
                    ],
                    backgroundColor: ['#ffb7b2', '#ffdac1', '#e2f0cb'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } }
            }
        });
    }
    <?php endif; ?>
</script>

</body>
</html>