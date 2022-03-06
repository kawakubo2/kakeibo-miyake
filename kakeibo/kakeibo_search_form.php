<?php
require_once '../common/DbManager.php';
require_once '../common/Encode.php';

session_start();

try {
	$db = getDb();
	$sql = "SELECT K.id, K.日付, H.費目名, K.メモ, K.入金額, K.出金額
			FROM 家計簿 AS K
				INNER JOIN 費目 AS H ON K.費目id = H.id";
	$stt = $db->prepare($sql);
	$stt->execute();
} catch(PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<link type="text/css" rel="stylesheet" href="../css/main.css" />
	<title>日付検索 | 家計簿アプリ</title>
</head>
<body>
	<h3>日付検索</h3>
	<a href="../index.php">トップ</a><br>
	<a href="kakeibo_index.php">一覧</a>
	<ul id="error_summary">
	<?php
	if (isset($_SESSION['kakeibo_search_errors'])) {
	    foreach ($_SESSION['kakeibo_search_errors'] as $error) { ?>
	    	<li><?=$error ?></li>
	<?php
	    }
	    unset($_SESSION['kakeibo_search_errors']);
	}
	?>
	</ul>
	<form method="GET" action="kakeibo_search_process.php">
		<div class="container">
			<label for="hiduke">日付:</label><br>
			<input type="date" id="hiduke" name="hiduke" />
		</div>
		<input type="submit" value="検索" />
	</form>
	<?php
	if (isset($_SESSION['search_result'])) {
	?>
	<table>
		<thead>
			<tr>
				<th>id</th><th>日付</th><th>費目</th><th>メモ</th><th>入金額</th><th>出金額</th><th></th>
			</tr>
		</thead>
		<tbody>
		<?php while ($row = $stt->fetch(PDO::FETCH_ASSOC)) { // ['id' => 5, '日付' => '2020-12-01', '費目名' => '食費', 'メモ' => 'コーヒーを購入う' ?>
			<tr>
				<td class="number"><?=$row['id'] ?></td>
				<td><?=$row['日付'] ?></td>
				<td><?=$row['費目名'] ?></td>
				<td><?=$row['メモ'] ?></td>
				<td class="number"><?=$row['入金額'] ?></td>
				<td class="number"><?=$row['出金額'] ?></td>
				<td>
				<?php
				/*
				 * a要素(アンカータグ)のhref属性にはクエリ文字列付きの
				 * URLを付けることができる
				 * 例) ?id=5&page=top  ?キー1=値1&キー2=値2&…
				 *
				 * 受け取るkakeibo_update_form.phpでは$_GETで受け取る
				 * 内容は [ 'id' => 5, 'page' => top ]のようになる。
				 */
				?>
					<a href="kakeibo_update_form.php?id=<?=$row['id'] ?>&page=<?=pathinfo(__FILE__)['basename'] ?>">編集</a>
					<a href="kakeibo_delete_form.php?id=<?=$row['id'] ?>&page=<?=pathinfo(__FILE__)['basename'] ?>">削除</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
	}
	?>
</body>
</html>
