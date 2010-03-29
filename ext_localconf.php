<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$conf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mf_mysql_profiler']);

	// load hook only if profiling is enabled
if (TYPO3_MODE == 'FE')	{
	if ( $conf['enableFEprofiling'] > 0 ){
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_db.php'] = t3lib_extMgm::extPath('mf_mysql_profiler').'class.ux_t3lib_db.php';
	}
}

if ( $conf['enableBEprofiling']>0 && TYPO3_MODE == 'BE'){
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_db.php'] = t3lib_extMgm::extPath('mf_mysql_profiler').'class.ux_t3lib_db.php';
}


?>
