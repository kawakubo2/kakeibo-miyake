<?php
require_once '../common/DbManager.php';
require_once '../common/Encode.php';
require_once '../common/himoku_assoc.php';

session_start();

if (isset($_GET['page']) && $_GET['page'] === 'top') {
	print('aaa');
	unset($_SESSION['hiduke']);
	unset($_SESSION['himoku']);
	unset($_SESSION['memo']);
	unset($_SESSION['nyukin']);
	unset($_SESSION['shukkin']);
	unset($_SESSION['id']);

	try {
		if (!isset($_SESSION['himoku_assoc']) || !isset($_SESSION['himoku_inout'])){
			$temp = getHimokuAssoc();
			$_SESSION['himoku_assoc'] = $temp[0];
			$_SESSION['himoku_inout'] = $temp[1];
		}

	} catch(PDOException $e) {
		die('エラーメッセージ:' . $e->getMessage());
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<link type="text/css" rel="stylesheet" href="../css/main.css" />
	<title>新規登録 | 家計簿アプリ</title>
</head>
<body>
	<h2>新規登録</h2>
	<a href="kakeibo_index.php">一覧</a>
	<p id="success_message">
	<?php
	if (isset($_SESSION['insert_success'])) {
		print($_SESSION['insert_success']);
		unset($_SESSION['insert_success']);
	}
	?>
	</p>
	<ul id="error_summary">
	<?php
	if (isset($_SESSION['insert_errors'])) {
		foreach ($_SESSION['insert_errors'] as $error) { ?>
			<li><?=$error ?></li>
	<?php
		}
		unset($_SESSION['insert_errors']);
	}
	?>
	</ul>
	<form method="POST" action="kakeibo_insert_process.php">
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
			<input type="number" id="shukkin" name="shukkin" min="0" value="<?=$_SESSION['shukkin'] ?>" />
		</div>
		<input type="submit" value="登録" />
	</form>
</body>
</html>
