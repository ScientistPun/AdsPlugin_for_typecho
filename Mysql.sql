CREATE TABLE `typecho_ads` (
  `aid` int(10) unsigned NOT NULL auto_increment COMMENT 'ad表主键',
  `keyword` varchar(200) NOT NULL COMMENT 'ad关键字',
  `type` tinyint(1) default 0 COMMENT 'ad展示方式',
  `name` varchar(200) default NULL COMMENT 'links名称',
  `content` text default NULL COMMENT 'ad内容',
  `description` varchar(200) default NULL COMMENT 'ad描述',
  `width` smallint(6) default NULL COMMENT 'ad宽度',
  `height` smallint(6) default NULL COMMENT 'ad高度',
  `updatetime` int(11) default 0 COMMENT 'ad更新时间',
  `status` tinyint(1) default 1 COMMENT 'ad启用状态',
  PRIMARY KEY  (`aid`, `keyword`)
) ENGINE=MYISAM  DEFAULT CHARSET=%charset%;
