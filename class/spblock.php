<?php
class SPBlock {
	function SPBlock() {}
	function addBlock($sid, $df = 'block') {
		global $xoopsDB, $stpgErrorMessage;
		switch ($df) {
			case 'navigation':
				$block_type = 1;
				$title = _MD_STPG_BLOCK_NAVIGATION;
				$content = '';
				$odr = SPBlock::countBlocks($sid);
			break;
			case 'childpages':
				$block_type = 2;
				$title = _MD_STPG_BLOCK_CHILDPAGES;
				$content = '';
				$odr = SPBlock::countBlocks($sid);
			break;
			default:
				if (ltrim($_POST['title']) == '') {
					$stpgErrorMessage = _MD_STPG_BLOCK_NOTITLE;
					return false;
				}
				escape_string_arr($_POST);
				$block_type = 0;
				$title = htmlspecialchars($_POST['title']);
				$content = $_POST['tinymce_content'];
				$odr = SPBlock::countBlocks($sid);
			break;
		}
		$sql = 'Insert Into ' . $xoopsDB -> prefix('sitepages_blocks') . ' (sid, block_type, title, content, odr) Values (\'' . $sid . '\', \'' . $block_type . '\', \'' . $title . '\', \'' . $content . '\', \'' . $odr . '\')';
		if (!$xoopsDB -> query($sql)) return false;
		$bid = $xoopsDB -> getInsertId();
		if ($_POST['appear_all']) SPBlock::appearBlockToAllPages($bid, true);
		return true;
	}
	function updateBlock($bid) {
		global $xoopsDB, $stpgErrorMessage;
		if (ltrim($_POST['title']) == '') {
			$stpgErrorMessage = _MD_STPG_BLOCK_NOTITLE;
			return false;
		}
		escape_string_arr($_POST);
		$title = htmlspecialchars($_POST['title']);
		$content = $_POST['tinymce_content'];
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_blocks') . ' Set title = \'' . $title . '\', content = \'' . $content . '\' Where id = \'' . $bid . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		if ($_POST['appear_all']) SPBlock::appearBlockToAllPages($bid, true);
		if ($_POST['disappear_all']) SPBlock::appearBlockToAllPages($bid, false);
		return true;
	}
	function updateBlockOrder($bid, $uod) {
		global $xoopsDB, $stpgErrorMessage;
		$uod = ($uod == 'u') ? -1 : 1;
		$sql = 'Select sid, odr From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . $bid . '\'';
		if (!list($sid, $odr) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		//the new order is error
		if (($uod == -1 && $odr == 0) || ($uod == 1 && ($odr + 1) == SPBlock::countBlocks($sid))) return false;
		$uod = $odr + $uod;
		$sql = 'Select id From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where odr = \'' . $uod . '\' And sid = ' . $sid;
		if (!list($id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_blocks') . ' Set odr = \'' . $uod . '\' Where id = \'' . $bid . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_blocks') . ' Set odr = \'' . $odr . '\' Where id = \'' . $id . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		return true;
	}
	function deleteBlock($bid) {
		global $xoopsDB;
		$sql = 'Select sid, odr From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . $bid . '\'';
		if (!list($sid, $odr) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		$sql = 'Delete From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . $bid . '\'';
		if (!($xoopsDB -> queryF($sql))) return false;
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_pages') . ' set blocks = REPLACE(blocks, \'[' . $bid . ']\', \'\') Where sid = \'' . $sid . '\'';
		$xoopsDB -> queryF($sql);
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_blocks') . ' Set odr = odr - 1 Where sid = \'' . $sid . '\' And odr > \'' . $odr . '\'';
		$xoopsDB -> queryF($sql);
		return true;
	}
	function appearBlockToAllPages($bid, $appear = true) {
		global $xoopsDB;
		$sql = 'Select sid From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . $bid . '\'';
		if (!list($sid) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		$sql = ($appear) ? ' set blocks = CONCAT(blocks, \'[' . $bid . ']\') Where sid = \'' . $sid . '\' And blocks Not Like \'%[' . $bid . ']%\'' : ' set blocks = REPLACE(blocks, \'[' . $bid . ']\', \'\') Where sid = \'' . $sid . '\'';
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_pages') . $sql;
		if (!$xoopsDB -> queryF($sql)) return false;
		return true;
	}
	function getBlock($bid) {
		global $xoopsDB;
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . $bid . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
		return $record;
	}
	function getBlocksByBids($bids) {
		global $xoopsDB;
		if (empty($bids) || !is_array($bids)) return array();
		$recordset = array();
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where id = \'' . implode('\' Or id = \'', $bids) . '\' Order By odr ASC';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
		return $recordset;
	}
	function getBlocksBySid($sid) {
		global $xoopsDB;
		$recordset = array();
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where sid = \'' . $sid . '\' Order By odr ASC';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
		return $recordset;
	}
	function countBlocks($sid) {
		global $xoopsDB;
		$sql = 'Select count(1) From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where sid = \'' . $sid . '\'';
		if (!list($counter) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		return $counter;
	}
}
?>