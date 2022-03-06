<?php
require_once '../common/DbManager.php';
require_once '../common/Encode.php';
require_once '../common/himoku_assoc.php';

session_start();

if (isset($_GET['page']) && in_array($_GET['page'], ['kakeibo_index.php', 'kakeibo_search_form.php' ])) {
	unset($_SESSION['hiduke']);
	unset($_SESSION['himoku']);
	unset($_SESSION['memo']);
	unset($_SESSION['nyukin']);
	unset($_SESSION['shukkin']);
	unset($_SESSION['id']);

	$_SESSION['back_page'] = $_GET['page'];

	try {
		if (!isset($_SESSION['himoku_assoc']) || !isset($_SESSION['himoku_inout'])) {
			$temp = getHimokuAssoc();
			$_SESSION['himoku_assoc'] = $temp[0];
			$_SESSION['himoku_inout'] = $temp[1];
		}

		$db = getDb();
		$sql = "SELECT id, 日付, 費目id, メモ, 入金額, 出金額
				FROM 家計簿
				WHERE id = :id";
		$stt = $db->prepare($sql);
		$stt->bindValue(':id', $_GET['id']);
		$stt->execute();

		if ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
			$_SESSION['id']      = $row['id'];
			$_SESSION['hiduke']  = $row['日付'];
			$_SESSION['himoku']  = $row['費目id'];
			$_SESSION['memo']    = $row['メモ'];
			$_SESSION['nyukin']  = $row['入金額'];
			$_SESSION['shukkin'] = $row['出金額'];
		} else {
			die("キー:{$_GET['id']}が見つかりません。");
		}
	} catch (PDOException $e) {
		die('エラーメッセージ:' . $e->getMessage());
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<link type="text/css" rel="stylesheet" href="../css/main.css" />
	<title>編集 | 家計簿アプリ</title>
</head>
<body>
	<h2>編集</h2>
	<a href="kakeibo_index.php">一覧</a>

	<ul id="error_summary">
	<?php
	if (isset($_SESSION['update_errors'])) {
		foreach ($_SESSION['update_errors'] as $error) { ?>
			<li><?=$error ?></li>
	<?php
		}
		unset($_SESSION['update_errors']);
	}
	?>
	</ul>
	<form method="POST" action="kakeibo_update_process.php">
		<div class="container">
			<label for="hiduke">日付:</label><br>
			<input type="date" id="hiduke" name="hiduke" value="<?=e($_SESSION['hiduke']) ?>" />
		</div>
		<div class="container">
			<label for="himoku">費目:</label><br>
			<select id="himoku" name="himoku">
				<option value="">-- 選択 --</option>
			<?php
			foreach ($_SESSION['himoku_assoc'] as $id => $name) {
				$prop = ($id === (int)e($_SESSION['himoku'])) ? 'selected': '';
				?>
				<option value="<?=$id?>" <?=$prop ?> ><?=$name ?></option>
			<?php
			}
			?>
			</select>
		</div>
		<div class="container">
			<label for="memo">メモ:</label><br>
			<textarea id="memo" name="memo" rows="5" cols="40"><?=e($_SESSION['memo']) ?></textarea>
		</div>
		<div class="container">
			<label for="nyukin">入金額:</label><br>
			<input type="number" id="nyukin" name="nyukin" min="0" value="<?=e($_SESSION['nyukin']) ?>" />
		</div>
		<div class="container">
			<label for="shukkin">出金額:</label><br>
			<input type="number" id="shukkin" name="shukkin" min="0" value="<?=e($_SESSION['shukkin']) ?>" />
		</div>
		<input type="hidden" name="id" value="<?=$_SESSION['id'] ?>" />
		<input type="submit" value="更新" />
	</form>
</body>
</html>