<?php
//include
include_once('include.php');
include_once('class/spsite.php');
include_once('class/spfile.php');
//parameter
$option = (empty($_GET['option']) || (!in_array($_GET['option'], array('tn', 'df')))) ? 'fl' : $_GET['option'];
$fid = empty($_GET['fid']) ? 0 : intval($_GET['fid']);
$submitted = empty($_POST['submitted']) ? false : true;

//main
$spFile = new SPFile;
if (!($file = $spFile -> getFile($fid))) redirect_header('index.php', 5, _MD_STPG_NOFILE);
//authority
if ($option =='df') {
	$spSite = new SPSite;
	$sid = $spFile -> getFileSid($fid);
	if (!($site = $spSite -> getSite($sid))) redirect_header('index.php', 5, _MD_STPG_NOSITE);
	if (!($spUser -> checkAuthority('site_edit', $site))) redirect_header('index.php', 5, _MD_STPG_NOAUTHORITY);
	if ($submitted) { $spFile -> deleteFile($fid) ?  redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $file['pid'], 5, _MD_STPG_FILE_DELSUCCESS) : redirect_header('page.php?option=viw&sid=' . $sid . '&pid=' . $file['pid'], 5, _MD_STPG_FILE_DELFAIL . $stpgErrorMessage); }
	include(XOOPS_ROOT_PATH . "/header.php");
	xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], '[' . $file['file_name'] . '] ' . _MD_STPG_FILE_DELCONFIRM);
	include(XOOPS_ROOT_PATH . "/footer.php");
} else {
	if ($option == 'fl') $spFile -> addCounter($fid);
	$path = ($option == 'tn') ? 'thumbnail' : 'file';
	$path = $spFile -> getFilePath($path, $file['real_name']);

	/*
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Description: File Transfer');
	header('Content-type: ' . $file['file_type']);
	header('Content-Disposition: inline; filename=' . $file['file_name']);
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: ' . $file['file_size']);
	*/

	header('Expires: 0');
	header('Content-Type: ' . $file['file_type']);
	if (preg_match("/MSIE ([0-9]\.[0-9]{1,2})/", $HTTP_USER_AGENT)) {
		header('Content-Disposition: inline; filename="' . iconv('utf-8', 'big5', $file['file_name']) . '"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	} else {
		header('Content-Disposition: inline; filename="' . iconv('utf-8', 'big5', $file['file_name']) . '"');
		header('Pragma: no-cache');
	}
	header("Content-Transfer-Encoding: binary");
	//header("Content-Type: application/force-download");

	readfile($path);
	
}
exit();
?>