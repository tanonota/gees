<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スキルチェックシート - 完了</title>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'M PLUS Rounded 1c', sans-serif; 
            background-color: #f0f4f8; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            color: #333;
        }
        .container { 
            background: #fff; 
            padding: 50px 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            text-align: center; 
            max-width: 500px; 
            width: 90%; 
            border-top: 6px solid #2c5263; /* 💡 メインカラーのアクセント */
        }
        h1 { 
            color: #2c5263; 
            font-size: 1.8rem; 
            margin-top: 0;
            margin-bottom: 20px; 
        }
        p { 
            font-size: 1.15rem; 
            color: #555; 
            line-height: 1.8; 
            margin-bottom: 40px; 
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .btn-login { 
            display: inline-block; 
            background-color: #2c5263; /* 💡 統一したボタンカラー */
            color: #fff; 
            text-decoration: none; 
            padding: 15px 40px; 
            border-radius: 8px; 
            font-weight: bold; 
            font-size: 1.1rem; 
            transition: 0.2s; 
            box-shadow: 0 4px 6px rgba(44, 82, 99, 0.2);
        }
        .btn-login:hover { 
            background-color: #1a3644; 
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(44, 82, 99, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 💡 タイトルを変更 -->
        <h1>スキルチェックシート</h1>
        
        <!-- 💡 候補者への感謝と指示のメッセージ -->
        <p>
            ご回答お疲れ様です。<br>
            面接担当者にお声がけください。
        </p>

        <!-- 💡 面接官が押すログインボタン -->
        <a href="login.php" class="btn-login">ログイン</a>
    </div>
</body>
</html>