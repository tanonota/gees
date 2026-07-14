<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理システム - ログイン</title>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'M PLUS Rounded 1c', sans-serif; 
            background-color: #f0f4f8; /* ダッシュボードと同じ背景色 */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-card { 
            background: #fff; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 350px; 
            border-top: 5px solid #2c5263; /* ダッシュボードと同じアクセントカラー */
        }
        h1 { 
            font-size: 1.4rem; 
            color: #2c5263; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            font-size: 0.9rem; 
            color: #555; 
            margin-bottom: 8px; 
            font-weight: bold; 
        }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 1rem; 
            box-sizing: border-box; 
            transition: border-color 0.2s; 
        }
        .form-group input:focus { 
            border-color: #2c5263; 
            outline: none; 
        }
        .btn-submit { 
            width: 100%; 
            padding: 14px; 
            background: #2c5263; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            font-size: 1rem; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.2s; 
            margin-top: 10px; 
        }
        .btn-submit:hover { 
            background: #1a3541; 
        }
        .error-msg { 
            color: #e74c3c; 
            font-size: 0.85rem; 
            margin-bottom: 20px; 
            text-align: center; 
            background: #fdf0f0; 
            padding: 10px; 
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h1>システムログイン</h1>
    
    <!-- URLにエラーフラグ(?error=1)がある場合に警告を出す処理 -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg">⚠️ IDまたはパスワードが間違っています。</div>
    <?php endif; ?>

    <!-- 認証処理（login_act.php）へデータを送るフォーム -->
    <form action="login_act.php" method="POST">
        <div class="form-group">
            <label for="lid">ログインID</label>
            <!-- プレースホルダーでヒントを表示 -->
            <input type="text" id="lid" name="lid" required placeholder="admin または interviewer">
        </div>
        <div class="form-group">
            <label for="lpw">パスワード</label>
            <input type="password" id="lpw" name="lpw" required>
        </div>
        
        <!-- 💡 面接官がログイン後に元の候補者画面に戻れるよう、受付番号を裏側でリレーする -->
        <input type="hidden" name="receipt_number" value="<?= htmlspecialchars($_GET['receipt_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <button type="submit" class="btn-submit">ログイン</button>
    </form>
</div>

</body>
</html>