<?php

// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com and John Bieling
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

//####################################################################
//### functions ######################################################
//####################################################################

function format_date($date, $format) {
	$answer = date($format, strtotime($date));
	return $answer;
};

function tmpl_reportList($allowed_reports, $host_lookup = 1, $sort_order, $dom_select = '') {

	$reportlist[] = "";
	$reportlist[] = "<!-- Start of report list -->";

	$reportlist[] = "<h1 class='main'>DMARC Reports" . ($dom_select == '' ? '' : " for " . htmlentities($dom_select)) . "</h1>";
	$reportlist[] = "<table class='reportlist'>";
	$reportlist[] = "  <thead>";
	$reportlist[] = "    <tr>";
	$reportlist[] = "      <th>Start Date</th>";
	$reportlist[] = "      <th>End Date</th>";
	$reportlist[] = "      <th>Domain</th>";
	$reportlist[] = "      <th>Reporting Organization</th>";
	$reportlist[] = "      <th>Report ID</th>";
	$reportlist[] = "      <th>Messages</th>";
	$reportlist[] = "    </tr>";
	$reportlist[] = "  </thead>";

	$reportlist[] = "  <tbody>";
	$reportsum    = 0;

	foreach ($allowed_reports[BySerial] as $row) {
		$row = array_map('htmlspecialchars', $row);
		$date_output_format = "r";
		$reportlist[] =  "    <tr>";
		$reportlist[] =  "      <td class='right'>". format_date($row['mindate'], $date_output_format). "</td>";
		$reportlist[] =  "      <td class='right'>". format_date($row['maxdate'], $date_output_format). "</td>";
		$reportlist[] =  "      <td class='center'>". $row['domain']. "</td>";
		$reportlist[] =  "      <td class='center'>". $row['org']. "</td>";
                $reportlist[] =  "      <td class='center'><a href='?report=" . $row['serial'] . ( $host_lookup ? "&hostlookup=1" : "&hostlookup=0" ) . ( $sort_order ? "&sortorder=1" : "&sortorder=0" ) . ($dom_select == '' ? '' : "&d=$dom_select") . "#rpt". $row['serial'] . "'>". $row['reportid']. "</a></td>";
		$reportlist[] =  "      <td class='center'>". number_format($row['rcount']+0,0). "</td>";
		$reportlist[] =  "    </tr>";
		$reportsum += $row['rcount'];
	}
	$reportlist[] = "<tr class='sum'><td></td><td></td><td></td><td></td><td class='right'>Sum:</td><td class='center'>".number_format($reportsum,0)."</td></tr>";
	$reportlist[] =  "  </tbody>";

	$reportlist[] =  "</table>";

	$reportlist[] = "<!-- End of report list -->";
	$reportlist[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportlist);
}

function tmpl_reportData($reportnumber, $allowed_reports, $host_lookup = 1, $sort_order) {

	if (!$reportnumber) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<!-- Start of report rata -->";
	$reportsum    = 0;

	if (isset($allowed_reports[BySerial][$reportnumber])) {
		$row = $allowed_reports[BySerial][$reportnumber];
		$row = array_map('htmlspecialchars', $row);
		$reportdata[] = "<a id='rpt".$reportnumber."'></a>";
		$reportdata[] = "<div class='center reportdesc'><p> Report from ".$row['org']." for ".$row['domain']."<br>(". format_date($row['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</p></div>";
	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<table class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th>IP Address</th>";
	$reportdata[] = "      <th>Host Name</th>";
	$reportdata[] = "      <th>Message Count</th>";
	$reportdata[] = "      <th>Disposition</th>";
	$reportdata[] = "      <th>Reason</th>";
	$reportdata[] = "      <th>DKIM Domain</th>";
	$reportdata[] = "      <th>Raw DKIM Result</th>";
	$reportdata[] = "      <th>SPF Domain</th>";
	$reportdata[] = "      <th>Raw SPF Result</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";

	$reportdata[] = "  <tbody>";

	global $mysqli;
	$sql = "SELECT * FROM rptrecord where serial = $reportnumber";
	$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
	while($row = $query->fetch_assoc()) {
		$row = array_map('htmlspecialchars', $row);
		$status="";
		if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")) {
			$status="red";
		} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")) {
			$status="orange";
		} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")) {
			$status="lime";
		} else {
			$status="yellow";
		};

		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		}
		if ( $row['ip6'] ) {
			$ip = inet_ntop($row['ip6']);
		}

		$reportdata[] = "    <tr class='".$status."'>";
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
	$reportdata[] = "<tr><td></td><td></td><td>$reportsum</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
	$reportdata[] = "  </tbody>";
	$reportdata[] = "</table>";

	$reportdata[] = "<!-- End of report rata -->";
	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

function tmpl_page ($body, $reportid, $host_lookup = 1, $sort_order, $dom_select, $domains = array(),$cssfile ) {
	$html       = array();
        $url_hswitch = ( $reportid ? "?report=$reportid&hostlookup=" : "?hostlookup=" )
                . ($host_lookup ? "0" : "1" )
                . ( "&sortorder=" ) . ($sort_order)
                . (isset($dom_select) && $dom_select <> "" ? "&d=$dom_select" : "" )
                ;
        $url_dswitch = "?hostlookup=" . ($host_lookup ? "1" : "0" ) . "&sortorder=" . ($sort_order); // drop selected report on domain switch
        $url_sswitch = ( $reportid ? "?report=$reportid&hostlookup=" : "?hostlookup=" )
                . ($host_lookup)
                . ( "&sortorder=" ) . ($sort_order ? "0" : "1" )
                . (isset($dom_select) && $dom_select <> "" ? "&d=$dom_select" : "" )
                ;

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>DMARC Report Viewer</title>";
	$html[] = "    <link rel='stylesheet' href='$cssfile'>";
	$html[] = "  </head>";

	$html[] = "  <body>";
  $html[] = "  <div class='optionblock'><div class='options'><span class='optionlabel'>Hostname Lookup:</span> <span class='activated'>" . ($host_lookup ? "on" : "off" ) . "</span> <a class='deactivated' href=\"$url_hswitch\">" . ($host_lookup ? "off" : "on" ) . "</a></div>";
  $html[] = "  <div class='options'><span class='optionlabel'>Sort order:</span> <span class='activated'>" . ($sort_order ? "ascending" : "descending" ) . "</span> <a class='deactivated' href=\"$url_sswitch\">" . ($sort_order ? "descending" : "ascending" ) . "</a></div>";	
  if ( count( $domains ) > 1 ) {
    $html[] = "<div class='options'><span class='optionlabel'>Domain(s):</span> <span class='activated'>" . ( "" == $dom_select ? "all" : $dom_select ) . "</span>";
    foreach( $domains as $d) {
      if( $d != $dom_select ) {
        $html[] = "<a class='deactivated' href=\"$url_dswitch&d=$d\">" . $d . "</a> ";
      }
    }
    if( "" != $dom_select ) {
      $html[] = "<a class='deactivated' href=\"$url_dswitch\">all</a>";
    }
  }
  $html[] = "</div>";   /* end domain option */
  
  $html[] = "<div class='options'><span class='optionlabel'>Period:</span> <span class='activated'>[to come]</span></div>";
  
  $html[] = "</div>";   /* end optionblock */

  $html[] = $body;
	
	$html[] = "  <div class='footer'>Brought to you by <a href='http://www.techsneeze.com'>TechSneeze.com</a> - <a href='mailto:dave@techsneeze.com'>dave@techsneeze.com</a></div>";
	$html[] = "  </body>";
	$html[] = "</html>";

	return implode("\n",$html);
}


//####################################################################
//### main ###########################################################
//####################################################################

// The file is expected to be in the same folder as this script, and it
// must exist.
include "dmarcts-report-viewer-config.php";
$dom_select= '';

if(!isset($dport)) {
  $dbport="3306";
}
if(!isset($cssfile)) {
  $cssfile="default.css";
}

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
if(isset($_GET['d'])){
  $dom_select=$_GET['d'];
}elseif(!isset($_GET['d'])){
  $dom_select= '';
}else{
  die('Invalid domain');
}


// Make a MySQL Connection using mysqli
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection, here is why: \n";
	echo "Errno: " . $mysqli->connect_errno . "\n";
	echo "Error: " . $mysqli->connect_error . "\n";
	exit;
}

define("BySerial", 1);
define("ByDomain", 2);
define("ByOrganisation", 3);

// get all domains reported
$sql="SELECT DISTINCT domain FROM `report` ORDER BY domain";
$domains= array();
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
  $domains[] = $row['domain'];
}
if( $dom_select <> '' && array_search($dom_select, $domains) === FALSE ) {
	echo "Error: invalid domain " . htmlentities($dom_select) . " \n";
	exit;
}


// Get allowed reports and cache them - using serial as key
$allowed_reports = array();

# Include the rcount via left join, so we do not have to make an sql query for every single report.
$where = '';
if( $dom_select <> '' ) {
  $where = "WHERE domain='" . $mysqli->real_escape_string($dom_select) . "'";
} 
$sort = '';
if( $sortorder ) {
  $sort = "ASC";
} else {
  $sort = "DESC";
}
$sql = "SELECT report.* , sum(rptrecord.rcount) AS rcount FROM `report` LEFT JOIN rptrecord ON report.serial = rptrecord.serial $where GROUP BY serial ORDER BY mindate $sort,maxdate $sort ,org";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
	//todo: check ACL if this row is allowed
	if (true) {
		//add data by serial
		$allowed_reports[BySerial][$row['serial']] = $row;
		//make a list of serials by domain and by organisation
		//$allowed_reports[ByDomain][$row['domain']][] = $row['serial'];
		//$allowed_reports[ByOrganisation][$row['org']][] = $row['serial'];
	}
}

// Generate Page with report list and report data (if a report is selected).
echo tmpl_page( ""
        .tmpl_reportList($allowed_reports, $hostlookup, $sortorder, $dom_select)
        .tmpl_reportData($reportid, $allowed_reports, $hostlookup, $sortorder )
	, $reportid
	, $hostlookup
	, $sortorder
	, $dom_select
	, $domains
	, $cssfile
);
?>
