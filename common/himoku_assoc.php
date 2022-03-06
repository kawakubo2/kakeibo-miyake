<?php
require_once 'DbManager.php';

function getHimokuAssoc() {
	try {
		$db = getDb();
		$sql = "SELECT id, 費目名, 入出金区分
				FROM 費目
				ORDER BY id";
		$stt = $db->prepare($sql);
		$stt->execute();

		// キー:費目id 値:費目名
		$himoku_assoc = [];
		// キー:費目id 値:入出金区分
		$himoku_inout = [];
		while ($row = $stt->fetch(PDO::FETCH_ASSOC)) { // ['id' => 1, '費目名' => '食費', '入出金区分' => '出金']
			$himoku_assoc[$row['id']] = $row['費目名'];
			$himoku_inout[$row['id']] = $row['入出金区分'];
		}
		// $himoku_assoc = [ 1 => '食費', 2 => '給料', … ];
		// $himoku_inout = [ 1 => '出金', 2 => '入金', 3 => '出金' … ];
	} catch(PDOException $e) {
		// ログへの書き込み
		throw $e; // 再スロー
	}
	return [$himoku_assoc, $himoku_inout];
}
