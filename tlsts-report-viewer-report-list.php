<?php

// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com, John Bieling and John P. New
// with additional extensions (sort order) of Klaus Tachtler.
//
// Available at:
// https://github.com/techsneeze/dmarcts-report-viewer
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
//####################################################################
//### configuration ##################################################
//####################################################################

// Copy dmarcts-report-viewer-config.php.sample to
// dmarcts-report-viewer-config.php and edit with the appropriate info
// for your database authentication and location.
//
// Edit the configuration variables in dmarcts-report-viewer.js with your preferences.
//
//
//####################################################################
//### functions ######################################################
//####################################################################

function tmpl_reportList($reports, $sort) {

	global $options;
	global $cookie_options;

    $reportlist[] = "";

	if (sizeof($reports) == 0) {
		$reportlist[] = "<div class='center'><b>No Reports Match this filter</b><br />Click the <i>Reset</i> button or choose a different value for <i>DMARC Result</i>, <i>Month</i>, <i>Domain(s)</i> or <i>Reporter(s)</i>.</div>";
	} else {
		$title_message_th = "Click to toggle sort direction by this column.";
		$title_message_tr = "Click to view detailed report data.";

		//	Resizer handles
		//	--------------------------------------------------------------------------
		$reportlist[] = "<div id='resizer_horizontal' class='resizer resizer_horizontal'></div>";
		$reportlist[] = "<div id='resizer_vertical' class='resizer resizer_vertical'></div>";
		$reportlist[] = "<table id='reportlistTbl' class='reportlist'>";
		$reportlist[] = "  <thead>";
		$reportlist[] = "    <tr>";

		$triangle = ($cookie_options['sort'] ? "asc":"desc") . "_triangle ";
		$reportlist[] = "      <th class='circle_container' style='padding-left: 5px' title='TLS-RPT Result. " . $title_message_th . "'><div class='circle_whole circle_black'></div><span style='display:none;'>1</span></span></th>";
		$reportlist[] = "      <th id='mindate' class=\"" . ($cookie_options['sort_column'] == 'mindate' ? $triangle : "") . "\" title='" . $title_message_th . "'>Start Date</th>";
		$reportlist[] = "      <th id='maxdate' class=\"" . ($cookie_options['sort_column'] == 'maxdate' ? $triangle : "") . "\" title='" . $title_message_th . "'>End Date</th>";
		$reportlist[] = "      <th id='domain' class=\"" . ($cookie_options['sort_column'] == 'domain' ? $triangle : "") . "\" title='" . $title_message_th . "'>Domain</th>";
		$reportlist[] = "      <th id='org' class=\"" . ($cookie_options['sort_column'] == 'org' ? $triangle : "") . "\" title='" . $title_message_th . "'>Reporting Organization</th>";
		$reportlist[] = "      <th id='reportid' class=\"" . ($cookie_options['sort_column'] == 'reportid' ? $triangle : "") . "\" title='" . $title_message_th . " (currently doesn&#39;t really sort well)'>Report ID</th>";
		$reportlist[] = "      <th id='rcount' class=\"" . ($cookie_options['sort_column'] == 'rcount' ? $triangle : "") . "\" title='" . $title_message_th . "'>Success</th>";
		$reportlist[] = "      <th id='rcount' class=\"" . ($cookie_options['sort_column'] == 'rcount' ? $triangle : "") . "\" title='" . $title_message_th . "'>Fail</th>";
		$reportlist[] = "    </tr>";
		$reportlist[] = "  </thead>";

		$reportlist[] = "  <tbody>";
		$report_success_sum    = 0;
		$report_fail_sum    = 0;

		foreach ($reports as $row) {
			$row = array_map('htmlspecialchars', $row);
			$reportlist[] =  "    <tr class='linkable' onclick=\"showReport('" . $row['serial'] . "')\" id='report" . $row['serial'] . "' title='" . $title_message_tr . "'>";
			$reportlist[] =  "      <td class='circle_container' style='padding-left: 5px;''><span class='status_sort_key'>" . get_tls_result($row)['status_sort_key'] . "</span><div title='TLS: " . get_tls_result($row)['result'] . "\n" . $title_message_tr . "'>
			<div class='circle_whole circle_" . get_tls_result($row)['color'] . "'></div></div></td>"; // Col 0
			$reportlist[] =  "      <td class='right'>". format_date($row['mindate'], $cookie_options['date_format']). "</td>";   // Col 1
			$reportlist[] =  "      <td class='right'>". format_date($row['maxdate'], $cookie_options['date_format']). "</td>";   // Col 3
			$reportlist[] =  "      <td class='center'>". $row['domain']. "</td>";                                     // Col 5
			$reportlist[] =  "      <td class='center'>". $row['org']. "</td>";                                        // Col 6
			$reportlist[] =  "      <td class='center'>". $row['reportid'] . "</td>";
			$reportlist[] =  "      <td class='right'>". number_format($row['summary_successful']+0,0). "</td>";                  // Col 9
			$reportlist[] =  "      <td class='right'>". number_format($row['summary_failure']+0,0). "</td>";                  // Col 9
			$reportlist[] =  "    </tr>";
			$report_success_sum += $row['summary_successful'];
			$report_fail_sum += $row['summary_failure'];
		}

		$reportlist[] =  "  </tbody>";
		$reportlist[] = "<tr class='sum'><td class='circle_container'></td><td class='circle_container'></td><td class='circle_container'></td><td></td><td></td><td class='right'>Sum:</td><td class='right'>".number_format($report_success_sum,0)."</td><td class='right'>".number_format($report_fail_sum,0)."</td></tr>";
		$reportlist[] =  "</table>";

		$reportlist[] = "<!-- End of report list -->";
		$reportlist[] = "";
	}
    #indent generated html by 2 extra spaces
    return implode("\n  ",$reportlist);
}

//####################################################################
//### main ###########################################################
//####################################################################

// These files are expected to be in the same folder as this script, and must exist.
include "dmarcts-report-viewer-config.php";
include "dmarcts-report-viewer-common.php";

// Get all configuration options
// --------------------------------------------------------------------------
configure();

$dom_select= '';
$org_select= '';
$per_select= '';
// $dmarc_select= '';
$report_status = '';
$where = '';

// Parameters of GET
// --------------------------------------------------------------------------

if(isset($_GET['sortorder']) && is_numeric($_GET['sortorder'])){
	$sortorder=$_GET['sortorder']+0;
}elseif(!isset($_GET['sortorder'])){
	$sortorder= isset( $default_sort ) ? $default_sort : 1;
}else{
	die('Invalid sortorder flag');
}

if(isset($_GET['d'])){
	$dom_select=$_GET['d'];
}else{
	$dom_select= '';
}

if( $dom_select == "all" ) {
	$dom_select= '';
}

if(isset($_GET['o'])){
	$org_select=$_GET['o'];
}else{
	$org_select= '';
}

if( $org_select == "all" ) {
	$org_select= '';
}

if(isset($_GET['p'])){
	$per_select=$_GET['p'];
}else{
	$per_select= date( 'Y-m' );
}

if( $per_select == "all" ) {
	$per_select= '';
}

// if(isset($_GET['dmarc'])){
// 	$dmarc_select=$_GET['dmarc'];
// }else{
// 	$dmarc_select= '';
// }
//
if(isset($_GET['rptstat'])){
	$report_status = $_GET['rptstat'];
}else{
	$report_status = '';
}

// Debug
// echo "<br />D=$dom_select <br /> O=$org_select <br />";
// echo "<br />DMARC=$dmarc_select<br />";

// Make a DB Connection
// --------------------------------------------------------------------------
$dbh = connect_db($dbtype, $dbhost, $dbport, $dbname, $dbuser, $dbpass);

// Get allowed reports and cache them - using serial as key
// --------------------------------------------------------------------------
$reports = array();

// set sort direction
// --------------------------------------------------------------------------
$sort = '';
if( $sortorder ) {
  $sort = "ASC";
} else {
  $sort = "DESC";
}

// Build SQL WHERE clause

// DMARC Result
// --------------------------------------------------------------------------
// switch ($dmarc_select) {
// 	case "all": // Everything
// 		break;
// 	case "DMARC_FAIL": // DMARC Fail
// 		$where .= ( $where <> '' ? " AND" : " WHERE" ) . " dmarc_result_min = 0 AND dmarc_result_max = 0";
// 		break;
// 	case "DMARC_PASS_AND_FAIL": // DMARC Pass and Fail
// 		$where .= ( $where <> '' ? " AND" : " WHERE" ) . " dmarc_result_min = 0 AND (dmarc_result_max = 1 OR dmarc_result_max = 2)";
// 		break;
// 	case "DMARC_OTHER_CONDITION": // Other condition: Yellow
// 		$where .= ( $where <> '' ? " AND" : " WHERE" ) . " dmarc_result_min >= 3 AND dmarc_result_max >= 3";
// 		break;
// 	case "DMARC_PASS": // DMARC Pass
// 		$where .= ( $where <> '' ? " AND" : " WHERE" ) . " (dmarc_result_min = 1 OR dmarc_result_min = 2) AND (dmarc_result_max <= 2)";
// 		break;
// 	default:
// 		break;
// }

// Report Status
// --------------------------------------------------------------------------
if ( $report_status != "all" && $report_status != "" ) {
	$where .= ( $where <> '' ? " AND" : " WHERE" ) . " " . $tls_result[$report_status]['status_sql_where'];
}

// Domains
// --------------------------------------------------------------------------
if( $dom_select <> '' ) {
	$where .= ( $where <> '' ? " AND" : " WHERE" ) . " domain=" . $dbh->quote($dom_select);
}

// Organisations
// --------------------------------------------------------------------------
if( $org_select <> '' ) {
	$where .= ( $where <> '' ? " AND" : " WHERE" ) . " org=" . $dbh->quote($org_select);
}

// Periods
// --------------------------------------------------------------------------
if( $per_select <> '' ) {
	$ye = substr( $per_select, 0, 4) + 0;
	$mo = substr( $per_select, 5, 2) + 0;
	$where .= ( $where <> '' ? " AND" : " WHERE" )
			. " ((extract(year from mindate) = $ye AND extract(month from mindate) = $mo) "
			. "  OR (extract(year from maxdate) = $ye AND extract(month from maxdate) = $mo)) ";
}

// Include the rcount via left join, so we do not have to make an sql query
// for every single report.
// --------------------------------------------------------------------------

$sql = "
SELECT
  *
FROM
  tls_report
$where
ORDER BY
    " . $cookie_options['sort_column'] . ( $cookie_options['sort'] ? " ASC" : " DESC" )
;

// Debug
// echo "<br />sql where = $where<br />";
// echo "<br /><b>Data List sql:</b>  $sql<br />";
// echo "<br />per_select = " . urlencode($per_select) . "<br />";

$query = $dbh->query($sql);
foreach($query as $row) {
    if (true) {
        //add data by serial
        $reports[$row['serial']] = $row;
    }
}

// Generate Report List
// --------------------------------------------------------------------------
echo tmpl_reportList($reports, $sort);

?>
