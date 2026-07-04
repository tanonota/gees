<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ応募フォーム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-wrapper">
        <form method="POST" action="insert.php">
            
            <div class="form-group">
                <label>お名前 <span class="required">必須</span></label>
                <div class="flex-inputs">
                    <span>姓</span><input type="text" name="name_last" placeholder="例)山田" required>
                    <span>名</span><input type="text" name="name_first" placeholder="例)太郎" required>
                </div>
            </div>

            <div class="form-group">
                <label>ふりがな <span class="required">必須</span></label>
                <div class="flex-inputs">
                    <span>姓</span><input type="text" name="kana_last" placeholder="例)やまだ" required>
                    <span>名</span><input type="text" name="kana_first" placeholder="例)たろう" required>
                </div>
            </div>

            <div class="form-group">
                <label>性別 <span class="required">必須</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="男" required> 男</label>
                    <label><input type="radio" name="gender" value="女" required> 女</label>
                </div>
            </div>

            <div class="form-group">
                <label>ご希望の職種(複数選択可) <span class="required">必須</span></label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="job_types[]" value="家事代行サービス"> 家事代行サービス(家政婦・お手伝いさん)</label>
                    <label><input type="checkbox" name="job_types[]" value="ベビー・キッズシッター"> ベビー・キッズシッター</label>
                    <label><input type="checkbox" name="job_types[]" value="イベント保育"> イベント保育</label>
                    <label><input type="checkbox" name="job_types[]" value="マザーリング"> マザーリング</label>
                    <label><input type="checkbox" name="job_types[]" value="シルバーシッター"> シルバーシッター</label>
                </div>
            </div>

            <div class="form-group">
                <label>お持ちの資格・経験</label>
                <textarea name="skills" rows="5"></textarea>
            </div>

            <p class="privacy-text">
                <a href="#">プライバシーポリシー</a>にご同意いただいた上で、<br>お問い合わせください。
            </p>

            <button type="submit" class="submit-btn">応募 / 確認画面へ</button>

        </form>
    </div>
</body>
</html>