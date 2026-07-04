<?php
// エラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. POSTデータ取得
// detail.php のフォームから送られてきたデータを受け取ります
$name_last  = isset($_POST['name_last']) ? $_POST['name_last'] : '';
$name_first = isset($_POST['name_first']) ? $_POST['name_first'] : '';
$gender     = isset($_POST['gender']) ? $_POST['gender'] : '';
$skills     = isset($_POST['skills']) ? $_POST['skills'] : '';

// 【重要】更新対象を特定するための id も受け取ります
$id         = isset($_POST['id']) ? $_POST['id'] : ''; 

// 複数選択のチェックボックス（ご希望の職種）の処理
$job_types = '';
if (isset($_POST['job_types']) && is_array($_POST['job_types'])) {
    $job_types = implode(',', $_POST['job_types']);
}

// 2. DB接続
require_once('funcs.php');
$pdo = db_conn();

// 3. データ更新SQL作成
// UPDATE文を使って、指定したidのデータだけを上書きします
$sql = "UPDATE staff_entry_table 
        SET name_last = :name_last, 
            name_first = :name_first, 
            gender = :gender, 
            job_types = :job_types, 
            skills = :skills 
        WHERE id = :id";
$stmt = $pdo->prepare($sql);

// 4. バインド変数に値をセット
$stmt->bindValue(':name_last',  $name_last,  PDO::PARAM_STR);
$stmt->bindValue(':name_first', $name_first, PDO::PARAM_STR);
$stmt->bindValue(':gender',     $gender,     PDO::PARAM_STR);
$stmt->bindValue(':job_types',  $job_types,  PDO::PARAM_STR);
$stmt->bindValue(':skills',     $skills,     PDO::PARAM_STR);
$stmt->bindValue(':id',         $id,         PDO::PARAM_INT); // idは数値なのでINT

// 5. SQL実行
$status = $stmt->execute();

// 6. データ更新処理後
if ($status == false) {
    // SQL実行時にエラーがある場合
    $error = $stmt->errorInfo();
    exit("ErrorMessage:".$error[2]);
} else {
    // 更新成功時は一覧画面（select.php）にリダイレクトして結果を確認できるようにします
    header('Location: select.php');
    exit();
}
?>