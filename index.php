<?php
//include
include_once('include.php');
include_once('class/spsite.php');

//main
$spSite = new SPSite;
//set template values
$tplvar = array();
$tplvar['module_name'] = $xoopsModule -> getVar('name');
$tplvar['header'] = $xoopsModule -> getVar('name');
$tplvar['auth']['is_site_managers'] = $spUser -> checkAuthority('site_managers');
$tplvar['sites'] = $spSite -> listSites();
foreach ($tplvar['sites'] as $k => $v) $tplvar['sites'][$k]['ownr'] = ($tplvar['sites'][$k]['ownr_uid'] == $spUser -> uid || $spUser -> checkAuthority('admin')) ? true : false;
//template
$xoopsOption['template_main'] = 'stpg_index.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>