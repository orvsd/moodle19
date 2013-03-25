# This file contains a complete database schema for all the 
# tables used by this module, written in SQL

# It may also contain INSERT statements for particular data 
# that may be used, especially new entries in the table log_display
# AudioRecorder DB SQL, version 1.2, 2006-11-26

CREATE TABLE `prefix_audiorecorder` (
  id SERIAL PRIMARY KEY,
  `course` integer NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `intro` text NOT NULL default '',
  `audiorecordertype` varchar(50) NOT NULL default 'upload',
  `var1` integer default '0',
  `var2` integer default '0',
  `var3` integer default '0',
  `grade` integer NOT NULL default '0',
  `resubmit` tinyint(2) NOT NULL default '0',
  `preventlate` tinyint(2) NOT NULL default '0',
  `maxbytes` integer NOT NULL default '100000',
  `timedue` integer NOT NULL default '0',
  `timeavailable` integer NOT NULL default '0',
  `timemodified` integer NOT NULL default '0'
);

CREATE INDEX prefix_audiorecorder_course_idx ON prefix_audiorecorder (course);

CREATE TABLE `prefix_audiorecorder_submissions` (
  id SERIAL PRIMARY KEY,
  `audiorecorder` integer NOT NULL default '0',
  `userid` integer NOT NULL default '0',
  `timecreated` integer NOT NULL default '0',
  `timemodified` integer NOT NULL default '0',
  `numfiles` integer NOT NULL default '0',
  `data1` text NOT NULL default '',
  `data2` text NOT NULL default '',
  `grade` int(11) NOT NULL default '0',
  `comment` text NOT NULL default '',
  `format` tinyint(4) NOT NULL default '0',
  `teacher` integer NOT NULL default '0',
  `timemarked` integer NOT NULL default '0',
  `mailed` tinyint(1) NOT NULL default '0'
);

CREATE INDEX prefix_audiorecorder_submissions_audiorecorder_idx ON prefix_audiorecorder_submissions (audiorecorder);
CREATE INDEX prefix_audiorecorder_submissions_userid_idx ON prefix_audiorecorder_submissions (userid);
CREATE INDEX prefix_audiorecorder_submissions_mailed_idx ON prefix_audiorecorder_submissions (mailed);
CREATE INDEX prefix_audiorecorder_submissions_timemarked_idx ON prefix_audiorecorder_submissions (timemarked);

INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('audiorecorder','add','audiorecorder','name');
INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('audiorecorder','update','audiorecorder','name');