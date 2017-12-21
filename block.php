<?php
//include
include_once('include.php');
include_once('class/spsite.php');
include_once('class/spblock.php');

//parameter
$option = (empty($_GET['option']) || (!in_array($_GET['option'], array('edt', 'del', 'odr')))) ? 'add' : $_GET['option'];
$submitted = empty($_POST['submitted']) ? false : true;
$sid = empty($_GET['sid']) ? 0 : intval($_GET['sid']);
$bid = empty($_GET['bid']) ? 0 : intval($_GET['bid']);
$uod = (empty($_GET['uod']) || $_GET['uod'] != 'd') ? 'u' : 'd';

//main
$spSite = new SPSite;
$spBlock = new SPBlock;
if (!($site = $spSite -> getSite($sid))) redirect_header('index.php', 5, _MD_STPG_NOSITE);
if (!$spUser -> checkAuthority('site_ownr', $site['ownr_uid'])) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
switch ($option) {
	case 'edt':
		if (!($block = $spBlock ->getBlock($bid))) redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_NOBLOCK);
		if ($submitted) ($spBlock -> updateBlock($bid)) ? redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_EDTSUCCESS) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_BLOCK_EDTFAIL . $stpgErrorMessage);
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_BLOCK_EDTBLOCK,
			'option' => $option,
			'sid' => $sid,
		);
		$tplvar['auth']['is_site_managers'] = $spUser -> checkAuthority('site_managers');
		$tplvar['block'] = $block;
		set_module_header('tinymce');
	break;
	case 'odr':
		($spBlock -> updateBlockOrder($bid, $uod)) ? redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_UPDATEORDERSUCCESS) : redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_UPDATEORDERFAIL);
	break;
	case 'del':
		if (!($block = $spBlock ->getBlock($bid))) redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_NOBLOCK);
		if ($block['block_type']) redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_DELTYPEERR);
		if ($submitted) { $spBlock -> deleteBlock($bid) ?  redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_DELSUCCESS) : redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_DELFAIL . $stpgErrorMessage); }
		include(XOOPS_ROOT_PATH . "/header.php");
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_STPG_BLOCK_DELCONFIRM);
		include(XOOPS_ROOT_PATH . "/footer.php");
	break;
	default:
		if ($submitted) ($spBlock -> addBlock($sid, 'block')) ? redirect_header('site.php?option=edt&sid=' . $sid, 5, _MD_STPG_BLOCK_ADDSUCCESS) : redirect_header(xoops_getenv('REQUEST_URI'), 5, _MD_STPG_BLOCK_ADDFAIL . $stpgErrorMessage);
		$tplvar = array(
			'module_name' => $xoopsModule -> getVar('name'),
			'header' => _MD_STPG_BLOCK_ADDBLOCK,
			'option' => $option,
			'sid' => $sid,
		);
		$tplvar['auth']['is_site_managers'] = $spUser -> checkAuthority('site_managers');
		set_module_header('tinymce');
	break;
}
//template
$xoopsOption['template_main'] = 'stpg_block.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>