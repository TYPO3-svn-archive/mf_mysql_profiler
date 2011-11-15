<?php

########################################################################
# Extension Manager/Repository config file for ext: "mf_mysql_profiler"
#
# Auto generated 11-01-2008 20:50
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MySql Database Profiler',
	'description' => 'Database Profiling Module for Admins to the Backend',
	'category' => 'module',
	'author' => 'Martin Ficzel',
	'author_email' => 'martin.ficzel@gmx.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'TYPO3_version' => '4.5.0-4.9.99',
	'author_company' => '',
	'version' => '0.0.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-4.9.99',
		),
		'conflicts' => array(
			'dbal'
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:16:{s:9:"ChangeLog";s:4:"4943";s:10:"README.txt";s:4:"9fa9";s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"f92c";s:14:"ext_tables.sql";s:4:"f4d4";s:31:"icon_tx_mfmysqlprofiler_log.gif";s:4:"475a";s:16:"locallang_db.xml";s:4:"940b";s:7:"tca.php";s:4:"a6c2";s:19:"doc/wizard_form.dat";s:4:"56d5";s:20:"doc/wizard_form.html";s:4:"479a";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"21f2";s:14:"mod1/index.php";s:4:"7a26";s:18:"mod1/locallang.xml";s:4:"97e7";s:22:"mod1/locallang_mod.xml";s:4:"c359";s:19:"mod1/moduleicon.gif";s:4:"8074";}',
);

?>