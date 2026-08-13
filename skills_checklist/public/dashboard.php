<?php
session_start();
require_once('../src/funcs.php');
sschk(); // ログインチェック
$pdo = db_conn();

$receipt_number = $_GET['receipt_number'] ?? '';
if (!$receipt_number) exit('受付番号が指定されていません。');

// 権限の判定（0: 一般面接官, 1: 総合管理者）
$is_interviewer = ($_SESSION['kanri_flg'] === 0);

$sql = "SELECT a.question_id, a.score FROM answers a JOIN candidates c ON a.candidate_id = c.id WHERE c.receipt_number = :receipt_number";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':receipt_number', $receipt_number, PDO::PARAM_STR);
$stmt->execute();
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$skill_score = 0; $mind_score = 0;
$skill_alerts = 0; $mind_alerts = 0;
$skill_axes = [0, 0, 0, 0, 0];
$mind_axes = [0, 0, 0, 0, 0];

$json_data = file_get_contents('../src/prompts.json');
$prompt_master = json_decode($json_data, true);

$hearing_prompts = []; 

foreach ($answers as $ans) {
    $q_id = $ans['question_id'];
    $score = (int)$ans['score'];

    if (strpos($q_id, 'skill_q') === 0) {
        $skill_score += $score;
        if ($score === 0) $skill_alerts++;
        $num = (int)str_replace('skill_q', '', $q_id);
        if ($num > 0 && $num <= 10) $skill_axes[ceil($num / 2) - 1] += $score;
    } elseif (strpos($q_id, 'mind_q') === 0) {
        $mind_score += $score;
        if ($score === 0) $mind_alerts++;
        $num = (int)str_replace('mind_q', '', $q_id);
        if ($num > 0 && $num <= 10) $mind_axes[ceil($num / 2) - 1] += $score;
    }

    if ($score <= 1) {
        $category = strpos($q_id, 'skill') === 0 ? '【スキル】' : '【マインド】';
        $info = $prompt_master[$q_id] ?? null;
        
        if ($info) {
            $score_key = 'score_' . $score;
            $hearing_prompts[] = [
                'category' => $category,
                'question' => $info['question'],
                'score'    => $score,
                'message'  => $info[$score_key] ?? '確認が必要です。'
            ];
        } else {
            $hearing_prompts[] = [
                'category' => $category,
                'question' => "設問: {$q_id} （※JSON未登録）",
                'score'    => $score,
                'message'  => 'この回答について深掘りヒアリングを行ってください。'
            ];
        }
    }
}

function determineRank($score, $alerts) {
    if ($alerts > 0 || $score <= 13) return ['rank' => 'C', 'label' => 'リスク懸念・不適格', 'color' => '#e74c3c'];
    if ($score >= 14 && $score <= 19) return ['rank' => 'B', 'label' => '要基礎研修・マインド調整', 'color' => '#f39c12'];
    if ($score >= 20 && $score <= 25) return ['rank' => 'A', 'label' => '優良適性・経験者レベル', 'color' => '#3498db'];
    return ['rank' => 'S', 'label' => 'プロレベル・ブランド体現', 'color' => '#2ecc71'];
}

$skill_result = determineRank($skill_score, $skill_alerts);
$mind_result = determineRank($mind_score, $mind_alerts);
$skill_json = json_encode($skill_axes);
$mind_json = json_encode($mind_axes);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個別評価ダッシュボード</title>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'M PLUS Rounded 1c', sans-serif; background-color: #f0f4f8; color: #333; padding: 30px; margin: 0; }
        .header-wrap { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; max-width: 1000px; margin: 0 auto 20px; }
        .header-title { font-size: 1.5rem; color: #2c5263; font-weight: bold; border-left: 5px solid #2c5263; padding-left: 10px; }
        .receipt-info { font-size: 1rem; color: #666; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 1000px; margin: 0 auto 20px; }
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 1000px; margin: 0 auto 30px; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-top: 5px solid #ccc; display: flex; flex-direction: column; align-items: center;}
        .card h2 { margin: 0 0 15px 0; font-size: 1.1rem; color: #555; }
        .score-value { font-size: 3rem; font-weight: bold; margin-bottom: 10px; }
        .score-value span { font-size: 1.2rem; color: #999; font-weight: normal; }
        .rank-badge { padding: 5px 20px; border-radius: 20px; color: #fff; font-weight: bold; font-size: 1.2rem; margin-bottom: 10px; }
        .label { font-size: 1rem; font-weight: bold; margin-bottom: 15px; }
        .alert-box { width: 100%; padding: 10px; background-color: #fdf0f0; color: #e74c3c; border-radius: 4px; font-size: 0.9rem; text-align: left; box-sizing: border-box; }
        .chart-container { width: 100%; max-width: 350px; margin-top: auto; }
        .btn-logout { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold;}
        
        .prompt-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-top: 5px solid #f39c12; }
        .prompt-container h2 { font-size: 1.3rem; color: #2c5263; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;}
        .prompt-list { list-style: none; padding: 0; margin: 0; }
        .prompt-item { padding: 20px; border-bottom: 1px solid #eee; display: flex; align-items: flex-start; gap: 20px;}
        .prompt-item:last-child { border-bottom: none; }
        .prompt-action { display: flex; flex-direction: column; align-items: center; gap: 8px; min-width: 60px; }
        .score-badge { padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 1rem; color: #fff; text-align: center; width: 100%; box-sizing: border-box;}
        .score-0 { background-color: #e74c3c; }
        .score-1 { background-color: #f39c12; }
        .checkbox-wrapper { margin-top: 5px; }
        .checkbox-wrapper label { display: flex; flex-direction: column; align-items: center; font-size: 0.8rem; color: #555; cursor: pointer; font-weight: bold;}
        .checkbox-wrapper input[type="checkbox"] { transform: scale(1.5); margin-bottom: 8px; cursor: pointer; accent-color: #2c5263;}
        .prompt-text h4 { margin: 0 0 10px 0; font-size: 1.05rem; color: #2c5263; line-height: 1.4; }
        .prompt-text p { margin: 0; font-size: 0.95rem; color: #444; line-height: 1.6; background-color: #f9f9f9; padding: 12px; border-radius: 4px; border-left: 4px solid #ddd;}
        .score-0 + .checkbox-wrapper + .prompt-text p { border-left-color: #e74c3c; background-color: #fdf0f0; }
        .score-1 + .checkbox-wrapper + .prompt-text p { border-left-color: #f39c12; background-color: #fef5e7; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex; justify-content: center; align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #fff; padding: 40px; border-radius: 12px;
            max-width: 500px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-content h3 { color: #2c5263; margin-top: 0; font-size: 1.5rem;}
        .modal-message { font-size: 1.1rem; line-height: 1.6; color: #333; margin-bottom: 25px;}
        .btn-primary {
            background: #3498db; color: white; border: none; padding: 15px 30px;
            font-size: 1.2rem; border-radius: 8px; cursor: pointer; font-weight: bold;
            transition: 0.2s;
        }
        .btn-primary:hover { background: #2980b9; }
        .recording-badge {
            display: none; background: #e74c3c; color: white; padding: 5px 15px;
            border-radius: 20px; font-weight: bold; font-size: 0.9rem;
            animation: blink 2s infinite; 
        }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body>

<?php if ($is_interviewer): ?>
<!-- 録音確認モーダル -->
<div id="recordingModal" class="modal-overlay">
    <div class="modal-content">
        <h3>🎙️ 面接（ヒアリング）の開始</h3>
        <p class="modal-message">
            情報の正確性を担保するため、本面接は録音をさせていただきます。<br><br>
            <strong style="color: #e74c3c; font-size: 1.2rem;">必ず候補者の方へ了承を得てから</strong><br>録音を開始してください。
        </p>
        <button id="startRecordBtn" class="btn-primary" style="background: #e74c3c;">同意を得て 録音開始</button>
    </div>
</div>

<!-- 💡 ヒアリング完了モーダル -->
<div id="completeModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>✅ ヒアリング完了</h3>
        <p class="modal-message">
            すべての必須項目を聞き終えました。<br>面接を終了し、録音データを保存しますか？
        </p>
        <button id="completeBtn" class="btn-primary" style="background: #2ecc71;">面接を終了して保存</button>
    </div>
</div>
<?php endif; ?>

<div class="header-wrap">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div class="header-title">個別評価ダッシュボード</div>
        <?php if ($is_interviewer): ?>
            <div id="recordingBadge" class="recording-badge">🔴 録音中</div>
        <?php else: ?>
            <!-- 💡 管理者用アクションボタン群 -->
            <a href="admin_dashboard.php" style="background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.9rem;">🔙 一覧へ戻る</a>
            
            <!-- 💡 AI分析用ZIPダウンロードボタン -->
            <a href="download_zip.php?receipt_number=<?= htmlspecialchars($receipt_number, ENT_QUOTES, 'UTF-8') ?>" style="background: #9b59b6; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.9rem; margin-left: 10px;">📦 AI分析用データ(ZIP)をダウンロード</a>
        <?php endif; ?>
    </div>
    <a href="logout.php" class="btn-logout">ログアウト</a>
</div>

<div class="receipt-info">受付番号：<strong><?= htmlspecialchars($receipt_number, ENT_QUOTES, 'UTF-8') ?></strong> の診断結果およびスコアバランス</div>

<div class="grid-container">
    <div class="card" style="border-top-color: <?= $skill_result['color'] ?>;">
        <h2>スキル評価（実務知識）</h2>
        <div class="score-value" style="color: <?= $skill_result['color'] ?>;"><?= $skill_score ?><span> / 30点</span></div>
        <div class="rank-badge" style="background-color: <?= $skill_result['color'] ?>;">Rank <?= $skill_result['rank'] ?></div>
        <div class="label" style="color: #555;"><?= $skill_result['label'] ?></div>
        <div class="chart-container"><canvas id="skillChart"></canvas></div>
    </div>

    <div class="card" style="border-top-color: <?= $mind_result['color'] ?>;">
        <h2>マインド評価（プロ意識）</h2>
        <div class="score-value" style="color: <?= $mind_result['color'] ?>;"><?= $mind_score ?><span> / 30点</span></div>
        <div class="rank-badge" style="background-color: <?= $mind_result['color'] ?>;">Rank <?= $mind_result['rank'] ?></div>
        <div class="label" style="color: #555;"><?= $mind_result['label'] ?></div>
        <div class="chart-container"><canvas id="mindChart"></canvas></div>
    </div>
</div>

<div class="prompt-container">
    <h2>🗣️ 面接時の深掘りヒアリング・プロンプト</h2>
    <?php if (empty($hearing_prompts)): ?>
        <p style="color: #2ecc71; font-weight: bold; font-size: 1.1rem; text-align: center; padding: 20px;">
            ✨ 素晴らしい結果です！1点以下の回答はありません。<br>自社ブランドへの期待を伝え、魅力付けを行ってください。
        </p>
    <?php else: ?>
        <ul class="prompt-list" id="promptList">
            <?php foreach ($hearing_prompts as $p): ?>
                <li class="prompt-item">
                    <div class="prompt-action">
                        <div class="score-badge <?= $p['score'] === 0 ? 'score-0' : 'score-1' ?>">
                            <?= $p['score'] ?>点
                        </div>
                        <?php if ($is_interviewer): ?>
                        <div class="checkbox-wrapper">
                            <label><input type="checkbox" class="hearing-check">確認済</label>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="prompt-text">
                        <h4><?= $p['category'] ?> Q. <?= htmlspecialchars($p['question'], ENT_QUOTES) ?></h4>
                        <p><?= htmlspecialchars($p['message'], ENT_QUOTES) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
    const skillData = <?= $skill_json ?>;
    const mindData = <?= $mind_json ?>;
    const chartOptions = { scales: { r: { min: 0, max: 6, ticks: { stepSize: 2 }, pointLabels: { font: { size: 10 } } } }, plugins: { legend: { display: false } } };

    new Chart(document.getElementById('skillChart'), { type: 'radar', data: { labels: ['洗剤・素材', '衛生管理', 'タスク交渉', '資産尊重', 'リスク予防'], datasets: [{ data: skillData, backgroundColor: 'rgba(52, 152, 219, 0.2)', borderColor: 'rgba(52, 152, 219, 1)', pointBackgroundColor: 'rgba(52, 152, 219, 1)' }] }, options: chartOptions });
    new Chart(document.getElementById('mindChart'), { type: 'radar', data: { labels: ['誠実・報告', '距離感', 'プロの裏方', '情緒安定', '察知力'], datasets: [{ data: mindData, backgroundColor: 'rgba(46, 204, 113, 0.2)', borderColor: 'rgba(46, 204, 113, 1)', pointBackgroundColor: 'rgba(46, 204, 113, 1)' }] }, options: chartOptions });

    <?php if ($is_interviewer): ?>
    let mediaRecorder;
    let audioChunks = [];

    document.getElementById('startRecordBtn').addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = event => {
                if (event.data.size > 0) audioChunks.push(event.data);
            };
            
            // 💡 録音停止時の処理（サーバーへ非同期送信）
            mediaRecorder.onstop = async () => {
                document.getElementById('recordingBadge').style.display = 'none';
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const formData = new FormData();
                formData.append('audio', audioBlob);
                formData.append('receipt_number', '<?= htmlspecialchars($receipt_number, ENT_QUOTES) ?>');

                try {
                    const response = await fetch('upload_audio.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if(result.status === 'success') {
                        alert('面接と録音が完了しました。お疲れ様でした！');
                        window.location.href = 'logout.php'; // 完了後はログアウト
                    } else {
                        alert('保存に失敗しました。');
                    }
                } catch(e) {
                    alert('通信エラーが発生しました。');
                }
            };

            mediaRecorder.start();
            document.getElementById('recordingModal').style.display = 'none';
            document.getElementById('recordingBadge').style.display = 'block';
        } catch (err) {
            alert("マイクへのアクセスが拒否されたか、エラーが発生しました。");
        }
    });

    // 💡 全チェックの監視ロジック
    const checkboxes = document.querySelectorAll('.hearing-check');
    const completeModal = document.getElementById('completeModal');
    
    if(checkboxes.length > 0) {
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                // 配列に変換し、全てがチェック(checked === true)か判定
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                if (allChecked) {
                    completeModal.style.display = 'flex'; // モーダル表示
                }
            });
        });
    } else {
        // ヒアリング項目が0件（優秀な候補者）の場合は、すぐに完了できるようにするなどの要件が必要ですが
        // 今回は「録音停止ボタンを別で作るか」、ヒアリングがない場合は面接官の判断で戻る運用とします。
    }

    document.getElementById('completeBtn').addEventListener('click', () => {
        completeModal.style.display = 'none';
        if(mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop(); // ここでonstopイベントが発火し、サーバーへ飛ぶ
        }
    });
    <?php endif; ?>
</script>

</body>
</html>