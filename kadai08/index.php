<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>現場の声アンケート</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<div class="main-container">
    <h1>現場の声アンケート</h1>

    <div class="avatar-wrap">
        <div class="avatar">
            <img src="image/icochara_base.jpeg" alt="キャラクター" id="chara-img">
        </div>
    </div>

    <div class="step-box active" id="step-0">
        <p class="intro-message">
            皆様<br>
            日々の業務へのご尽力に心より感謝申し上げます。<br><br>
            より良い職場環境づくりのため、皆様の率初なご要望やご意見をお聞かせください。<br><br>
            <span style="font-size: 14px; color: #666;">※本アンケートは匿名です。いただいたご意見は環境改善の目的のみに使用いたします。</span>
        </p>
        <div class="btn-wrap">
            <button id="start-btn" class="start-btn">スタート</button>
        </div>
    </div>
    
    <div class="step-box" id="step-1">
        <div class="question-text">親しい知人や友人に、当社の「単独訪問スタッフ」のお仕事をどの程度おすすめしたいですか？</div>
        <div class="star-rating">
            <span class="star" data-score="1">★</span>
            <span class="star" data-score="2">★</span>
            <span class="star" data-score="3">★</span>
            <span class="star" data-score="4">★</span>
            <span class="star" data-score="5">★</span>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-0">スタート画面に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-2">
        <div class="question-text">お客様宅での「安全性」や「業務範囲の明確さ（ルール）」について、どの程度満足していますか？</div>
        <div class="btn-wrap">
            <button class="ans-btn" data-score="5">非常に満足している</button>
            <button class="ans-btn" data-score="4">やや満足している</button>
            <button class="ans-btn" data-score="3">どちらともいえない</button>
            <button class="ans-btn" data-score="2">やや不慢である</button>
            <button class="ans-btn" data-score="1">非常に不満である</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-1">1つ前の質問に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-3">
        <div class="question-text">トラブルが起きた際の本部（管理スタッフ）の「サポート体制」や、事前の「情報共有」について、どの程度満足していますか？</div>
        <div class="btn-wrap">
            <button class="ans-btn" data-score="5">非常に満足している</button>
            <button class="ans-btn" data-score="4">やや満足している</button>
            <button class="ans-btn" data-score="3">どちらともいえない</button>
            <button class="ans-btn" data-score="2">やや不慢である</button>
            <button class="ans-btn" data-score="1">非常に不満である</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-2">1つ前の質問に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-4">
        <div class="question-text">移動時間の扱いや給与・評価制度など、会社の「労働環境・待遇」について、どの程度満足していますか？</div>
        <div class="btn-wrap">
            <button class="ans-btn" data-score="5">非常に満足している</button>
            <button class="ans-btn" data-score="4">やや満足している</button>
            <button class="ans-btn" data-score="3">どちらともいえない</button>
            <button class="ans-btn" data-score="2">やや不満である</button>
            <button class="ans-btn" data-score="1">非常に不満である</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-3">1つ前の質問に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-5">
        <div class="question-text">現場で感じている不安や、会社・管理スタッフへの要望があれば教えてください。<br><span style="font-size: 14px; color: #666; font-weight: normal;">※当てはまるカテゴリを選んでください。</span></div>
        <div class="btn-wrap">
            <button class="ans-btn q5-main-btn" data-main="お客様のこと">お客様のこと</button>
            <button class="ans-btn q5-main-btn" data-main="現場・自分のこと">現場・自分のこと</button>
            <button class="ans-btn q5-main-btn" data-main="運営について">運営について</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-4">1つ前の質問に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-6">
        <div class="question-text">具体的な内容に最も近いものを選んでください。</div>
        <div class="btn-wrap" id="q5-level-2-customer" style="display: none;">
            <button class="ans-btn q5-sub-btn" data-sub="嬉しかった事">嬉しかった事</button>
            <button class="ans-btn q5-sub-btn" data-sub="お叱り・ご不満">お叱り・ご不満</button>
            <button class="ans-btn q5-sub-btn" data-sub="その他">その他</button>
        </div>
        <div class="btn-wrap" id="q5-level-2-field" style="display: none;">
            <button class="ans-btn q5-sub-btn" data-sub="ヒヤッとしたこと">ヒヤッとしたこと</button>
            <button class="ans-btn q5-sub-btn" data-sub="うまくいったこと">うまくいったこと</button>
            <button class="ans-btn q5-sub-btn" data-sub="やりづらさ・不安">やりづらさ・不安</button>
        </div>
        <div class="btn-wrap" id="q5-level-2-hq" style="display: none;">
            <button class="ans-btn q5-sub-btn" data-sub="助かったこと">助かったこと</button>
            <button class="ans-btn q5-sub-btn" data-sub="お叱り・ご不満">お叱り・ご不満</button>
            <button class="ans-btn q5-sub-btn" data-sub="その他">その他</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-5">1つ前の画面に戻る</button>
        </div>
    </div>

    <div class="step-box" id="step-7">
        <div class="question-text">詳細をご自由にご記入ください。<br><span style="font-size: 14px; color: #666; font-weight: normal;">※無回答でもそのまま送信できます。</span></div>
        <div class="btn-wrap">
            <textarea id="comment-area" class="text-input" placeholder="詳細をご記入ください。"></textarea>
            <button id="final-submit-btn" class="ans-btn">送信する</button>
        </div>
        <div class="back-btn-wrap">
            <button class="back-btn" data-target="step-6">1つ前の画面に戻る</button>
        </div>
    </div>

    <div class="progress-dots" id="progress-indicator" style="display: none;">
        <div class="dot active" id="dot-1"></div>
        <div class="dot" id="dot-2"></div>
        <div class="dot" id="dot-3"></div>
        <div class="dot" id="dot-4"></div>
        <div class="dot" id="dot-5"></div>
    </div>

    <form id="survey-form" action="insert.php" method="post" style="display: none;">
        <input type="hidden" name="q1_score" id="input-q1" value="">
        <input type="hidden" name="q2_score" id="input-q2" value="">
        <input type="hidden" name="q3_score" id="input-q3" value="">
        <input type="hidden" name="q4_score" id="input-q4" value="">
        <input type="hidden" name="q5_main" id="input-q5-main" value="">
        <input type="hidden" name="q5_sub" id="input-q5-sub" value="">
        <input type="hidden" name="comment" id="input-comment" value="">
    </form>
</div>

<script src="js/script.js"></script>

</body>
</html>