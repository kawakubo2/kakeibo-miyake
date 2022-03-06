<?php
require_once '../common/DbManager.php';

session_start();

if (isset($_POST['cancel'])) {
	;
} else if (isset($_POST['ok'])) {
	try {
		$db = getDb();
		$sql = "DELETE FROM 家計簿
				WHERE id = :id";
		$stt = $db->prepare($sql);
		$stt->bindValue(':id', $_POST['id']);
		$stt->execute();
	} catch (PDOException $e) {
		die('エラーメッセージ:' . $e->getMessage());
	}
	$_SESSION['delete_success'] = "id={$_POST['id']}を削除しました。";
} else {
	die('ネットワーク送信に不備');
}
header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/kakeibo_index.php');