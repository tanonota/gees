$(document).ready(function() {
    let totalScore = 0;

    $('#start-test').click(function() {
        totalScore = 0;
        
        let firstStepBox = $('.step-box[data-step="1"]');
        
        $('#dynamic-bg').attr('src', firstStepBox.attr('data-bg'));
        
        $('.step-box').removeClass('active').hide();
        firstStepBox.addClass('active').show();
        
        $('#modal-overlay, #modal-content').fadeIn(300);
    });

    $('#close-modal, #modal-overlay').click(function() {
        $('#modal-overlay, #modal-content').fadeOut(300);
    });

    $('#modal-content').click(function(e) {
        e.stopPropagation();
    });

    $('.ans-btn').click(function() {
        let score = parseInt($(this).attr('data-score'));
        totalScore += score;

        let currentStepBox = $(this).closest('.step-box');
        let currentStepNum = parseInt(currentStepBox.attr('data-step'));
        let nextStepNum = currentStepNum + 1;
        
        let nextStepBox = $('.step-box[data-step="' + nextStepNum + '"]');

        currentStepBox.fadeOut(200, function() {
            $(this).removeClass('active');

            if (nextStepBox.length > 0) {
                $('#dynamic-bg').attr('src', nextStepBox.attr('data-bg'));
                nextStepBox.fadeIn(200).addClass('active');
            } else {
                showResult(totalScore);
            }
        });
    });

    function showResult(score) {
        let title = "";
        let desc = "";
        let titleColor = "#3B8598"; 

        if (score >= 3) {
            title = "生存本能アラート！<br>サバイバル期ママ";
            titleColor = "#e74c3c";
            desc = "あなたの今の辛さは愛情不足ではなく、ホルモンと睡眠負債による『エラー表示』です。今すぐやめるべき『家事・育児の引き算リスト』をLINEでお渡しします。";
        } else if (score >= 1) {
            title = "情報迷子の<br>真面目すぎママ";
            titleColor = "#f39c12"; 
            desc = "「ちゃんと育てなきゃ」という責任感が、あなた自身を苦しめています。ネットの噂に振り回されない『産後メンタルと赤ちゃんの科学的・本当の知識』をLINEでこっそり教えます。";
        } else {
            title = "実はポテンシャル高！<br>肝っ玉母さん予備軍";
            desc = "あなたは自分なりの育児ペースを掴むセンスがあります！さらに子育ての質を上げる『保育のプロが実践する、声かけ・遊びのヒント集』をLINEでお届けします。";
        }

        $('#result-title').html(title).css('color', titleColor);
        $('#result-desc').html(desc);
        
        let resultScreenBox = $('#result-screen');
        $('#dynamic-bg').attr('src', resultScreenBox.attr('data-bg'));
        
        resultScreenBox.fadeIn(200).addClass('active');
    }
});