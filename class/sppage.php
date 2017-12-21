<?php
class SPPage {
	function SPPage() {}
	function addPage($sid, $pid, $df = 'page') {
		global $xoopsDB, $stpgErrorMessage;
		switch ($df) {
			case 'homepage':
				$pid = 0;
				$page_type = 0;
				$title = htmlspecialchars($_POST['title']) . _MD_STPG_PAGE_HOMEPAGE;
				$content = '';
				$header = 1;
				$navigation = 1;
				$router = 1;
				$blks = SPBlock::getBlocksBySid($sid);
				$blocks = array();
				foreach ($blks as $k => $v) array_push($blocks, $k);
				$blocks = implode_idstring($blocks);
			break;
			case 'folder':
				if (ltrim($_POST['title']) == '') {
					$stpgErrorMessage = _MD_STPG_PAGE_NOTITLE;
					return false;
				}
				escape_string_arr($_POST);
				$page_type = 1;
				$title = htmlspecialchars($_POST['title']);
				$content = '';
				$header = intval($_POST['header']);
				$navigation = intval($_POST['navigation']);
				$router = intval($_POST['router']);
				$blocks = implode_idstring($_POST['blocks']);
			break;
			default:
				if (ltrim($_POST['title']) == '') {
					$stpgErrorMessage = _MD_STPG_PAGE_NOTITLE;
					return false;
				}
				escape_string_arr($_POST);
				$page_type = 0;
				$title = htmlspecialchars($_POST['title']);
				$content = $_POST['tinymce_content'];
				$header = intval($_POST['header']);
				$navigation = intval($_POST['navigation']);
				$router = intval($_POST['router']);
				$blocks = implode_idstring($_POST['blocks']);
			break;
		}
		$sql = 'Insert Into ' . $xoopsDB -> prefix('sitepages_pages') . ' (sid, pid, page_type, title, content, header, navigation, router, blocks) Values (\'' . $sid . '\', \'' . $pid . '\', \'' . $page_type . '\', \'' . $title . '\', \'' . $content . '\', \'' . $header . '\', \'' . $navigation . '\', \'' . $router . '\', \'' . $blocks . '\')';
		if (!$xoopsDB -> query($sql)) return false;
		//pid become the id of new page
		$pid = $xoopsDB -> getInsertId();
		if ($df == 'folder') SPFile::addFiles($pid);
		return $pid;
	}
	function updatePage($pid) {
		global $xoopsDB, $stpgErrorMessage;
		if (ltrim($_POST['title']) == '') {
			$stpgErrorMessage = _MD_STPG_PAGE_NOTITLE;
			return false;
		}
		$sql = 'Select page_type From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where id = \'' . $pid . '\'';
		if (!list($page_type) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) {
			$stpgErrorMessage = _MD_STPG_NOPAGE;
			return false;
		}
		escape_string_arr($_POST);
		$title = htmlspecialchars($_POST['title']);
		$content = ($page_type) ? '' : $_POST['tinymce_content'];
		$header = intval($_POST['header']);
		$navigation = intval($_POST['navigation']);
		$router = intval($_POST['router']);
		$blocks = implode_idstring($_POST['blocks']);
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_pages') . ' Set title = \'' . $title . '\', content = \'' . $content . '\', header = \'' . $header . '\', navigation = \'' . $navigation . '\', router = \'' . $router . '\', blocks = \'' . $blocks . '\' Where id = \'' . $pid . '\'';
		if (!$xoopsDB -> query($sql)) return false;
		if ($page_type) SPFile::addFiles($pid);
		return true;
	}
	function updatePagePid($pid, $topid) {
		global $xoopsDB, $stpgErrorMessage;
		//get childs array
		$childs = array();
		$childpages = array();
		SPPage::getChildPagesAll($pid, $childpages);
		foreach ($childpages as $k => $v) array_push($childs, $k);
		array_push($childs, $pid);
		if (in_array($topid, $childs)) {
			$stpgErrorMessage = _MD_STPG_PAGE_MOVCHILDSNO;
			return false;
		}
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_pages') . ' Set pid = \'' . $topid . '\' Where id = \'' . $pid . '\'';
		if (!$xoopsDB -> query($sql)) return false;
		return true;
	}
	function deletePages($pid) {
		global $xoopsDB, $stpgErrorMessage;
		$childs = array();
		SPPage::getChildPagesAll($pid, $childs);
		$del_pids = array();
		foreach ($childs as $k => $v) array_push($del_pids, $k);
		array_push($del_pids, $pid);
		//delete files
		$sql = implode('\' Or pid = \'', $del_pids);
		$sql = 'Select id From ' . $xoopsDB -> prefix('sitepages_files') . ' Where pid = \'' . $sql . '\'';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while (list($fid) = $xoopsDB -> fetchRow($result)) SPFile::deleteFile($fid);
		$sql = implode('\' Or id = \'', $del_pids);
		$sql = 'Delete From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where id = \'' . $sql . '\'';
		if (!($xoopsDB -> queryF($sql))) return false;
		return true;
	}
	function getPage($sid, $pid = 0) {
		global $xoopsDB;
		$sql = (empty($pid)) ? ' pid' : ' id';
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where sid = \'' . $sid . '\' And' . $sql . ' = \'' . $pid . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
		$record['blocks'] = explode_idstring($record['blocks']);
		return $record;
	}
	function listChildPages($pid) {
		global $xoopsDB;
		$recordset = array();
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where pid = \'' . $pid . '\' Order By title ASC';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['blocks'] = explode_idstring($record['blocks']);
			$recordset[$record['id']] = $record;
		}
		return $recordset;
	}
	function getPageTree($sid, $pid = 0) {
		$page = SPPage::getPage($sid, $pid);
		$pagechilds = SPPage::listChildPages($page['id']);
		$childs = array();
		foreach ($pagechilds as $val) array_push($childs, SPPage::getPageTree($sid, $val['id']));
		$tree = array(
			'id' => $page['id'],
			'title' => $page['title'],
			'page_type' => $page['page_type'],
			'childs' => $childs,
		);
		return $tree;
	}
	function getChildPagesAll($pid, &$childs) {
		$pagechilds = SPPage::listChildPages($pid);
		foreach ($pagechilds as $val) {
			$childs[$val['id']] = $val['title'];
			SPPage::getChildPagesAll($val['id'], $childs);
		}
	}
	function routerPage($sid, $pid, &$router) {
		global $xoopsDB;
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where sid = \'' . $sid . '\' And id = \'' . $pid . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
		$record['blocks'] = explode_idstring($record['blocks']);
		array_unshift($router, $record);
		if (!empty($record['pid'])) SPPage::routerPage($sid, $record['pid'], $router);
	}
}
?>