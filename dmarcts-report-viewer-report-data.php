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

function tmpl_reportData($reportnumber, $reports, $host_lookup = 1) {

	$title_message = "Click to toggle sort direction by this column";
	
	if (!$reportnumber) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<script type=\"text/javascript\">sorttable();</script>";
    
//     $reportdata[] = "<!-- Start of report data -->";
	$reportsum    = 0;

	if (isset($reports[$reportnumber])) {
		$row = $reports[$reportnumber];
		$row = array_map('htmlspecialchars', $row);
//         $reportdata[] = "<a id='rpt".$reportnumber."'></a>";

		$reportdata[] = "<div id='report_desc_container' class='center reportdesc_container'>";
		$reportdata[] = "<div id='report_desc' class='center reportdesc'>Report from ".$row['org']." for ".$row['domain']."<br>(". format_date($row['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</div>";

		$reportdata[] = "<div style='display:inline-block;margin-left:20px;'><img src='xml.png' id='xml_html_img' title='Show Raw Report XML' onclick='showXML()'></div>";

		$reportdata[] = "</div>";

	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<div id='report_data_xml' style='display:none; float:right; overflow-y:auto; resize: vertical; text-align:left;'>";
	$reportdata[] =  "<pre lang=\"xml\">" . $row['raw_xml'] . "</pre>";
	$reportdata[] = "</div>";

	$reportdata[] = "<div id='report_data_table_div' style='overflow-y:auto; resize: both;'>";
	$reportdata[] = "<table id='report_data_table' class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th class=\"asc_triangle\" title='" . $title_message . "'>IP</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Host<br />Name</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Message<br />Count</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Disposition</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Reason</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Result</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Result</th>";
// 	$reportdata[] = "      <th><img src='xml.png' id='xml_html_img' title='Show Raw Report XML' onclick='showXML()' style='float:left;'></th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";

	$reportdata[] = "  <tbody>";

	global $mysqli;
	$sql = "SELECT * FROM rptrecord where serial = $reportnumber ORDER BY ip ASC";
// Debug
// echo "<br><b>sql reportdata =</b> $sql<br>";

	$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
	while($row = $query->fetch_assoc()) {
		$status = get_status_color($row);

		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		} elseif ( $row['ip6'] ) {
			$ip = inet_ntop($row['ip6']);
		} else {
			$ip = "-";
		}

		/* escape html characters after exploring binary values, which will be messed up */
		$row = array_map('htmlspecialchars', $row);

		$reportdata[] = "    <tr class='".get_status_color($row)[0]."'>";
		$reportdata[] = "      <td>". $ip. "</td>";
		if ( $host_lookup ) {
			$reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
		} else {
			$reportdata[] = "      <td>#off#</td>";
		}
		$reportdata[] = "      <td>". $row['rcount']. "</td>";
		$reportdata[] = "      <td>". $row['disposition']. "</td>";
		$reportdata[] = "      <td>". $row['reason']. "</td>";
		$reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
		$reportdata[] = "      <td>". $row['dkimresult']. "</td>";
		$reportdata[] = "      <td>". $row['spfdomain']. "</td>";
		$reportdata[] = "      <td>". $row['spfresult']. "</td>";
		$reportdata[] = "    </tr>";

		$reportsum += $row['rcount'];
	}
	$reportdata[] = "  </tbody>";
	$reportdata[] = "<tr><td></td><td class='right'>Sum:</td><td>$reportsum</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
	$reportdata[] = "</table>";

	$reportdata[] = "</div>"; 


	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

//####################################################################
//### main ###########################################################
//####################################################################

// These files are expected to be in the same folder as this script, and must exist.
include "dmarcts-report-viewer-config.php";
include "dmarcts-report-viewer-common.php";

// Parameters of GET
// --------------------------------------------------------------------------

if(isset($_GET['report']) && is_numeric($_GET['report'])){
	$reportid=$_GET['report']+0;
}elseif(!isset($_GET['report'])){
	$reportid=false;
}else{
	die('Invalid Report ID');
}

if(isset($_GET['hostlookup']) && is_numeric($_GET['hostlookup'])){
	$hostlookup=$_GET['hostlookup']+0;
}elseif(!isset($_GET['hostlookup'])){
	$hostlookup= isset( $default_lookup ) ? $default_lookup : 1;
}else{
	die('Invalid hostlookup flag');
}

if(isset($_GET['sortorder']) && is_numeric($_GET['sortorder'])){
	$sortorder=$_GET['sortorder']+0;
}elseif(!isset($_GET['sortorder'])){
	$sortorder= isset( $default_sort ) ? $default_sort : 1;
}else{
	die('Invalid sortorder flag');
}

// Debug
//echo "<br />D=$dom_select <br /> O=$org_select <br />";

// Make a MySQL Connection using mysqli
// --------------------------------------------------------------------------
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection, here is why: \n";
	echo "Errno: " . $mysqli->connect_errno . "\n";
	echo "Error: " . $mysqli->connect_error . "\n";
// Debug ONLY. This will expose database credentials when database connection fails
// 	echo "Database connection information: <br />dbhost: " . $dbhost . "<br />dbuser: " . $dbuser . "<br />dbpass: " . $dbpass . "<br />dbname: " . $dbname . "<br />dbport: " . $dbport . "<br />";
	exit;
}

// // Get allowed reports and cache them - using serial as key
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

// Include the rcount via left join, so we do not have to make an sql query
// for every single report.
// --------------------------------------------------------------------------

$sql = "SELECT report.* , sum(rptrecord.rcount) AS rcount, MIN(rptrecord.dkimresult) AS dkimresult, MIN(rptrecord.spfresult) AS spfresult FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'neutral') AS dkimresult, COALESCE(spfresult, 'neutral') AS spfresult, serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial WHERE report.serial = " . $mysqli->real_escape_string($reportid) . " GROUP BY serial ORDER BY mindate $sort, maxdate $sort , org";

// Debug
// echo "<br /><b>Data Report sql:</b> $sql<br />";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
	if (true) {
		//add data by serial
		$reports[$row['serial']] = $row;
	}
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo tmpl_reportData($reportid, $reports, $hostlookup );

?>
