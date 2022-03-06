<?php
require_once '../common/DbManager.php';

session_start();

$_SESSION['year']        = $_GET['year'];
$_SESSION['start_month'] = $_GET['start_month'];
$_SESSION['end_month']   = $_GET['end_month'];

$errors = [];

if ($_SESSION['year'] === '') {
	$errors[] = '年は必須選択です。'; // array_push($errors, '年は必須選択です。');
}

if ($_SESSION['start_month'] > $_SESSION['end_month']) {
	$errors[] = '開始月が終了月を超えています。';
}

if (count($errors) > 0) {
	$_SESSION['shukei_errors'] = $errors;
	header('Location: http://' . $_SERVER['HTTP_HOST']
			. dirname($_SERVER['PHP_SELF']) . '/shukei_form.php');
	exit();
}

try {
	/*
	 * [
	 *   [ '食費'   => [1 => 0, 2 => 0, 3 => 0, ... 12 => 0],
	 *                        ・
	 *                        ・
	 *                        ・
	 *   [ '居住費' => [1 => 0, 2 => 0, 3 => 0, ... 12 => 0],
	 * ]
	 */

	$result = [];

	foreach ($_SESSION['himoku_assoc'] as $himoku_id => $_) {
		$year_summary = [];
		for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
			$year_summary[$i] = 0;
		}
		$result[$himoku_id] = $year_summary;
	}

	$db = getDb();
	$sql = "SELECT K.費目id, H.費目名, MONTH(K.日付) AS 月, SUM(K.入金額 + K.出金額) AS 合計額
			FROM 家計簿 AS K
			    INNER JOIN 費目 AS H ON K.費目id = H.id
			WHERE YEAR(日付) = :year
			GROUP BY K.費目id, H.費目名, MONTH(日付)
			ORDER BY K.費目id, H.費目名, MONTH(日付)";
	$stt = $db->prepare($sql);
	$stt->bindValue(':year', $_GET['year']);
	$stt->execute();

	while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
		$result[$row['費目id']][$row['月']] = $row['合計額'];
	}

	$_SESSION['shukei_result'] = $result;

	header('Location: http://' . $_SERVER['HTTP_HOST']
			. dirname($_SERVER['PHP_SELF']) . '/shukei_form.php');

} catch (PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}