<?php
// エラーを表示させる設定（トラブルシューティング用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. POSTデータ取得（全項目を受け取れるようにします）
// ※フォームに入力がない場合は空文字('')を入れる安全な書き方です
$name_last  = isset($_POST['name_last']) ? $_POST['name_last'] : '';
$name_first = isset($_POST['name_first']) ? $_POST['name_first'] : '';
$kana_last  = isset($_POST['kana_last']) ? $_POST['kana_last'] : '';
$kana_first = isset($_POST['kana_first']) ? $_POST['kana_first'] : '';
$gender     = isset($_POST['gender']) ? $_POST['gender'] : '';

$zipcode    = isset($_POST['zipcode']) ? $_POST['zipcode'] : '';
$pref       = isset($_POST['pref']) ? $_POST['pref'] : '';
$city       = isset($_POST['city']) ? $_POST['city'] : '';
$address    = isset($_POST['address']) ? $_POST['address'] : '';
$email      = isset($_POST['email']) ? $_POST['email'] : '';
$phone      = isset($_POST['phone']) ? $_POST['phone'] : '';
$birth_date = isset($_POST['birth_date']) ? $_POST['birth_date'] : '';
$location   = isset($_POST['location']) ? $_POST['location'] : '';

$skills     = isset($_POST['skills']) ? $_POST['skills'] : '';
$others     = isset($_POST['others']) ? $_POST['others'] : '';

// 複数選択のチェックボックス（ご希望の職種）の処理
$job_types = '';
if (isset($_POST['job_types']) && is_array($_POST['job_types'])) {
    $job_types = implode(',', $_POST['job_types']);
}

// 2. DB接続
require_once('funcs.php');
$pdo = db_conn();

// 3. データ登録SQL作成（省略せずにすべてのカラムを指定します）
$sql = "INSERT INTO staff_entry_table(
            name_last, name_first, kana_last, kana_first, gender, 
            zipcode, pref, city, address, email, phone, birth_date, 
            location, job_types, skills, others, indate
        ) VALUES (
            :name_last, :name_first, :kana_last, :kana_first, :gender, 
            :zipcode, :pref, :city, :address, :email, :phone, :birth_date, 
            :location, :job_types, :skills, :others, sysdate()
        )";
$stmt = $pdo->prepare($sql);

// 4. バインド変数に値をセット（すべての変数に値を紐付けます）
$stmt->bindValue(':name_last',  $name_last,  PDO::PARAM_STR);
$stmt->bindValue(':name_first', $name_first, PDO::PARAM_STR);
$stmt->bindValue(':kana_last',  $kana_last,  PDO::PARAM_STR);
$stmt->bindValue(':kana_first', $kana_first, PDO::PARAM_STR);
$stmt->bindValue(':gender',     $gender,     PDO::PARAM_STR);

$stmt->bindValue(':zipcode',    $zipcode,    PDO::PARAM_STR);
$stmt->bindValue(':pref',       $pref,       PDO::PARAM_STR);
$stmt->bindValue(':city',       $city,       PDO::PARAM_STR);
$stmt->bindValue(':address',    $address,    PDO::PARAM_STR);
$stmt->bindValue(':email',      $email,      PDO::PARAM_STR);
$stmt->bindValue(':phone',      $phone,      PDO::PARAM_STR);
$stmt->bindValue(':birth_date', $birth_date, PDO::PARAM_STR);
$stmt->bindValue(':location',   $location,   PDO::PARAM_STR);

$stmt->bindValue(':job_types',  $job_types,  PDO::PARAM_STR);
$stmt->bindValue(':skills',     $skills,     PDO::PARAM_STR);
$stmt->bindValue(':others',     $others,     PDO::PARAM_STR);

// 5. SQL実行
$status = $stmt->execute();

// 6. データ登録処理後
if ($status == false) {
    // SQL実行時にエラーがある場合
    $error = $stmt->errorInfo();
    exit("ErrorMessage:".$error[2]);
} else {
    // 登録成功時は入力画面にリダイレクト
    header('Location: index.php');
    exit();
}
?>