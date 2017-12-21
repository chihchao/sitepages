<?php
class SPUser {
	var $uid;
	var $groups;
	function SPUser() {
		global $xoopsUser;
		$this -> uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
		$this -> groups = is_object($xoopsUser) ? $xoopsUser -> getGroups() : array(XOOPS_GROUP_ANONYMOUS);
	}
	function checkAuthority($type = 'admin', $uids = 0) {
		global $xoopsUser, $xoopsModuleConfig;
		//if admin, return true
		if (is_object($xoopsUser) && $xoopsUser -> isAdmin()) return true;
		$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
		$groups = is_object($xoopsUser) ? $xoopsUser -> getGroups() : array(XOOPS_GROUP_ANONYMOUS);
		if ($type == 'site_managers') foreach ($groups as $val) if (in_array($val, $xoopsModuleConfig['site_managers'])) return true;
		if ($type == 'site_ownr') if ($uid == $uids) return true;
		if ($type == 'site_edit') if ($uids['ownr_uid'] == $uid || in_array($uid, $uids['edit_uids'])) return true;
		return false;
	}
	function listSiteManagers() {
		global $xoopsDB, $xoopsModuleConfig;
		if (empty($xoopsModuleConfig['site_managers'])) return array();
		$recordset = array();
		$sql = implode('\' Or ' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'', $xoopsModuleConfig['site_managers']);
	$sql = 'Select ' . $xoopsDB -> prefix('users') . '.uid, ' . $xoopsDB -> prefix('users') . '.uname, ' . $xoopsDB -> prefix('users') . '.name From ' . $xoopsDB -> prefix('groups_users_link') . ' Left Join ' . $xoopsDB -> prefix('users') . ' On ' . $xoopsDB -> prefix('groups_users_link') . '.uid = ' . $xoopsDB -> prefix('users') . '.uid Where ' . $xoopsDB -> prefix('users') . '.level > 0 And (' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'' . $sql . '\') Group By ' . $xoopsDB -> prefix('groups_users_link') . '.uid Order by ' . $xoopsDB -> prefix('users') . '.uname';
		if (!$result = $xoopsDB -> query($sql)) return false;
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['uid']] = $record;
		return $recordset;
	}
}
?>