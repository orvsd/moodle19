# This file contains a complete database schema for all the 
# tables used by this module, written in SQL

# It may also contain INSERT statements for particular data 
# that may be used, especially new entries in the table log_display
# AudioRecorder DB SQL, version 1.2, 2006-11-26

# DROP TABLE IF EXISTS `prefix_audiorecorder`;
CREATE TABLE `prefix_audiorecorder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `course` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `intro` text NOT NULL,
  `audiorecordertype` varchar(50) NOT NULL default 'upload',
  `var1` bigint(10) default '0',
  `var2` bigint(10) default '0',
  `var3` bigint(10) default '0',
  `grade` int(10) NOT NULL default '0',
  `resubmit` tinyint(2) unsigned NOT NULL default '0',
  `preventlate` tinyint(2) unsigned NOT NULL default '0',
  `maxbytes` int(10) unsigned NOT NULL default '100000',
  `timedue` int(10) unsigned NOT NULL default '0',
  `timeavailable` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Info about Audio Recorder.';

# DROP TABLE IF EXISTS `prefix_audiorecorder_submissions`;
CREATE TABLE `prefix_audiorecorder_submissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `audiorecorder` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `timecreated` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  `numfiles` int(10) unsigned NOT NULL default '0',
  `data1` text NOT NULL,
  `data2` text NOT NULL,
  `grade` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  `format` tinyint(4) unsigned NOT NULL default '0',
  `teacher` int(10) unsigned NOT NULL default '0',
  `timemarked` int(10) unsigned NOT NULL default '0',
  `mailed` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `audiorecorder` (`audiorecorder`),
  KEY `userid` (`userid`),
  KEY `mailed` (`mailed`),
  KEY `timemarked` (`timemarked`)
) COMMENT='Info about submitted audio files';

INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('audiorecorder','add','audiorecorder','name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('audiorecorder','update','audiorecorder','name');