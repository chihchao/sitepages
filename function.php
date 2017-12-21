<?php
//deal slashes problem, set magic_quotes_gpc off
function setoff_magic_quotes_gpc() {
	if (get_magic_quotes_gpc()) {
		function traverse(&$arr) {
			if (!is_array($arr)) return;
			foreach ($arr as $key => $val) is_array ($arr[$key]) ? traverse($arr[$key]) : ($arr[$key] = stripslashes ($arr[$key]));
		}
		$gpc = array( &$_GET, &$_POST, &$_COOKIE );
		traverse($gpc);
	}
}
//escape string for array data
function escape_string($string) {
	if (function_exists('mysql_real_escape_string')) {
		$string = mysql_real_escape_string($string);
	} elseif (function_exists('mysql_escape_string')) {
		$string = mysql_escape_string($string);
	} else {
		$string = addslashes($string);
	}
	return $string;
}
function escape_string_arr_trv(&$arr) {
	if (!is_array($arr)) return;
	foreach ($arr as $key => $val) is_array($arr[$key]) ? escape_string_arr_trv($arr[$key]) : ($arr[$key] = escape_string($arr[$key]));
}
function escape_string_arr(&$arr) { escape_string_arr_trv($arr); }
//split a id string, string like [*][*][*]...
function explode_idstring($id_string) {
	if (empty($id_string)) {
		$id_string = array();
	} else {
		$id_string = explode('][', substr($id_string, 1, -1));
		foreach($id_string as $key => $val) $id_string[$key] = intval($val);
		$id_string = '[' . implode('][', array_unique($id_string)) . ']';
		$id_string = explode('][', substr($id_string, 1, -1));
	}
	return $id_string;
}
//join a id string
function implode_idstring($id_array) {
	if (!is_array($id_array)) $id_array = array();
	if (empty($id_array)) {
		$id_array = '';
	} else {
		foreach($id_array as $key => $val) $id_array[$key] = intval($val);
		$id_array = '[' . implode('][', array_unique($id_array)) . ']';
	}
	return $id_array;
}
//set tinymce header
function set_module_header($type, $var = '') {
	global $xoops_module_header;
	switch ($type) {
		case 'tinymce':
			$xoops_module_header .= '
<script language="javascript" type="text/javascript" src="tinymce/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	theme : "advanced",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_buttons1 : "fontselect,fontsizeselect,separator,bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator",
	theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright, justifyfull,separator,bullist,numlist,separator,outdent,indent",
	theme_advanced_buttons3 : "link,unlink,image,separator,cut,copy,paste,separator,undo,redo,separator,cleanup,removeformat,code",
	content_css : "tinymce/tinymce.css",
	extended_valid_elements : "a[href|title=' . _MD_STPG_TINYMCE_ATITLE . '|target],img[class|src|border=0|alt=' . _MD_STPG_TINYMCE_IMGALT . '|title|width|height|align|name],ul[class],li[class]",
	mode : "exact",
	elements : "tinymce_content",
	language : "zh_tw_utf8",
	debug : false,
	remove_linebreaks : false
});
</script>
			';
		break;
		case 'jquery':
			$xoops_module_header .= '
<script type="text/javascript" src="js/jquery.js"></script>
			';
		break;
		case 'filefield':
			$xoops_module_header .= '
<script type="text/javascript">
function addFileField(e) { $(\'#STPGFormFileField\').append(\'<li><input type="file" name="file[]" size="10" /><br /><input type="text" name="description[]" value="" /></li>\'); }
$(document).ready(function(e){
	$("#STPGFormFileField > *").remove();
	$("#STPGFormAddField").click(addFileField);
});
</script>
			';
		break;
		case 'jwplayer':
			$xoops_module_header .= '
<script type="text/javascript" src="jwplayer/swfobject.js"></script>
			';
		break;
		case 'stylecss':
			$xoops_module_header .= '
<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
			';
		break;
		case 'themecss':
			$xoops_module_header .= '
<link rel="stylesheet" type="text/css" media="screen" href="themes/' . $var . '/style.css" />
			';
		break;
		default:
			$xoops_module_header = '';
		break;
	}
}
function view_file_embed_html($file) {
	$file_name = strtolower(substr(strrchr($file['file_name'], '.'), 1));
	if ($file_name == 'jpg' || $file_name == 'jpeg' || $file_name == 'gif' || $file_name == 'png') {
		return '<img src="file.php?option=fl&fid=' . $file['id'] . '" title="' . $file['description'] . '" width="100%" />';
	} elseif ($file_name == 'mpg' || $file_name == 'avi' || $file_name == 'wmv') {
		return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="480" height="400">
			<param name="url" value="file.php?option=fl&fid=' . $file['id'] . '">
			<param name="uiMode" value="mini">
			<param name="autoStart" value="true">
			<embed src="file.php?option=fl&fid=' . $file['id'] . '" type="video/x-ms-wmv" width="480" height="400" autoStart="1" showControls="1"></embed>
			</object>';
	
	} elseif ($file_name == 'mp3' || $file_name == 'wav' || $file_name == 'wma' || $file_name == 'mid') {
		return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" type="application/x-oleobject" height="45" width="150">
			<param name="url" value="file.php?option=fl&fid=' . $file['id'] . '">
			<param name="autoStart" value="true">
			<param name="loop" value="false">
			<param name="uiMode" value="mini">
			<param name="volume" value="50">
			<embed src="file.php?option=fl&fid=' . $file['id'] . '" type="video/x-ms-wmv" height="45" width="150" autoStart="1" loop="0" volume="50" ShowPositionControls="0" showControls="1">
			</embed>
			</object>';
	} elseif ($file_name == 'pdf' || $file_name == 'pps' || $file_name == 'ppt' || $file_name == 'tif') {
		return '<iframe src="http://docs.google.com/viewer?url=' . rawurlencode(XOOPS_URL . '/modules/sitepages/file.php?option=fl&fid=' . $file['id']) . '&embedded=true" width="100%" height="780" style="border: none;"></iframe>';
	} else {
		return false;
	}
}
?>