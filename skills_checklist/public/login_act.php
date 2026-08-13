
<?php
// エラーを画面に表示させるための設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// セッションの開始（ログイン状態を保持するため）
session_start();
require_once('../src/funcs.php');
$pdo = db_conn();

// 1. login.php から送られてきたデータを受け取る
$lid = $_POST['lid'] ?? '';
$lpw = $_POST['lpw'] ?? '';
$receipt_number = $_POST['receipt_number'] ?? '';

// 2. 入力されたID（lid）を条件に、DBからユーザーを探す
$stmt = $pdo->prepare("SELECT * FROM users WHERE lid = :lid AND life_flg = 0");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
$stmt->execute();
$val = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. パスワードの照合（password_verify関数）
// $valにデータがあり、かつ暗号化されたパスワードと一致するかチェック
if ($val && password_verify($lpw, $val['lpw'])) {
    
    // 💡 ログイン成功時の処理
    // セッションIDを新しく発行し、乗っ取り（セッションハイジャック）を防ぐ
    session_regenerate_id(true);
    
    // サーバーの金庫（SESSION）にユーザー情報を預ける
    $_SESSION['chk_ssid']  = session_id();
    $_SESSION['kanri_flg'] = $val['kanri_flg']; // 面接官(0)か管理者(1)か
    $_SESSION['name']      = $val['name'];

    // 💡 権限による画面の振り分け
    if ($_SESSION['kanri_flg'] === 1) {
        // 管理者の場合：後で作る「全体ダッシュボード」へ飛ばす
        // ※まだファイルがないので、一旦仮のURLを指定しています
        redirect('admin_dashboard.php'); 
    } else {
        // 面接官の場合：担当している候補者の「個別ダッシュボード」へ飛ばす
        redirect("dashboard.php?receipt_number={$receipt_number}");
    }

} else {
    // ログイン失敗時：エラーパラメータをつけてログイン画面に戻す
    redirect("login.php?error=1&receipt_number={$receipt_number}");
}
