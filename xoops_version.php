<?php
//modules basic
$modversion['name'] = _MI_STPG_NAME;
$modversion['version'] = 1.0;
$modversion['description'] = _MI_STPG_DESC;
$modversion['credits'] = 'atlas(ch.ch.hsu@gmail.com)';
$modversion['author'] = 'atlas(ch.ch.hsu@gmail.com)';
$modversion['license'] = 'GPL see LICENSE';
$modversion['image'] = 'images/logo.png';
$modversion['dirname'] = 'sitepages';

//database
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'] = array(
	'sitepages_sites',
	'sitepages_blocks',
	'sitepages_pages',
	'sitepages_files',
);

//admin
$modversion['hasAdmin'] = 1;

//mainmenu
$modversion['hasMain'] = 1;

//templates
$modversion['templates'] = array(
	array('file' => 'stpg_index.htm', 'description' => ''),
	array('file' => 'stpg_site.htm', 'description' => ''),
	array('file' => 'stpg_page.htm', 'description' => ''),
	array('file' => 'stpg_block.htm', 'description' => ''),
	array('file' => 'sitepages.htm', 'description' => ''),
);

//config
$modversion['config'][] = array(
	'name' => 'site_managers',
	'title' => '_MI_STPG_CFG_SITEMANAGERS',
	'description' => '_MI_STPG_CFG_SITEMANAGERS_DESC',
	'formtype' => 'group_multi',
	'valuetype' => 'array',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'filetype_ok',
	'title' => '_MI_STPG_CFG_FILETYPEOK',
	'description' => '_MI_STPG_CFG_FILETYPEOK_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => 'jpg|jpeg|png|gif|doc|xls|ppt|odt|ods|odp|mp3|wma|mpg|wmv|avi|pdf|flv'
);
$modversion['config'][] = array(
	'name' => 'view_file',
	'title' => '_MI_STPG_CFG_VIEWFILE',
	'description' => '_MI_STPG_CFG_VIEWFILE_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
?>