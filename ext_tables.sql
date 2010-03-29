#
# Table structure for table 'tx_mfmysqlprofiler_log'
#
CREATE TABLE tx_mfmysqlprofiler_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	data_src tinytext NOT NULL,
	query_simple text NOT NULL,
	query_hash text NOT NULL,
	query_complete text NOT NULL,
	time int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);