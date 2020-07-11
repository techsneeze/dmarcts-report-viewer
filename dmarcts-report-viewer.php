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
//####################################################################
//### functions ######################################################
//####################################################################

function html ($default_hostlookup = 1, $cssfile, $domains = array(), $orgs = array(), $periods = array() ) {

	$html       = array();

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>DMARC Report Viewer</title>";
	$html[] = "    <link rel='stylesheet' href='$cssfile'>";
	$html[] = "    <script src='dmarcts-report-viewer.js'></script>";
	$html[] = "    <meta charset=\"UTF-8\" />";
	$html[] = "    <meta name='google' content='notranslate' />";
	$html[] = "    <meta http-equiv=\"Content-Language\" content=\"en_US\" />";
	$html[] = "  </head>";

	$html[] = "  <body id='body' onload=showReportlist('reportlistTbl');>";
// 	$html[] = "  <body id='body' onload=showReportlist('reportlistTbl');makeResizableDiv();>";


	//	Optionblock form
	//	--------------------------------------------------------------------------
	$html[] = "    <div id='optionblock' class='optionblock'><form action=\"?\" method=\"post\">";


	//	Host lookup option
	//	--------------------------------------------------------------------------
	$html[] = "<div class='options'><span class='optionlabel'>Hostname(s):</span> <input type=\"radio\" name=\"selHostLookup\" value=\"1\" onclick=\"showReport(current_report)\"" . ($default_hostlookup ? " checked=\"checked\"" : "" ) . "> on<input type=\"radio\" name=\"selHostLookup\" value=\"0\" onclick=\"showReport(current_report)\"" . ($default_hostlookup ? "" : " checked=\"checked\"" ) . "> off</div>";


	// 	DMARC select
	// 	--------------------------------------------------------------------------
		$html[] = "<div class='options'><span class='optionlabel'>DMARC Result:</span>";
		$html[] = "<select name=\"selDMARC\" id=\"selDMARC\" onchange=\"showReportlist('reportlistTbl')\">";
		$html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";
		$html[] = "<option value=\"1\">DKIM and SPF Pass</option>";
		$html[] = "<option value=\"3\">DKIM or SPF Fail</option>";
		$html[] = "<option value=\"4\">DKIM and SPF Fail</option>";
		$html[] = "<option value=\"2\">Other condition</option>";
		$html[] = "</select>";
		$html[] = "</div>";


	// 	Period select
	// 	--------------------------------------------------------------------------
	if ( count( $periods ) > 0 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Month:</span>";
		$html[] = "<select name=\"selPeriod\" id=\"selPeriod\" onchange=\"showReportlist('reportlistTbl')\">";
		$html[] = "<option value=\"all\">[all]</option>";

		for ($p = 0; $p < sizeof($periods); $p++) {
			$arg = "";
			if( $p == 0 ) {
				$arg =" selected=\"selected\"";
			}
			$html[] = "<option $arg value=\"$periods[$p]\">$periods[$p]</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}

	//	Domains select
	//	--------------------------------------------------------------------------
	if ( count( $domains ) > 1 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Domain(s):</span>";
		$html[] = "<select name=\"selDomain\" id=\"selDomain\" onchange=\"showReportlist('reportlistTbl')\">";
		$html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";

		foreach( $domains as $d) {
			$html[] = "<option $arg value=\"$d\">$d</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}


	//	Organizations select
	//	--------------------------------------------------------------------------
	if ( count( $orgs ) > 0 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Reporter(s):</span>";
		$html[] = "<select name=\"selOrganisation\" id=\"selOrganisation\" onchange=\"showReportlist('reportlistTbl')\">";
		$html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";

		foreach( $orgs as $o) {
			$html[] = "<option $arg value=\"$o\">" . ( strlen( $o ) > 25 ? substr( $o, 0, 22) . "..." : $o ) . "</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}

	//	Refresh button
	//	--------------------------------------------------------------------------
	$html[] = "<div class='options'>";
	$html[] = "<button type=\"button\" onclick=\"refresh_report_list()\" title=\"Refreshes data with current filter.\">Refresh</button>";
	$html[] = "</div>";

	//	Reset button
	//	--------------------------------------------------------------------------
	$html[] = "<div class='options' style='border-right: 0px;'>";
	$html[] = "<button type=\"button\" onclick=\"reset_report_list()\" title=\"Resets the filter to show all records and refreshes the data.\">Reset</button>";
	$html[] = "</div>";

	//	End optionblock
	//	--------------------------------------------------------------------------
	$html[] = "</form></div>";


	//	Report divs
	//	--------------------------------------------------------------------------
	$html[] = "<!-- Start of report list -->";
	$html[] = "<div id='title' class='title'>DMARC Reports</div>";
	$html[] = "<div id='report_list' style='overflow-y:auto; resize: vertical;'>";
	$html[] = "</div>";
	$html[] = "<!-- End of report list -->";

	$html[] = "<!-- Start of report data -->";
	$html[] = "<div id='report_data' style='text-align:center;'>";
	$html[] = "</div>";
	$html[] = "<!-- End of report data -->";

	//	Page Footer
	//	--------------------------------------------------------------------------
	$html[] = "  <div id='footer' class='footer'>&copy; 2016-2020 by <a href='http://www.techsneeze.com'>TechSneeze.com</a>, John Bieling and <a href='mailto:dmarcts-report-viewer@hazelden.ca'>John P. New</a>. <span id='icons' style='display:none;'> XML/HTML Icons made by <a href=\"https://smashicons.com/\" title=\"Smashicons\">Smashicons</a> from <a href=\"https://www.flaticon.com/\" title=\"Flaticon\"> www.flaticon.com</a></span></div>";
	$html[] = "  </body>";
	$html[] = "</html>";

	return implode("\n",$html);
}

//####################################################################
//### main ###########################################################
//####################################################################

// These files must exist, in the same folder as this script.
include "dmarcts-report-viewer-config.php";
include "dmarcts-report-viewer-common.php";

// Make a MySQL Connection using mysqli
// --------------------------------------------------------------------------
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection<br />";
	echo "Errno: " . $mysqli->connect_errno . " ";
	echo "Error: " . $mysqli->connect_error . " ";
// Debug ONLY. This will expose database credentials when database connection fails
// 	echo "Database connection information: <br />dbhost: " . $dbhost . "<br />dbuser: " . $dbuser . "<br />dbpass: " . $dbpass . "<br />dbname: " . $dbname . "<br />dbport: " . $dbport . "<br />";
	exit;
}

// Get all domains reported
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT domain FROM `report` ORDER BY domain";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");

while($row = $query->fetch_assoc()) {
	$domains[] = $row['domain'];
}

// Get all organisations
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT org FROM `report` ORDER BY org";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");

while($row = $query->fetch_assoc()) {
	$orgs[] = $row['org'];
}

// Get all periods
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT DISTINCT year(mindate) as year, month(mindate) as month FROM `report` ORDER BY year desc, month desc";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");

while($row = $query->fetch_assoc()) {
	$periods[] = sprintf( "%'.04d-%'.02d", $row['year'], $row['month'] );
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo html( 
	$default_hostlookup, 
	$cssfile, 
	$domains, 
	$orgs, 
	$periods
);
?>
