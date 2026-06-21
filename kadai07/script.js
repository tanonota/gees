$(document).ready(function() {

    // ==============================
    // [START ➔ STEP 1] スタートボタン
    // ==============================
    $('#start-btn').on('click', function() {
        $('#step-0').fadeOut(300, function() {
            $('#step-1').fadeIn(300);
            $('#progress-indicator').fadeIn(300);
        });
    });

    // ==============================
    // [STEP 1 (星評価) ➔ 2]
    // ==============================
    $('.star').on('click', function() {
        const score = $(this).data('score');
        $('#input-q1').val(score); 
        
        $('.star').removeClass('active');
        $('.star').each(function() {
            if ($(this).data('score') <= score) {
                $(this).addClass('active');
            }
        });

        setTimeout(function() {
            $('#step-1').fadeOut(300, function() {
                $('#step-2').fadeIn(300);
                $('.dot').removeClass('active');
                $('#dot-2').addClass('active');
            });
        }, 400);
    });

    // ==============================
    // [STEP 2 ➔ 3]
    // ==============================
    $('#step-2 .ans-btn').on('click', function() {
        const score = $(this).data('score');
        $('#input-q2').val(score);
        
        $('#step-2').fadeOut(300, function() {
            $('#step-3').fadeIn(300);
            $('.dot').removeClass('active');
            $('#dot-3').addClass('active');
        });
    });

    // ==============================
    // [STEP 3 ➔ 4]
    // ==============================
    $('#step-3 .ans-btn').on('click', function() {
        const score = $(this).data('score');
        $('#input-q3').val(score);
        
        $('#step-3').fadeOut(300, function() {
            $('#step-4').fadeIn(300);
            $('.dot').removeClass('active');
            $('#dot-4').addClass('active');
        });
    });

    // ==============================
    // [STEP 4 ➔ 5]
    // ==============================
    $('#step-4 .ans-btn').on('click', function() {
        const score = $(this).data('score');
        $('#input-q4').val(score);
        
        $('#step-4').fadeOut(300, function() {
            $('#step-5').fadeIn(300);
            $('.dot').removeClass('active');
            $('#dot-5').addClass('active');
            // ※ここにあった画像切り替え処理を削除しました
        });
    });

    // ==============================
    // [STEP 5 ➔ 6] メインカテゴリ選択
    // ==============================
    $('.q5-main-btn').on('click', function() {
        $('.q5-main-btn').removeClass('selected');
        $(this).addClass('selected');
        
        const mainCat = $(this).data('main');
        $('#input-q5-main').val(mainCat);

        $('#q5-level-2-customer, #q5-level-2-field, #q5-level-2-hq').hide();
        $('.q5-sub-btn').removeClass('selected');
        
        if (mainCat === 'お客様のこと') { 
            $('#q5-level-2-customer').show(); 
        } else if (mainCat === '現場・自分のこと') { 
            $('#q5-level-2-field').show(); 
        } else if (mainCat === '運営について') { 
            $('#q5-level-2-hq').show(); 
        }

        $('#step-5').fadeOut(300, function() {
            $('#step-6').fadeIn(300);
        });
    });

    // ==============================
    // [STEP 6 ➔ 7] サブカテゴリ選択
    // ==============================
    $('.q5-sub-btn').on('click', function() {
        $('.q5-sub-btn').removeClass('selected');
        $(this).addClass('selected');
        
        const subCat = $(this).data('sub');
        $('#input-q5-sub').val(subCat);

        let hintText = "詳細をご記入ください。";
        switch (subCat) {
            case "嬉しかった事": hintText = "お客様からいただいた嬉しいお言葉や出来事を教えてください！"; break;
            case "助かったこと": hintText = "運営や本部の対応で助かったことを教えてください！"; break;
            case "お叱り・ご不満": hintText = "ご不満の状況や、改善すべき点を詳しく教えてください。"; break;
            case "ヒヤッとしたこと": hintText = "どのような状況でヒヤリとしましたか？再発防止のためにお聞かせください。"; break;
            case "うまくいったこと": hintText = "現場で工夫したことや、うまくいったエピソードを教えてください！"; break;
            case "やりづらさ・不安": hintText = "現場で感じている不安や、やりづらい部分を教えてください。"; break;
            case "その他": hintText = "その他、ご自由にご記入ください。"; break;
        }
        $('#comment-area').attr('placeholder', hintText);

        $('#step-6').fadeOut(300, function() {
            $('#step-7').fadeIn(300);
            // ※ここにあった画像切り替え処理も削除しました
        });
    });

    // ==============================
    // [STEP 7 ➔ 送信]
    // ==============================
    $('#final-submit-btn').on('click', function() {
        const comment = $('#comment-area').val();
        $('#input-comment').val(comment);
        $('#survey-form').submit();
    });

    // ==============================
    // [戻るボタンの処理]
    // ==============================
    function goBack(targetId, $currentStep) {
        $currentStep.fadeOut(300, function() {
            $('#' + targetId).fadeIn(300);

            if (targetId === 'step-0') {
                $('#progress-indicator').fadeOut(300);
            } else {
                let stepNum = targetId.split('-')[1];
                if (parseInt(stepNum) >= 5) { stepNum = '5'; }
                
                $('.dot').removeClass('active');
                $('#dot-' + stepNum).addClass('active');

                if (targetId === 'step-4') {
                    $('#comment-area').val('');
                }
            }
        });
    }

    $('.back-btn').on('click', function() {
        const targetId = $(this).data('target'); 
        const $currentStep = $(this).closest('.step-box');
        goBack(targetId, $currentStep);
    });

});