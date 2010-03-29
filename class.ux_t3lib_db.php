<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


class ux_t3lib_DB extends t3lib_DB {
	
	var $ux_t3lib_DB_profiling_time = 0;
	var $ux_t3lib_DB_profiling_mask = false;
	var $last_insert_id;

	function ux_t3lib_DB(){

		$conf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mf_mysql_profiler']);
			// load profiling time
		$this->ux_t3lib_DB_profiling_time = ((int)$conf['profilingTime'])/1000;
			// load profiling mask
		$profilingMask = str_replace( ' ' , '' , $conf['profilingMask'] );
			// split profiling mask
		if ( $profilingMask ){
			$this->ux_t3lib_DB_profiling_mask = explode( ',' ,$profilingMask );
		} else {
			$this->ux_t3lib_DB_profiling_mask = false;
		}
	}

	function exec_UPDATEquery($table,$where,$fields_values,$no_quote_fields=FALSE)  {
		$ts = microtime();
		$query = $this->UPDATEquery($table,$where,$fields_values,$no_quote_fields);
		$res = mysql_query($query, $this->link);
		if ($this->debugOutput) $this->debug('exec_UPDATEquery');
		$this->ux_t3lib_DB_profile($table, $query, $ts );
		return $res;
	}
 
	function exec_DELETEquery($table,$where)        {
		$ts = microtime();
		$query = $this->DELETEquery($table,$where);
		$res = mysql_query($query, $this->link);
		if ($this->debugOutput) $this->debug('exec_DELETEquery');
		$this->ux_t3lib_DB_profile($table, $query, $ts );
		return $res;
	}

	function exec_INSERTquery($table,$fields_values,$no_quote_fields=FALSE) {
		$ts = microtime();
		$query =  $this->INSERTquery($table,$fields_values,$no_quote_fields);
		$res = mysql_query($query , $this->link);
		$this->last_insert_id = parent::sql_insert_id();
		$this->ux_t3lib_DB_profile($table, $query, $ts );
		if ($this->debugOutput) $this->debug('exec_SELECTquery');
		return $res;
	}

	function exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')   {
		$ts = microtime();
		$query =  $this->SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit);
		$res = mysql_query($query , $this->link);
		$this->ux_t3lib_DB_profile($from_table, $query, $ts );
		if ($this->debugOutput) $this->debug('exec_SELECTquery');
		return $res;
	}
	
	function ux_t3lib_DB_simplify ($src){
		
			// define patterns
		$str_pattern = "/(?<!\\\\)'([^'\\\\]*(\\\\.[^'\\\\]*)*)'/";
		$num_pattern = "/(?<=[=<>\\s\\(]{1})[0-9.]+(?=([\\s\\)]{1}|\\Z))/";
		 
			// replace all string sources with predefined string
		$src = preg_replace( $str_pattern , "'string'" , $src);
			// replace all numbers 
		$src = preg_replace( $num_pattern , "123" , $src);
		return $src;
	}

	function sql_insert_id(){
		return $this->last_insert_id;
	}

	function ux_t3lib_DB_profile ($table, $query, $time){

			// check profiling mask	
		if ( $this->ux_t3lib_DB_profiling_mask && count( $this->ux_t3lib_DB_profiling_mask  ) > 0 ){
			$enable_profiling = false;
			foreach($this->ux_t3lib_DB_profiling_mask as $tableMaskName){
				if ( strpos( $table, $tableMaskName ) === 0 ){
					$enable_profiling = true;
				}
			}
		} else {
			$enable_profiling = true;
		}

		if (!$enable_profiling) return;
		
			// check profiling time			
		$query_time = microtime() - $time;	
		if ($this->ux_t3lib_DB_profiling_time >= $query_time) return;

		
		
			// simplify src and query for analysis
		$simple_table = $this->ux_t3lib_DB_simplify($table);
		$simple_query = $this->ux_t3lib_DB_simplify($query);
	
			// save log record without profiling
		parent::exec_INSERTquery(
			'tx_mfmysqlprofiler_log',
			array(
				'tstamp' => time(),
				'data_src' => $simple_table,
				'query_simple' => $simple_query,
				'query_hash' =>md5($simple_query),
				'query_complete' => $query,
				'time' => ($query_time * 1000)
			)
		);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mf_mysql_profiler/class.ux_t3lib_db.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mf_mysql_profiler/class.ux_t3lib_db.php']);
}
?>
