<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Martin Ficzel <martin.ficzel@gmx.de>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'DB Profiling' for the 'mf_mysql_profiler' extension.
 *
 * @author	Martin Ficzel <martin.ficzel@gmx.de>
 */

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:mf_mysql_profiler/mod1/locallang.xml");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_mfmysqlprofiler_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $settings;
	var $profilingMask;
	
	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		$this->settings = t3lib_div::_GP('SET');
		
		$conf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mf_mysql_profiler']);

			// load profiling mask
		$profilingMask = str_replace(' ','' , $conf['profilingMask'] );

		if ($profilingMask){
			$this->profilingMask = explode(',' , $profilingMask);
		} else {
			$this->profilingMask = false;
		}

	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"overview" => $LANG->getLL("overview"),
				"source"   => $LANG->getLL("source"),
				"query"    => $LANG->getLL("query"),
				"manage"   => $LANG->getLL("manage"),
				"clear"    => $LANG->getLL("clear")
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if ( $BE_USER->user["admin"] )	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br />".$LANG->sL("LLL:EXT:lang/locallang_core.xml:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		
			// enable default if needed
		if (!$this->settings['table'] and in_array( $this->settings["function"],array('source','query') ) ){
			$this->settings["function"] ='overview';
		}
			// build headline
		if ( $this->settings["function"] != 'manage' ){
			$content = '';
			$content.= $this->link_module('Overview', 'overview').'&nbsp;&gt;&nbsp;';
			if ($this->settings['table']) $content.= $this->link_module('Table:'.$this->settings['table'] , 'source').'&nbsp;&gt;&nbsp;';
			if ($this->settings['query']) $content.= $this->link_module('Query:'.$this->settings['query'] , 'query');
			$this->content.=$this->doc->section("Functions:",$content,0,1);
		}
		
		switch( $this->settings["function"] ) {
			default:
			case "overview":
					// list table infos
				$data = $this->get_source_info();
				$content = $this->print_data($data);
					
				$this->content.=$this->doc->section("Tables:",$content,0,1);
			break;
			
			case "source":
				if ($this->settings['table']){
						// list querys and stats
					$data = $this->get_table_info($this->settings['table']);
					$content =  $this->print_data($data);
					$this->content.=$this->doc->section("Querys:",$content,0,1);	
						// list indices
					$data = $this->get_index_info($this->settings['table']);
					$content =  $this->print_data($data);
					$this->content.=$this->doc->section('Indices for ' . $this->settings['table'] . ':',$content,0,1);
				} else {
					$content .= 'you have to select a table';
					$this->content.=$this->doc->section("Message #2:",$content,0,1);
				}
			break;
			
			case "query":
				if ($this->settings['query']){
						// query stats
					$queryData = $this->get_query_info($this->settings['query']);
					if ($queryData){
						
							// query
						$this->content.=$this->doc->section("Simple Query:",$queryData['query_simple'],0,1);
						$this->content.=$this->doc->section("Query:",$queryData['query_complete'],0,1);
						
							// query stats
						$content = $this->print_data(array($queryData),array('query_simple','query_complete'));
						$this->content.=$this->doc->section("Query Stats:",$content,0,1);
						
							// explain
						$dataExplain = $this->get_query_explain($queryData['query_simple']);
						$content = $this->print_data($dataExplain);
						$this->content.=$this->doc->section("Explain Query:",$content,0,1);
						
							// list indices
						$dataIndices = $this->get_index_info($queryData['data_src']);
						$content = $this->print_data($dataIndices);
						$this->content.=$this->doc->section('Indices for '.$queryData['data_src'].':',$content,0,1);	
					}	
				} else {
					$content="you have to select table and query";
					$this->content.=$this->doc->section("Message #3:",$content,0,1);
				}
			break;
			
			case "clear":
				
				$content = '<a href="index.php?&SET[function]=clear&SET[exec]=clear" >Clear</a>';
				$this->content.=$this->doc->section("Clear Log-Items:",$content,0,1);
				
				if ( $this->settings['exec'] == "clear"){
					$res = $this->clearQueryLog();
					$this->content.=$this->doc->section("Result:",$res,0,1);
				}
				
				break;
		}
	}
	
	function clearQueryLog(){
		$content = 'Empty table tx_mfmysqlprofiler_log: ';
		$success = $GLOBALS['TYPO3_DB']->sql_query('TRUNCATE TABLE `tx_mfmysqlprofiler_log`;');
		if ($success) {
			$content.= 'Success!';
		} else {
			$content.= 'FAIL!!!';
		}
		return $content;
	}
	
	/*
	 * 
	 */
	 
	 function get_source_info (){
		 
	 		// get list of sources
		$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT data_src FROM `tx_mfmysqlprofiler_log` GROUP by data_src');
		$tables = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res) ){
			$tables[] = $row[0];
		}
			// compile informations
		$data = array();

		foreach ($tables as $table){
			$table_info = array('tablename' => $table);

				// show only tables from profiling mask
			$enable_profiling = false;
			if (  $this->profilingMask && count( $this->profilingMask ) > 0 ){
				foreach($this->profilingMask as $profileTableName){
					if (strpos($table, $profileTableName) === 0){
						$enable_profiling = true;
					}
				}
			} else {
				$enable_profiling = true;
			}

			if ($enable_profiling){
					// add statistic infos
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*) AS num_querys, AVG(time) as avg_time, MIN(time) AS min_time, MAX(time) AS max_time, SUM(time) as time_sum' ,'tx_mfmysqlprofiler_log', 'data_src = "'.$table.'"' ,'' );
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$table_info['num_querys']    = $row['num_querys'];
					$table_info['avg_time']      = $row['avg_time'];
					$table_info['sum_time']      = $row['time_sum'];
					$table_info['min_time']      = $row['min_time'];
					$table_info['max_time']      = $row['max_time'];
				} else {
					$table_info['num_querys']    = '';
					$table_info['avg_time']      = '';
					$table_info['sum_time']      = '';
					$table_info['min_time']      = '';
					$table_info['max_time']      = '';
				}
					// add link
				$table_info['link'] = '<a href="index.php?&SET[function]=source&SET[table]='.$table.'" >details</a>';


				$data[] = $table_info;
			}
		}
		return $data;
	 }
	 
		// where | query | num_calls | avg_time | min_time | max_time | combined_time | show details 
	 function get_table_info ($table){
	 	$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*) AS num_querys, AVG(time) as avg_time, MIN(time) AS min_time, MAX(time) AS max_time, SUM(time) as time_sum, query_simple, query_hash' ,'tx_mfmysqlprofiler_log', 'data_src = "'.$table.'"' ,'query_hash');
	 	$querys = array();
	 	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ){
			
			 	// add link
			 $row['link'] = '<a href="index.php?&SET[function]=query&SET[table]='.$table.'&SET[query]='.$row['query_hash'].'" >details</a>';
			 unset ($row['query_hash']);
			 
			 $querys[] = $row;
		}
		return $querys;
	 }
	 
	 	// list all indices of a given query
	 function get_index_info ($table){
		$tables = explode(',',$table);
		$indices = array();
		
		foreach ($tables as $singleTable){
			$tableParts = explode (' ', trim($singleTable));
			$tableName = $tableParts[0];
			$res  = $GLOBALS['TYPO3_DB']->sql_query('SHOW INDEX FROM `'.$tableName.'`');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ){
				$indices[] = $row;
			}
		}
		
		return $indices;
	 }
	 
	 function get_query_info ($query) {
	 	$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*) AS num_querys, AVG(time) as avg_time, MIN(time) AS min_time, MAX(time) AS max_time, SUM(time) as time_sum, data_src, query_simple, query_complete ' ,'tx_mfmysqlprofiler_log', 'query_hash = \''.$query.'\'' ,'query_hash');
	 	$info = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	 	return $info;
	 }
	 
	 function get_query_explain ($query) {
  		$res  = $GLOBALS['TYPO3_DB']->sql_query('EXPLAIN '.$query );
		$info = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ){
			$info[]=$row;
		};
  		return $info;
	 }
	 
	 function print_data ($data, $hide_rows = array() ){
	 	$res = '';
	 	if (is_array($data)) {
	 		if ( !is_array($data[0])){
	 			 $data[0] = array($data);
	 		}
	 		$res .= '<table class="typo3-dblist"><tr>';
	 			// write header
	 		foreach (array_keys($data[0])  as $key){
	 			if (!in_array($key, $hide_rows )){
	 				$res .= '<td class="c-headLine"><strong>'.$key.'</strong></td>';
	 			}
	 		}
	 		$res .= '</tr>';
	 			// write content
	 		$line = 0;	
	 		foreach ($data as $row){
	 			$res .= '<tr>';
	 			$col = 0;	
	 			foreach ($row as $key=>$value){
	 				if (!in_array($key, $hide_rows)){
	 					/*
	 					if (strlen($stripped) > 30){
	 						$res .= '<td><span title="'.htmlentities($stripped).'" >'.substr($value,0,30).'</span></td>';
	 					} else {
		 					$res .= '<td><span title="'.htmlentities($stripped).'" >'.$value.'</span></td>';
	 					}*/
	 					
	 					// calc style
	 					if ($line%2){
	 						if ($col%2){
	 							$style = 'background-color:#FFFFFF;';
	 						} else {
	 							$style = 'background-color:#FAFAFA;';
	 						}	
	 					} else {
	  						if ($col%2){
	  							$style = 'background-color:#EEEEEE;';
	 						} else {
	 							$style = 'background-color:#EAEAEA;';
	 						}	
	 					}
	 						// write row
	 					$stripped = strip_tags($value);	
	 					if (strpos($value,'</a>') === false){
	 						$value = substr(strip_tags($value),0,150);
	 					}
	 					$res .= '<td style="'.$style.'" ><span title="'.htmlentities($stripped).'" >'.$value.'</span></td>';	 				
	 				}
	 				$col ++;
	 			}
				$res .= '<tr>';	
				$line ++;
	 		}
	 		$res .= '</table>';
	 	}
	 	return $res;
	 }
	 	 
	 function print_info(){
	 	
	 }
	 
	 function link_module($text, $module , $settings=false){
	 		// fallback to predefined vars 
	 	if (!$settings) {
	 		$settings = $this->settings;
	 	}
	 		// set module code
	 	$settings['function'] = $module;
	 		// create params
	 	$params = array();
	 	foreach ($settings as $key=>$value){
	 		$params[]='SET['.$key.']='.htmlspecialchars($value);
	 	}
	 		// build result
	 	$result = '<a href="index.php?'.implode('&',$params).'">'.$text.'</a>';
	  
		return $result;	
	 }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mf_mysql_profiler/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mf_mysql_profiler/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_mfmysqlprofiler_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>