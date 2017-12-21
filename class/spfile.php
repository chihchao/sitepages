<?php
class SPFile {
	function SPFile() {}
	function addFiles($pid) {
		global $xoopsDB, $xoopsModuleConfig, $stpgErrorMessage;
		$filetype_image = array('image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png');
		$thumbnail_size = 100;
		$upload_path = SPFile::getFilePath('upload');
		if (!is_dir($upload_path) || !is_writeable($upload_path)) {
			if (!is_dir(SPFile::getFilePath('upload')) && !mkdir(SPFile::getFilePath('upload'))) {
				$stpgErrorMessage = _MD_STPG_ERRMSG_FILE_NOUPLOADPATH;
				return false;
			}
		}
		//test page exist or not
		$sql = 'Select id From ' . $xoopsDB -> prefix('sitepages_pages') . ' Where id = \'' . $pid . '\'';
		if (!list($sid) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) {
			$stpgErrorMessage = _MD_STPG_NOPAGE;
			return false;
		}
		foreach($_FILES['file']['tmp_name'] as $key => $val) {
			if (is_uploaded_file($_FILES['file']['tmp_name'][$key])) {
			//file type
			$filetype_ok = empty($xoopsModuleConfig['filetype_ok']) ? array() : explode('|', strtolower($xoopsModuleConfig['filetype_ok']));
			$filetype = strtolower(substr(strrchr($_FILES['file']['name'][$key], '.'), 1));
			if (empty($filetype_ok) || in_array($filetype, $filetype_ok)) {
			//file values
			$file = array();
			$file['file_name'] = escape_string($_FILES['file']['name'][$key]);
			$file['file_type'] = escape_string($_FILES['file']['type'][$key]);
			$file['file_size'] = escape_string($_FILES['file']['size'][$key]);
			$_POST['description'][$key] = (ltrim($_POST['description'][$key]) == '') ? $file['file_name'] : escape_string($_POST['description'][$key]);
			//the real name of file saved in server
			$file['real_name'] = function_exists('microtime') ? str_replace('0.', '', str_replace(' ', '_', microtime())) : time();
			//creat thumbnail and put file to upload path
			if (in_array($file['file_type'], $filetype_image)) SPFile::resizeImage($_FILES['file']['tmp_name'][$key], SPFile::getFilePath('thumbnail', $file['real_name']), $thumbnail_size, $thumbnail_size);
			if (!move_uploaded_file($_FILES['file']['tmp_name'][$key], SPFile::getFilePath('file', $file['real_name']))) {
				$stpgErrorMessage .= '[' . $_FILES['file']['name'][$key] . '] Can not move tmp_file to upload path.' . chr(13) . chr(10);
				continue;
			}
			$sql = 'Insert Into ' . $xoopsDB -> prefix('sitepages_files') . ' (pid, file_name, file_type, file_size, description, real_name, counter, date_time) Values (\'' . $pid . '\', \'' . $file['file_name'] . '\', \'' . $file['file_type'] . '\', \'' . $file['file_size'] . '\', \'' . $_POST['description'][$key] . '\', \'' . $file['real_name'] . '\', 0, \'' . time() . '\')';
			if ($xoopsDB -> query($sql)) {
				$stpgErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] Upload success.</li>' . chr(13) . chr(10);
			} else {
				$stpgErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] MySQL insert fail.</li>' . chr(13) . chr(10);
			}
			//if filetype_ok else
			} else {
				$stpgErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] File type error.</li>' . chr(13) . chr(10);
			}
			//if is_uploaded_file else
			} else {
				$stpgErrorMessage .= '<li>[file ' . $key . '] No upload file.</li>' . chr(13) . chr(10);
			}
		}
		$stpgErrorMessage = '<ul>' . $stpgErrorMessage . '</ul>';
		return true;
	}
	function addCounter($fid) {
		global $xoopsDB;
		$sql = 'Update ' . $xoopsDB -> prefix('sitepages_files') . ' Set counter = counter + 1 Where id = \'' . $fid . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		return true;
	}
	function deleteFile($fid) {
		global $xoopsDB, $stpgErrorMessage;
		$sql = 'Select real_name From ' . $xoopsDB -> prefix('sitepages_files') . ' Where id = \'' . $fid . '\'';
		if (!list($real_name) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) {
			$stpgErrorMessage = 'No file.';
			return false;
		}
		if (file_exists(SPFile::getFilePath('file', $real_name)) && unlink(SPFile::getFilePath('file', $real_name))) {
			if (file_exists(SPFile::getFilePath('thumbnail', $real_name))) unlink(SPFile::getFilePath('thumbnail', $real_name));
			$sql = 'Delete From ' . $xoopsDB -> prefix('sitepages_files') . ' Where id = \'' . $fid . '\'';
			if ($xoopsDB -> queryF($sql)) {
				return true;
			} else {
				$stpgErrorMessage = 'MySQL delete fail.';
				return false;
			}
		} else {
			$stpgErrorMessage = 'Delete file fail.';
			return false;
		}
	}
	function getFile($fid) {
		global $xoopsDB, $xoopsModuleConfig;
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_files') . ' Where id = \'' . $fid . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
		$record['icon'] =  SPFile::getFilePath('thumbnail', $record['real_name']);
		$record['icon'] =  (is_file($record['icon'])) ? 'file.php?option=tn&fid=' . $record['id'] : SPFile::getFilePath('default_icon', $record['file_name']);
		$record['view'] = (view_file_embed_html($record) && $xoopsModuleConfig['view_file']) ? true : false;
		return $record;
	}
	function listFiles($pid) {
		global $xoopsDB, $xoopsModuleConfig;
		$recordset = array();
		$sql = 'Select * From ' . $xoopsDB -> prefix('sitepages_files') . ' Where pid = \'' . $pid . '\' Order By date_time ASC';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['icon'] =  SPFile::getFilePath('thumbnail', $record['real_name']);
			$record['icon'] =  (is_file($record['icon'])) ? 'file.php?option=tn&fid=' . $record['id'] : SPFile::getFilePath('default_icon', $record['file_name']);
			$record['view'] = (view_file_embed_html($record) && $xoopsModuleConfig['view_file']) ? true : false;
			$recordset[$record['id']] = $record;
		}
		return $recordset;
	}
	function getFileSid($fid) {
		global $xoopsDB;
		$sql ='Select ' . $xoopsDB -> prefix('sitepages_pages') . '.sid From ' . $xoopsDB -> prefix('sitepages_files') . ' Left Join ' . $xoopsDB -> prefix('sitepages_pages') . ' On ' . $xoopsDB -> prefix('sitepages_files') . '.pid = ' . $xoopsDB -> prefix('sitepages_pages') . '.id Where ' . $xoopsDB -> prefix('sitepages_files') . '.id = \'' . $fid . '\'';
		if (!list($sid) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		return $sid;
	}
	function getFilePath($case = 'file', $file_name = '') {
		global $xoopsModuleConfig;
		$path = '';
		switch ($case) {;
			case 'upload':
				$path = XOOPS_ROOT_PATH . '/uploads/sitepages';
			break;
			case 'thumbnail':
				$path = XOOPS_ROOT_PATH . '/uploads/sitepages/' . $file_name . '_tn';
			break;
			case 'default_icon':
				$file_name = strtolower(substr(strrchr($file_name, '.'), 1));
				switch ($file_name) {
					case 'mp3':
						$path = 'images/icon_mp3.png';
					break;
					case 'wma':
						$path = 'images/icon_wma.png';
					break;
					case 'wav':
						$path = 'images/icon_wav.png';
					break;
					case 'mid':
						$path = 'images/icon_mid.png';
					break;
					case 'wmv':
						$path = 'images/icon_wmv.png';
					break;
					case 'mov':
						$path = 'images/icon_mov.png';
					break;
					case 'mpg':
						$path = 'images/icon_mpg.png';
					break;
					case 'avi':
						$path = 'images/icon_avi.png';
					break;
					case 'xls':
						$path = 'images/icon_xls.png';
					break;
					case 'ppt':
						$path = 'images/icon_ppt.png';
					break;
					case 'doc':
						$path = 'images/icon_doc.png';
					break;
					case 'odt':
						$path = 'images/icon_odt.png';
					break;
					case 'odp':
						$path = 'images/icon_odp.png';
					break;
					case 'ods':
						$path = 'images/icon_ods.png';
					break;
					case 'pdf':
						$path = 'images/icon_pdf.png';
					break;
					case 'txt':
						$path = 'images/icon_txt.png';
					break;
					case 'mm':
						$path = 'images/icon_mm.png';
					break;
					case 'sb':
						$path = 'images/icon_sb.png';
					break;
					case 'rar':
					case 'zip':
						$path = 'images/icon_zip.png';
					break;
					case 'flv':
					case 'swf':
						$path = 'images/icon_swf.png';
					break;
					case 'html':
					case 'htm':
						$path = 'images/icon_htm.png';
					break;
					default:
						$path = 'images/icon_default.png';
					break;
				}
			break;
			default:
				$path = XOOPS_ROOT_PATH . '/uploads/sitepages/' . $file_name;
			break;
		}
		return $path;
	}
	function resizeImage($source, $thumbnail, $max_width, $max_height){
		if (file_exists($source) && !empty($thumbnail)){
			$source_size = getimagesize($source); //圖檔大小
			if ($source_size[0] < $max_width && $source_size[1] < $max_height) {
				//圖檔寬、高都小於縮圖大小
				$thumbnail_size[0] = $source_size[0];
				$thumbnail_size[1] = $source_size[1];
			} else {
				$source_ratio = $source_size[0] / $source_size[1]; // 計算寬/高
				$thumbnail_ratio = $max_width / $max_height;
				if ($thumbnail_ratio > $source_ratio) {
					$thumbnail_size[1] = $max_height;
					$thumbnail_size[0] = $max_height * $source_ratio;
				}else{
					$thumbnail_size[0] = $max_width;
					$thumbnail_size[1] = $max_width / $source_ratio;
				}
			}
			if (function_exists('imagecreatetruecolor')) {
				$thumbnail_img = imagecreatetruecolor($thumbnail_size[0], $thumbnail_size[1]);
			} else {
				$thumbnail_img = imagecreate($thumbnail_size[0], $thumbnail_size[1]);
			}
			switch ($source_size[2]) {
				case 1:
					$source_img = imagecreatefromgif($source);
					break;
				case 2:
					$source_img = imagecreatefromjpeg($source);
					break;
				case 3:
					$source_img = imagecreatefrompng($source);
					break; 
				default:
					return false;
					break; 
			} 
			imagecopyresized($thumbnail_img, $source_img, 0, 0, 0, 0, $thumbnail_size[0], $thumbnail_size[1], $source_size[0], $source_size[1]);
			imagejpeg($thumbnail_img, $thumbnail, 100);
			imagedestroy($source_img);
			imagedestroy($thumbnail_img);
			return true;
		}else{
			return false;
		}
	}
}
?>