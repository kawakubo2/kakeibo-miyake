<?php
require_once '../common/DbManager.php';

session_start();

$_SESSION['hiduke'] = $_POST['hiduke'];
$_SESSION['himoku'] = $_POST['himoku'];
$_SESSION['memo'] = $_POST['memo'];
$_SESSION['nyukin'] = $_POST['nyukin'];
$_SESSION['shukkin'] = $_POST['shukkin'];

// 入力値検証(バリデーション)
$errors = [];

// 単項目チェック
if ($_SESSION['hiduke'] === '') {
	$errors[] = '日付は必須入力です。';
}

if ($_SESSION['himoku'] === '') {
	$errors[] = '費目は必須選択です。';
}

if (mb_strlen($_SESSION['memo']) > 255) {
	$errors[] = 'メモは255文字以内で入力してください。';
}

$_SESSION['nyukin'] = ($_SESSION['nyukin'] === '') ? 0: $_SESSION['nyukin'];
$_SESSION['shukkin'] = ($_SESSION['shukkin'] === '') ? 0: $_SESSION['shukkin'];

// 相関チェック
if ($_SESSION['himoku'] !== '') {
	if ($_SESSION['himoku_inout'][$_SESSION['himoku']] === '入金') {
		if (!($_SESSION['nyukin'] > 0 && (int)$_SESSION['shukkin'] === 0)) {
			$errors[] = $_SESSION['himoku_assoc'][$_SESSION['himoku']] . 'の場合、
					入金額に0より大きい値、出金額に0を入力してください。';
		}
	}
	if ($_SESSION['himoku_inout'][$_SESSION['himoku']] === '出金') {
		if (!((int)$_SESSION['nyukin'] === 0 && $_SESSION['shukkin'] > 0)) {
			$errors[] = $_SESSION['himoku_assoc'][$_SESSION['himoku']] . 'の場合、
					出金額に0より大きい値、入金額に0を入力してください。';
		}
	}
}

if (count($errors) > 0) {
	$_SESSION['insert_errors'] = $errors;
	header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/kakeibo_insert_form.php');
	exit();
}

try {
	$db = getDb();

	/*
	 *  :hidukeのように:で始まるものをプレイスホルダー
	 *  後から値を埋めるという意味合いがある。
	 *  SQLインジェクション攻撃を防ぐためには必須。
	 */
	$sql = "INSERT INTO 家計簿(日付, 費目id, メモ, 入金額, 出金額)
			VALUES(:hiduke, :himoku, :memo, :nyukin, :shukkin)";

	$stt = $db->prepare($sql);
	$stt->bindValue(':hiduke',    $_SESSION['hiduke']);
	$stt->bindValue(':himoku',    $_SESSION['himoku']);
	$stt->bindValue(':memo',      $_SESSION['memo']);
	$stt->bindValue(':nyukin',    $_SESSION['nyukin']);
	$stt->bindValue(':shukkin',   $_SESSION['shukkin']);

	$stt->execute();

	unset($_SESSION['hiduke']);
	unset($_SESSION['himoku']);
	unset($_SESSION['memo']);
	unset($_SESSION['nyukin']);
	unset($_SESSION['shukkin']);

	$_SESSION['insert_success'] = '登録しました';

	header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/kakeibo_insert_form.php');

} catch (PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}