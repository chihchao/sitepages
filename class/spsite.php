<?php
class SPSite {
	function SPSite() {}
	function addSite() {
		global $xoopsDB, $stpgErrorMessage, $spUser;
		if (ltrim($_POST['title']) == '') {
			$stpgErrorMessage = _MD_STPG_SITE_NOTITLE;
			return false;
		}
		escape_string_arr($_POST);
		$sql = 'Insert Into ' . $xoopsDB -> prefix('sitepages_sites') . ' (ownr_uid, title, slogan, edit_uids, xoops_page, block_location, theme) Values (\'' . $spUser -> uid . '\', \'' . htmlspecialchars($_POST['title']) . '\', \'' . $_POST['slogan'] . '\', \'' . implode_idstring($_POST['edit_uids']) . '\', \'' . intval($_POST['xoops_page']) . '\', \'' . intval($_POST['block_location']) . '\', \'' . $_POST['theme'] . '\')';
		if (!$xoopsDB -> query($sql)) return false;
		$sid = $xoopsDB -> getInsertId();
		//SPBlock::addBlock($sid, 'navigation');
		SPBlock::addBlock($sid, 'childpages');
		SPPage::addPage($sid, 0, 'homepage');
		return $sid;
	}
	function updateSite($sid) {
		global $xoopsDB, $stpgErrorMessage;
		if (ltrim($_POST['title']) == '') {
			$stpgErrorMessage = _MD_STPG_SITE_NOTITLE;
			return false;
		}
		escape_string_arr($_POST);
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_sites') . ' Set title = \'' . htmlspecialchars($_POST['title']) . '\', slogan = \'' . $_POST['slogan'] . '\', edit_uids = \'' . implode_idstring($_POST['edit_uids']) . '\', xoops_page = \'' . intval($_POST['xoops_page']) . '\', block_location = \'' . intval($_POST['block_location']) . '\', theme = \'' . $_POST['theme'] . '\' Where id = \'' . $sid . '\'';
		if (!$xoopsDB -> query($sql)) return false;
		return true;
	}
	function deleteSite($sid) {
		global $xoopsDB;
		$sql = 'Select id From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where sid= \'' . $sid . '\' And pid = \'0\'';
		if (!list($pid) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		SPPage::deletePages($pid);
		$sql = 'Delete From ' . $xoopsDB -> prefix('sitepages_blocks') . ' Where sid = \'' . $sid . '\'';
		$xoopsDB -> queryF($sql);
		$sql = 'Delete From ' . $xoopsDB -> prefix('sitepages_sites') . ' Where id = \'' . $sid . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		return true;
	}
	function getSite($sid) {
		global $xoopsDB;
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_sites') . ' Where id = \'' . $sid . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
		$record['ownr_uname'] = XoopsUser::getUnameFromId($record['ownr_uid']);
		$record['edit_uids'] = explode_idstring($record['edit_uids']);
		return $record;
	}
	function listSites() {
		global $xoopsDB;
		$recordset = array();
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_sites') . ' Order By title ASC';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['ownr_uname'] = XoopsUser::getUnameFromId($record['ownr_uid']);
			$record['edit_uids'] = explode_idstring($record['edit_uids']);
			$recordset[$record['id']] = $record;
		}
		return $recordset;
	}
	function listThemes() {
		$dir = './themes/';
		$dir_array = array();
		if(is_dir($dir) && $dh = opendir($dir)){
			while (($file = readdir($dh)) !== false) if (is_dir($dir . $file) && $file != '.' && $file != '..') array_push($dir_array, $file);
			closedir($dh);
		}
		return $dir_array;
	}
}
?>