<?php
require_once '../common/DbManager.php';
require_once '../common/himoku_assoc.php';

session_start();

try {
	$db = getDb();
	$sql = "SELECT DISTINCT YEAR(日付) AS 年
			FROM 家計簿
			ORDER BY 年 DESC";
	$stt = $db->prepare($sql);
	$stt->execute();
} catch (PDOException $e) {
	die('エラーメッセージ:' . $e->getMessage());
}
if (!isset($_SESSION['himoku_assoc']) || !isset($_SESSION['himoku_inout'])) {
	$temp = getHimokuAssoc();
	$_SESSION['himoku_assoc'] = $temp[0];
	$_SESSION['himoku_inout'] = $temp[1];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<link type="text/css" rel="stylesheet" href="../css/main.css" />
<title>集計表 | 家計簿</title>
</head>
<body>
<h2>集計表</h2>
<a href="../index.php">トップ</a>
<ul id="error_summary">
<?php
if (isset($_SESSION['shukei_errors'])) {
	foreach ($_SESSION['shukei_errors'] as $error) {
		print("<li>{$error}</li>");
	}
	unset($_SESSION['shukei_errors']);
}
?>
</ul>
<form method="GET" action="shukei_process.php">
	<div class="container">
		<label>年:</label>
		<select name="year">
			<option value="">--選択--</option>
<?php
while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
	if ($row['年'] == $_SESSION['year']) {
		$prop = 'selected';
	} else {
		$prop = '';
	}
?>
			<option value="<?=$row['年'] ?>" <?=$prop ?> ><?=$row['年'] ?></option>
<?php
}
?>
		</select>
	</div>
	<div class="container">
		開始月:<select name="start_month">
			<?php
			for ($i = 1; $i <= 12; $i++) {
				if ($i == $_SESSION['start_month']) {
					$prop = 'selected';
				} else {
					$prop = '';
				}
			?>
				<option value="<?=$i ?>" <?=$prop ?> ><?=$i ?></option>
			<?php
			}
			?>
			   </select>
		～
		終了月:<select name="end_month">
			<?php
			for ($i = 1; $i <= 12; $i++) {
				if ($i == (isset($_SESSION['end_month']) ? $_SESSION['end_month'] : 12)) {
					$prop = 'selected';
				} else {
					$prop = '';
				}
			?>
				<option value="<?=$i ?>" <?=$prop ?> ><?=$i ?></option>
			<?php
			}
			?>
			   </select>

	</div>
	<input type="submit" value="集計" />
</form>
<hr>
<?php if (isset($_SESSION['shukei_result'])) { ?>
<table id="himoku_month">
	<caption><?=$_SESSION['year'] ?>年</caption>
	<colgroup id="himoku_label" span="1"></colgroup>
	<colgroup id="summary_data" span="<?=($_SESSION['end_month'] - $_SESSION['start_month'] + 2) ?>"></colgroup>
	<thead>
		<tr>
			<th>費目名</th>
			<?php for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) { ?>
				<th><?=$i ?>月</th>
			<?php } ?>
			<th>合計</th>
		</tr>
	</thead>
	<tbody>
<?php
	$nyukin_array = [];
	$shukkin_array = [];

	for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
		$nyukin_array[$i] = 0;  // [ 1 => 0, 2 => 0, ・・・, 12 => 0 ]
		$shukkin_array[$i] = 0; // [ 1 => 0, 2 => 0, ・・・, 12 => 0 ]
	}
/*
 * $_SESSIONのイメージ
	[
		'shukei_result' => [
								1 => [ 1 => 40000, 2 => 30000, 3 => 35000, ・・・, 12 => 42000 ],
								2 => [ 2 => 240000, 2 => 250000, 3=> 230000, ・・, 12 => 26000 ],
								        ・・・
								6 => [ 1 => 85000, 2 => 85000, 3 => 85000, ・・・. 12 => 85000 ],
							],
		'himoku_assoc' => [1 => '食費', 2 => '給料', ・・・ 6 => '居住費'],
		'himoku_inout' => [1 => '出金', 2 => '入金', 3 => '出金', 4 => '出金', 5 => '出金', 6 => '出金'],
	]
*/
	foreach ($_SESSION['shukei_result'] as $himoku => $month_summary) { ?>
		<tr>
			<th><?=$_SESSION['himoku_assoc'][$himoku] ?></th>
			<?php
				$year_total = 0;
				for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
					$year_total += $month_summary[$i];
					if ($_SESSION['himoku_inout'][$himoku] === '入金') {
						$nyukin_array[$i] += $month_summary[$i];
					} else { // 出金
						$shukkin_array[$i] += $month_summary[$i];
					}
			?>
				<td class="number"><?=$month_summary[$i] ?></td>
			<?php
				}
			?>
			<td class="number"><?=number_format($year_total) ?></td>
		</tr>
<?php
	}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>入金合計</th>
	<?php
		$nyukin_total = 0;
		for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
			$nyukin_total += $nyukin_array[$i];
	?>
			<td class="number"><?=$nyukin_array[$i] ?></td>
	<?php
		}
	?>
			<td class="number"><?=number_format($nyukin_total) ?></td>
		</tr>
		<tr>
			<th>出金合計</th>
	<?php
		$shukkin_total = 0;
		for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
			$shukkin_total += $shukkin_array[$i];
	?>
			<td class="number"><?=$shukkin_array[$i] ?></td>
	<?php
		}
	?>
			<td class="number"><?=number_format($shukkin_total) ?></td>
		</tr>
		<tr>
			<th>収支</th>
	<?php
		$shushi_total = 0;
		for ($i = $_SESSION['start_month']; $i <= $_SESSION['end_month']; $i++) {
			$shushi_total += ($nyukin_array[$i] - $shukkin_array[$i]);
	?>
			<td class="number"><?=($nyukin_array[$i] - $shukkin_array[$i]) ?></td>
	<?php
		}
	?>
			<td class="number"><?=number_format($shushi_total) ?></td>
		</tr>
	</tfoot>
</table>
<?php
	unset($_SESSION['shukei_result']);
}
?>
</body>
</html>