CREATE TABLE `sitepages_sites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ownr_uid` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `slogan` varchar(255) default NULL,
  `edit_uids` text,
  `xoops_page` tinyint(1) NOT NULL default '1',
  `block_location` tinyint(1) NOT NULL default '0',
  `theme` varchar(255) NOT NULL default 'default',
  PRIMARY KEY  (`id`),
  KEY `ownr_uid` (`ownr_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `sitepages_blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sid` int(10) unsigned NOT NULL default '0',
  `block_type` tinyint(1) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `content` text,
  `odr` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `sitepages_pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sid` int(10) unsigned NOT NULL default '0',
  `pid` int(10) unsigned NOT NULL default '0',
  `page_type` tinyint(1) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `content` text,
  `header` tinyint(1) NOT NULL default '1',
  `navigation` tinyint(1) NOT NULL default '1',
  `router` tinyint(1) NOT NULL default '1',
  `blocks` text,
  PRIMARY KEY  (`id`),
  KEY `sid` (`sid`,`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `sitepages_files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_type` varchar(255) default NULL,
  `file_size` varchar(20) default NULL,
  `description` varchar(255) NOT NULL default '',
  `real_name` varchar(60) NOT NULL default '',
  `counter` int(10) unsigned default '0',
  `date_time` varchar(20) default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
