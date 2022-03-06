<?php
require_once '../common/DbManager.php';
require_once '../common/Encode.php';


try {
	$db = getDb();
	$sql = "SELECT K.id, K.日付, H.費目名, K.メモ, K.入金額, K.出金額
			FROM 家計簿 AS K
				INNER JOIN 費目 AS H ON K.費目id = H.id
			WHERE K.id = :id";
	$stt = $db->prepare($sql);
	$stt->bindValue(':id', $_GET['id']);
	$stt->execute();

	if ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
		;
	} else {
		die("キー{$_GET['id']}が見つかりません。");
	}

} catch (PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<link type="text/css" rel="stylesheet" href="../css/main.css" />
<title>削除 | 家計簿</title>
</head>
<body>
	<h3>削除</h3>
	<a href="kakeibo_index.php">一覧</a>
	<table>
		<tr><th>id</th><td><?=e($row['id']) ?></td></tr>
		<tr><th>日付</th><td><?=e($row['日付']) ?></td></tr>
		<tr><th>費目名</th><td><?=e($row['費目名']) ?></td></tr>
		<tr><th>メモ</th><td><?=e($row['メモ']) ?></td></tr>
		<tr><th>入金額</th><td class="number"><?=e($row['入金額']) ?></td></tr>
		<tr><th>出金額</th><td class="number"><?=e($row['出金額']) ?></td></tr>
	</table>
	<form method="POST" action="kakeibo_delete_process.php">
		<input type="hidden" name="id" value="<?=e($_GET['id']) ?>" />
		<input type="submit" name="ok" value="OK" />
		<input type="submit" name="cancel" value="キャンセル" />
	</form>
</body>
</html>