<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("tools","txmfmysqlprofilerM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

$TCA["tx_mfmysqlprofiler_log"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_mfmysqlprofiler_log.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "data_src, query_simple, query_complete, time",
	)
);
?>