<?php
require_once '../common/DbManager.php';

session_start();

$_SESSION['id']      = $_POST['id'];
$_SESSION['hiduke']  = $_POST['hiduke'];
$_SESSION['himoku']  = $_POST['himoku'];
$_SESSION['memo']    = $_POST['memo'];
$_SESSION['nyukin']  = $_POST['nyukin'];
$_SESSION['shukkin'] = $_POST['shukkin'];

// 入力値検証(バリデーション)

$errors = [];

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

/*
 * $_SESSION = ['hiduke' => '2020-12-11', 'himoku' => 1, … 'himoku_inout' => [1 => '出金', 2 => '入金', 3 => '出金' … ]
 *
 * $himoku_inout = [ 1 => '出金', 2 => '入金', 3 => '出金' … ]
 * $himoku_input[1] ---> 出金
 * $himoku_input[2] ---> 入金
 */
if ($_SESSION['himoku'] !== '') {
	if ($_SESSION['himoku_inout'][$_SESSION['himoku']] === '入金') {
		if (!($_SESSION['nyukin'] > 0 && (int)$_SESSION['shukkin'] === 0)) {
			$errors[] = $_SESSION['himoku_assoc'][$_SESSION['himoku']] . 'の場合、
					入金額に0より大きい値、出金額に0を入力してください。';
		}
	}
	if ($_SESSION['himoku_inout'][$_SESSION['himoku']] === '出金') {
		if (!($_SESSION['shukkin'] > 0 && (int)$_SESSION['nyukin'] === 0)) {
			$errors[] = $_SESSION['himoku_assoc'][$_SESSION['himoku']] . 'の場合、
					出金額に0より大きい値、入金額に0を入力してください。';
		}
	}
}

if (count($errors) > 0) {
	$_SESSION['update_errors'] = $errors;
	header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/kakeibo_update_form.php');
	exit();
}

try {
	$db = getDb();
	$sql = "UPDATE 家計簿
			SET 日付 = :hiduke, 費目id = :himoku, メモ = :memo, 入金額 = :nyukin, 出金額 = :shukkin
			WHERE id = :id";
	$stt = $db->prepare($sql);
	$stt->bindValue(':hiduke',  $_SESSION['hiduke']);
	$stt->bindValue(':himoku',  $_SESSION['himoku']);
	$stt->bindValue(':memo',    $_SESSION['memo']);
	$stt->bindValue(':nyukin',  $_SESSION['nyukin']);
	$stt->bindValue(':shukkin', $_SESSION['shukkin']);
	$stt->bindValue(':id',      $_SESSION['id']);

	$stt->execute();

	$_SESSION['update_success'] = 'id=' . $_SESSION['id'] . 'を更新しました';

	unset($_SESSION['hiduke']);
	unset($_SESSION['himoku']);
	unset($_SESSION['memo']);
	unset($_SESSION['nyukin']);
	unset($_SESSION['shukkin']);
	unset($_SESSION['id']);

	header('Location: http://' . $_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) . '/' . $_SESSION['back_page']);
	unset($_SESSION['back_page']);
} catch (PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}