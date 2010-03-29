<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_mfmysqlprofiler_log"] = Array (
	"ctrl" => $TCA["tx_mfmysqlprofiler_log"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "data_src,query_simple,query_complete,time"
	),
	"feInterface" => $TCA["tx_mfmysqlprofiler_log"]["feInterface"],
	"columns" => Array (
		"data_src" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log.data_src",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"query_simple" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log.query_simple",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"query_hash" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log.query_hash",		
			"config" => Array (
				"type" => "input"
			)
		),
		"query_complete" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log.query_complete",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"time" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mf_mysql_profiler/locallang_db.xml:tx_mfmysqlprofiler_log.time",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "data_src;;;;1-1-1, query_simple, query_complete, time")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>