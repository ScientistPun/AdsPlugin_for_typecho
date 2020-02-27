CREATE TABLE `typecho_ads` (
  `aid` INTEGER NOT NULL PRIMARY KEY,
  `keyword` varchar(200) NOT NULL PRIMARY KEY,
  `type` smallint(1) default '0',
  `name` varchar(200) default NULL,
  `content` varchar(1000) default NULL,
  `description` varchar(200) default NULL,
  `width` smallint(6) default 0,
  `height` smallint(6) default 0,
  `updatetime` INTEGER default 0,
  `status` smallint(1) default '0'
);