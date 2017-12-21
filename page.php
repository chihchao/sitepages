<?php
//include
include_once('include.php');
include_once('class/spsite.php');
include_once('class/spblock.php');
include_once('class/sppage.php');
include_once('class/spfile.php');
include_once(XOOPS_ROOT_PATH . '/class/template.php');

//parameter
$option = (empty($_GET['option']) || (!in_array($_GET['option'], array('add', 'edt', 'del', 'mov', 'mto', 'tre', 'vfl')))) ? 'viw' : $_GET['option'];
$submitted = empty($_POST['submitted']) ? false : true;
$pof = (empty($_GET['pof']) || $_GET['pof'] != 'f') ? 'p' : 'f';
$sid = empty($_GET['sid']) ? 0 : intval($_GET['sid']);
$pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
$fid = empty($_GET['fid']) ? 0 : intval($_GET['fid']);
$topid = empty($_GET['topid']) ? 0 : intval($_GET['topid']);

//function
function travelPageTreeString($tree, &$tree_string) {
	global $sid;
	$tree_string .= '<li><a href="page.php?option=viw&sid=' . $sid . '&pid=' . $tree['id'] . '" title="' . $tree['title'] . '">' . $tree['title'] . '</a>';
	if (!empty($tree['childs'])) {
		$tree_string .= '<ul>';
		foreach ($tree['childs'] as $val) travelPageTreeString($val, $tree_string);
		$tree_string .= '</ul>';
	}
	$tree_string .= '</li>';
}
function getPageTreeHTMLString($tree) {
	travelPageTreeString($tree, $tree_string);
	return '<ul>' . $tree_string . '</ul>';
}
function travelMoveTreeString($tree, $childs, &$tree_string) {
	global $sid, $pid;
	if (in_array($tree['id'], $childs)) {
		$tree_string .= '<li>' . $tree['title'];
	} else {
		$tree_string .= '<li><a href="page.php?option=mto&sid=' . $sid . '&pid=' . $pid . '&topid=' . $tree['id'] . '" title="' . $tree['title'] . '">' . $tree['title'] . '</a>';
	}
	if (!empty($tree['childs'])) {
		$tree_string .= '<ul>';
		foreach ($tree['childs'] as $val) travelMoveTreeString($val, $childs, $tree_string);
		$tree_string .= '</ul>';
	}
	$tree_string .= '</li>';
}
function getMoveTreeString($tree, $childs) {
	travelMoveTreeString($tree, $childs, $tree_string);
	return '<ul>' . $tree_string . '</ul>';
}
//main
$spSite = new SPSite;
$spPage = new SPPage;
$spBlock = new SPBlock;
$spFile = new SPFile;
if (!($site = $spSite -> getSite($sid))) redirect_header('index.php', 5, _MD_STPG_NOSITE);
switch ($option) {
	case 'add':
		if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid = 0, get right pid
		$pid = $page['id'];
		if ($submitted) {
			if ($pof == 'f') {
				($pid = $spPage -> addPage($sid, $pid, 'folder')) ? redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_ADDSUCCESS . $stpgErrorMessage) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_PAGE_ADDFAIL . $stpgErrorMessage);
			} else {
				($pid = $spPage -> addPage($sid, $pid, 'page')) ? redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_ADDSUCCESS) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_PAGE_ADDFAIL . $stpgErrorMessage);
			}
		}
		//set template values
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => ($pof == 'f') ? _MD_STPG_PAGE_ADDFOLDER : _MD_STPG_PAGE_ADDPAGE,
			'option' => $option,
			'sid' => $sid,
			'pid' => $pid,
			'pof' => $pof,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['router'] = array();
		$spPage -> routerPage($sid, $pid, $tplvar['router']);
		$tplvar['blocks'] = $spBlock -> getBlocksBySid($sid);
		set_module_header('tinymce');
		set_module_header('jquery');
		set_module_header('filefield');
	break;
	case 'edt':
		if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid = 0, get right pid
		$pid = $page['id'];
		if ($submitted) ($spPage -> updatePage($pid)) ? redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_EDTSUCCESS . $stpgErrorMessage) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_PAGE_EDTFAIL . $stpgErrorMessage);
		//set template values
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_PAGE_EDTPAGE,
			'option' => $option,
			'sid' => $sid,
			'pid' => $pid,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['router'] = array();
		$spPage -> routerPage($sid, $pid, $tplvar['router']);
		$tplvar['blocks'] = $spBlock -> getBlocksBySid($sid);
		foreach ($tplvar['blocks'] as $k => $v) $tplvar['blocks'][$k]['checked'] = (in_array($tplvar['blocks'][$k]['id'], $page['blocks'])) ? true : false;
		$tplvar['page'] = $page;
		set_module_header('tinymce');
		set_module_header('jquery');
		set_module_header('filefield');
		set_module_header('themecss', $site['theme']);
	break;
	case 'del':
		if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid = 0, get right pid
		$pid = $page['id'];
		if ($page['pid'] == 0) redirect_header('page.php?option=viw&sid=' . $sid, 5, _MD_STPG_PAGE_DELROOTNOT);
		if ($submitted) { $spPage -> deletePages($pid) ?  redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $page['pid'], 5, _MD_STPG_PAGE_DELSUCCESS) : redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_DELFAIL . $stpgErrorMessage); }
		include(XOOPS_ROOT_PATH . "/header.php");
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_STPG_PAGE_DELCONFIRM);
		include(XOOPS_ROOT_PATH . "/footer.php");
	break;
	case 'mov':
		if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid = 0, get right pid
		$pid = $page['id'];
		$tree = $spPage -> getPageTree($sid, 0);
		$childs = array();
		$childpages = array();
		$spPage -> getChildPagesAll($pid, $childpages);
		foreach ($childpages as $k => $v) array_push($childs, $k);
		array_push($childs, $pid);
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_PAGE_MOVPAGE,
			'option' => $option,
			'sid' => $sid,
			'pid' => $pid,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['site'] = $site;
		$tplvar['tree_string'] = getMoveTreeString($tree, $childs);
		$tplvar['router'] = array();
		$spPage -> routerPage($sid, $pid, $tplvar['router']);
		$tplvar['page'] = $page;
		set_module_header('themecss', $site['theme']);
	break;
	case 'mto':
		if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		if (!($topage = $spPage -> getPage($sid, $topid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid or topod = 0, get right pid and topid
		$pid = $page['id'];
		$topid = $topage['id'];
		if ($submitted) { $spPage -> updatePagePid($pid, $topid) ?  redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_MOVSUCCESS) : redirect_header('page.php?option=mov&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_MOVFAIL . $stpgErrorMessage); }
		include(XOOPS_ROOT_PATH . "/header.php");
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_STPG_PAGE_MOVCONFIRM . $topage['title']);
		include(XOOPS_ROOT_PATH . "/footer.php");
	break;
	case 'tre':
		$tree = $spPage -> getPageTree($sid, 0);
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_BLOCK_SITETREE,
			'option' => $option,
			'sid' => $sid,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['site'] = $site;
		$tplvar['tree_string'] = getPageTreeHTMLString($tree);
		set_module_header('themecss', $site['theme']);
	break;
	case 'vfl':
		if (!$xoopsModuleConfig['view_file']) redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, _MD_STPG_PAGE_VIEWFILE_NO);
		$spFile = new SPFile;
		if (!($file = $spFile -> getFile($fid))) redirect_header('index.php', 5, _MD_STPG_NOFILE);
		$pid = $file['pid'];
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => $page['title'],
			'option' => $option,
			'sid' => $sid,
			'pid' => $pid,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['site'] = $site;
		$page['blocks'] = array();
		$tplvar['page'] = $page;
		$tplvar['file'] = $file;
		$tplvar['router'] = array();
		$spPage -> routerPage($sid, $pid, $tplvar['router']);
		$tplvar['files'] = $spFile -> listFiles($pid);
		$tplvar['embed_html'] = view_file_embed_html($file);
		set_module_header('themecss', $site['theme']);
		set_module_header('jwplayer');
	break;
	default:
		if (!($page = $spPage -> getPage($sid, $pid))) redirect_header('index.php', 5, _MD_STPG_NOPAGE);
		//if pid = 0, get right pid
		$pid = $page['id'];
		if ($submitted) {
			if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
			$spFile -> addFiles($pid);
			redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $pid, 5, $stpgErrorMessage);
		}
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => $page['title'],
			'option' => $option,
			'sid' => $sid,
			'pid' => $pid,
		);
		$tplvar['auth'] = array(
			'is_site_managers' => $spUser -> checkAuthority('site_managers'),
			'is_site_ownr' => $spUser -> checkAuthority('site_ownr', $site['ownr_uid']),
			'is_site_edit' => $spUser -> checkAuthority('site_edit', $site),
		);
		$tplvar['site'] = $site;
		$tplvar['page'] = $page;
		$tplvar['page']['blocks'] = $spBlock -> getBlocksByBids($page['blocks']);
		$tplvar['router'] = array();
		$spPage -> routerPage($sid, $pid, $tplvar['router']);
		$tplvar['childpages'] = $spPage -> listChildPages($pid);
		$tplvar['files'] = $spFile -> listFiles($pid);
		set_module_header('themecss', $site['theme']);
		set_module_header('jquery');
		set_module_header('filefield');
	break;
}

//template
if ($site['xoops_page']) {
	$xoopsOption['template_main'] = 'stpg_page.htm';
	include(XOOPS_ROOT_PATH . '/header.php');
	$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
	$xoopsTpl -> assign('tplvar', $tplvar);
	include(XOOPS_ROOT_PATH . '/footer.php');
} else {
	$tpl = new XoopsTpl();
	$tpl -> assign('xoops_module_header', $xoops_module_header);
	$tpl -> assign('tplvar', $tplvar);
	$tpl -> display('db:sitepages.htm');
}
?>