<?php
//include
include_once('include.php');
include_once('class/spsite.php');
include_once('class/spblock.php');
include_once('class/sppage.php');
include_once('class/spfile.php');

//parameter
$option = (empty($_GET['option']) || (!in_array($_GET['option'], array('add', 'edt', 'del')))) ? 'viw' : $_GET['option'];
$submitted = empty($_POST['submitted']) ? false : true;
$sid = empty($_GET['sid']) ? 0 : intval($_GET['sid']);

//main
$spSite = new SPSite;
switch ($option) {
	case 'add':
		if (!$spUser -> checkAuthority('site_managers')) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if ($submitted) ($sid = $spSite -> addSite()) ? redirect_header('page.php?sid=' . $sid, 5, _MD_STPG_SITE_ADDSUCCESS) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_SITE_ADDFAIL . $stpgErrorMessage);
		$site_managers = $spUser -> listSiteManagers();
		$site_managers_uids = array();
		$site_managers_name = array();
		foreach ($site_managers as $val) {
			array_push($site_managers_uids, $val['uid']);
			array_push($site_managers_name, $val['uname']);
		}
		$tplvar = array();
		$tplvar['module_name'] = $xoopsModule -> getVar('name');
		$tplvar['header'] = _MD_STPG_SITE_ADDSITE;
		$tplvar['option'] = $option;
		$tplvar['auth']['is_site_managers'] = $spUser -> checkAuthority('site_managers');
		$tplvar['form'] = array(
			'site_managers' => array('uids' => $site_managers_uids, 'name' => $site_managers_name),
			'themes' => $spSite -> listThemes(),
		);
		$tplvar['site']['theme'] = 'default';
	break;
	case 'edt':
		if (!($site = $spSite -> getSite($sid))) redirect_header('index.php', 5, _MD_STPG_NOSITE);
		if (!$spUser -> checkAuthority('site_ownr', $site['ownr_uid'])) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if ($submitted) ($spSite -> updateSite($sid)) ? redirect_header('page.php?sid=' . $sid, 5, _MD_STPG_SITE_EDTSUCCESS) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_SITE_EDTFAIL . $stpgErrorMessage);
		$spBlock = new SPBlock;
		$site_managers = $spUser -> listSiteManagers();
		$site_managers_uids = array();
		$site_managers_name = array();
		foreach ($site_managers as $val) {
			array_push($site_managers_uids, $val['uid']);
			array_push($site_managers_name, $val['uname']);
		}
		$blocks = $spBlock -> getBlocksBySid($sid);
		$count_blocks = $spBlock -> countBlocks($sid);
		foreach ($blocks as $k => $v) {
			$blocks[$k]['odr_top'] = ($v['odr']) ? false : true;
			$blocks[$k]['odr_bottom'] = (($v['odr'] + 1) == $count_blocks) ? true : false;
			$blocks[$k]['deleted'] = ($v['block_type']) ? false : true;
		}
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_SITE_EDTSITE,
			'option' => $option,
			'sid' => $sid,
		);
		$tplvar['auth']['is_site_managers'] = $spUser -> checkAuthority('site_managers');
		$tplvar['form'] = array(
			'site_managers' => array('uids' => $site_managers_uids, 'name' => $site_managers_name),
			'themes' => $spSite -> listThemes(),
		);
		$tplvar['site'] = $site;
		$tplvar['blocks'] = $blocks;
	break;
	case 'del':
		if (!($site = $spSite -> getSite($sid))) redirect_header('index.php', 5, _MD_STPG_NOSITE);
		if (!$spUser -> checkAuthority('site_ownr', $site['ownr_uid'])) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
		if ($submitted) { $spSite -> deleteSite($sid) ?  redirect_header('index.php', 5, _MD_STPG_SITE_DELSUCCESS) : redirect_header('index.php', 5, _MD_STPG_SITE_DELFAIL . $stpgErrorMessage); }
		include(XOOPS_ROOT_PATH . "/header.php");
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_STPG_SITE_DELCONFIRM);
		include(XOOPS_ROOT_PATH . "/footer.php");
	break;
	default:
		header('location:index.php');
	break;
}
//template
$xoopsOption['template_main'] = 'stpg_site.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>