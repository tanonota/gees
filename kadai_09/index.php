<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>スタッフ事前登録フォーム</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>
<body>
    <div class="container">
        <h1>スタッフ登録フォーム</h1>
        <form action="insert.php" method="POST" enctype="multipart/form-data" id="entryForm">
            <input type="hidden" name="line_uid" id="line_uid" value="">

            <div class="section">
                <h2>【セクション1：基本情報】</h2>
                
                <div class="form-group">
                    <label>顔写真アップロード <span class="required">必須</span></label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg, image/png, image/heic" required>
                    <img id="preview" src="" alt="プレビュー" style="display:none; max-width: 150px; margin-top: 10px;">
                </div>

                <div class="form-group">
                    <label>氏名 <span class="required">必須</span></label>
                    <div class="flex-inputs">
                        <input type="text" name="name_last" placeholder="姓" required>
                        <input type="text" name="name_first" placeholder="名" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>フリガナ <span class="required">必須</span></label>
                    <div class="flex-inputs">
                        <input type="text" name="kana_last" placeholder="セイ" required>
                        <input type="text" name="kana_first" placeholder="メイ" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>生年月日 <span class="required">必須</span></label>
                    <input type="date" name="birth_date" required>
                </div>

                <div class="form-group">
                    <label>電話番号（ハイフンなし） <span class="required">必須</span></label>
                    <input type="tel" name="phone" pattern="^[0-9]+$" required>
                </div>

                <div class="form-group">
                    <label>メールアドレス <span class="required">必須</span></label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>現住所 <span class="required">必須</span></label>
                    <input type="text" name="zipcode" placeholder="郵便番号" required>
                    <input type="text" name="address" placeholder="都道府県・市区町村・番地・マンション名" required style="margin-top: 5px;">
                </div>
            </div>

            <div class="section">
                <h2>【セクション2：希望する働き方・通勤手段】</h2>
                
                <div class="form-group">
                    <label>対応可能サービス（複数可） <span class="required">必須</span></label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="services[]" value="キッズ・ベビーシッター"> キッズ・ベビーシッター</label>
                        <label><input type="checkbox" name="services[]" value="マザーリング"> マザーリング</label>
                        <label><input type="checkbox" name="services[]" value="シルバーシッター"> シルバーシッター</label>
                        <label><input type="checkbox" name="services[]" value="ハウスキーパー"> ハウスキーパー</label>
                        <label><input type="checkbox" name="services[]" value="ペットシッター"> ペットシッター</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Wワーク（副業）の有無 <span class="required">必須</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="w_work" value="なし" required> なし</label>
                        <label><input type="radio" name="w_work" value="あり"> あり</label>
                    </div>
                </div>
                <div class="form-group hidden-field" id="w_work_detail_wrapper">
                    <label>Wワークの詳細 <span class="required">必須</span></label>
                    <textarea name="w_work_detail" rows="3" placeholder="現在の就業状況をご記入ください"></textarea>
                </div>

                <div class="form-group">
                    <label>利用可能な交通手段（複数可） <span class="required">必須</span></label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="transport[]" value="公共交通機関"> 公共交通機関</label>
                        <label><input type="checkbox" name="transport[]" value="バイク"> バイク</label>
                        <label><input type="checkbox" name="transport[]" value="自転車"> 自転車</label>
                        <label><input type="checkbox" name="transport[]" value="自動車(軽普通)"> 自動車(軽普通)</label>
                        <label><input type="checkbox" name="transport[]" value="自動車(HV車)"> 自動車(HV車)</label>
                    </div>
                </div>
                <div class="form-group hidden-field" id="car_name_wrapper">
                    <label>車名 <span class="required">必須</span></label>
                    <input type="text" name="car_name" placeholder="例：プリウス">
                </div>
            </div>

            <div class="section">
                <h2>【セクション4：健康状態・NG条件】</h2>
                
                <div class="form-group">
                    <label>喫煙の有無 <span class="required">必須</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="smoking" value="吸わない" required> 吸わない</label>
                        <label><input type="radio" name="smoking" value="吸う"> 吸う</label>
                    </div>
                </div>
                <div class="form-group hidden-field" id="smoking_detail_wrapper">
                    <label>1日の喫煙本数 <span class="required">必須</span></label>
                    <input type="number" name="smoking_amount" placeholder="本数">
                </div>
            </div>

            <div class="form-group submit-group">
                <button type="submit" id="submitBtn">登録内容を確認・送信する</button>
            </div>
        </form>
    </div>

    <script>
    $(document).ready(function() {
        // 画像プレビューとサイズチェック（5MB制限）
        $('#photo').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('ファイルサイズは5MB以下にしてください。');
                $(this).val('');
                $('#preview').attr('src', '').hide();
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        });

        // 条件分岐：Wワーク
        $('input[name="w_work"]').on('change', function() {
            if ($(this).val() === 'あり') {
                $('#w_work_detail_wrapper').slideDown().find('textarea').prop('required', true);
            } else {
                $('#w_work_detail_wrapper').slideUp().find('textarea').prop('required', false).val('');
            }
        });

        // 条件分岐：自動車（「自動車」という文字列が含まれているかチェック）
        $('input[name="transport[]"]').on('change', function() {
            let hasCar = false;
            $('input[name="transport[]"]:checked').each(function() {
                if ($(this).val().includes('自動車')) {
                    hasCar = true;
                }
            });
            if (hasCar) {
                $('#car_name_wrapper').slideDown().find('input').prop('required', true);
            } else {
                $('#car_name_wrapper').slideUp().find('input').prop('required', false).val('');
            }
        });

        // 条件分岐：喫煙
        $('input[name="smoking"]').on('change', function() {
            if ($(this).val() === '吸う') {
                $('#smoking_detail_wrapper').slideDown().find('input').prop('required', true);
            } else {
                $('#smoking_detail_wrapper').slideUp().find('input').prop('required', false).val('');
            }
        });

        // 送信時の処理（未入力スクロールとスピナー無効化）
        $('#entryForm').on('submit', function(e) {
            const invalidElements = $(this).find(':invalid');
            if (invalidElements.length > 0) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: invalidElements.first().offset().top - 20
                }, 500);
                return;
            }
            
            // 二重送信防止
            $('#submitBtn').prop('disabled', true).text('送信中...');
        });
    });
    </script>
</body>
</html>