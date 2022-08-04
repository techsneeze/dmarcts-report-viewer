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

function html ($domains = array(), $orgs = array(), $periods = array() ) {

	global $tls_result;

	global $options;
	global $cookie_options;
	$html       = array();
	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>TLS Report Viewer</title>";
	$html[] = "    <link rel='stylesheet' href='" . $cookie_options['cssfile'] . "'>";
	$html[] = "    <script src='dmarcts-report-viewer.js'></script>";

	if ( $cookie_options['xml_data_hljs'] ) {
		$html[] = "    <link rel='stylesheet' href='highlight.js/styles/" . $cookie_options['cssfile'] . "'>";
		$html[] = "    <script src='highlight.js/highlight.pack.js'></script>";
	}

	$html[] = "    <script>";
	$html[] = "      var report_list_height_percent = " . $cookie_options["report_list_height_percent"] . ";";
	$html[] = "      var report_data_xml_width_percent = " . $cookie_options["report_data_xml_width_percent"] . ";";
	$html[] = "      var xml_data_open = " . $cookie_options['xml_data_open'] . ";";
	$html[] = "      var xml_data_highlight = " . $cookie_options['xml_data_highlight'] . ";";
	$html[] = "      var xml_data_hljs = " . $cookie_options['xml_data_hljs'] . ";";
	$html[] = "      var report_type = 'tls';";
	$html[] = "    </script>";
	$html[] = "    <meta charset=\"UTF-8\" />";
	$html[] = "    <meta name='google' content='notranslate' />";
	$html[] = "    <meta http-equiv=\"Content-Language\" content=\"en_US\" />";
	$html[] = "  </head>";

	$html[] = "  <body id='body' onload=showReportlist('tls'); onresize=showResizers();>";
	$html[] = "<div id='screen_overlay' onclick=\"hideMenu();\" style=\"top: 0;left: 0;height: 100%;width: 100%;position: absolute;display: none;z-index: 1;\">
    ";
	$html[] = "</div>";


	//	Optionblock form
	//	--------------------------------------------------------------------------
	$html[] = "    <div id='optionblock' class='optionblock'><form action=\"?\" method=\"post\">";


	//	Host lookup option
	//	--------------------------------------------------------------------------
	$html[] = "		<div class='options'><span class='optionlabel'>Hostname(s):</span><br>";
	$html[] = "			<div class='x-css'>";
	$html[] = "				<input type=\"radio\" class='x-css' id='HostLookupOn' name=\"HostLookup\" value=\"1\" onclick=\"showReport(current_report)\"" . ($cookie_options['HostLookup'] ? " checked=\"checked\"" : "" ) . ">";
	$html[] = "				<label for='HostLookupOn'>On</label>";
	$html[] = "				<input type=\"radio\" class='x-css' id='HostLookupOff' name=\"HostLookup\" value=\"0\" onclick=\"showReport(current_report)\"" . ($cookie_options['HostLookup'] ? "" : " checked=\"checked\"" ) . ">";
	$html[] = "				<label for='HostLookupOff'>Off</label>";
	$html[] = "			</div>";
	$html[] = "		</div>";


	// // 	DMARC select
	// // 	--------------------------------------------------------------------------
	// 	$html[] = "<div class='options'><span class='optionlabel'>DMARC Result:</span><br>";
	// 	$html[] = "<select class='x-css' name=\"DMARC\" id=\"selDMARC\" onchange=\"showReportlist('tls')\">";
	// 	$html[] = "<option " . ( $cookie_options['DMARC'] ? "" : "selected=\"selected\" " ) . "value=\"all\">[all]</option>";
	// 	foreach($dmarc_result as $key => $value) {
	// 		$html[] = sprintf("<option class='" . $value['color'] . "' %s value=\"%s\">%s</option>",
	// 				$cookie_options['DMARC'] == $key ? "selected=\"selected\"" : "",
	// 				$key,
	// 				$value['text']
	// 			);
	// 	}
	// 	$html[] = "</select>";
	// 	$html[] = "</div>";
	//
	//
	// 	Report Status select
	// 	--------------------------------------------------------------------------
		$html[] = "<div class='options'><span class='optionlabel'>Report Status:</span><br>";
		$html[] = "<select class='x-css' name=\"ReportStatus\" id=\"selReportStatus\" onchange=\"showReportlist('tls')\">";
		$html[] = "<option " . ( $cookie_options['ReportStatus'] ? "" : "selected=\"selected\" " ) . "value=\"all\">[all]</option>";
		foreach($tls_result as $key => $value) {
			$html[] = sprintf("<option class='color: " . $value['color'] . "' %s value=\"%s\">%s</option>",
					$cookie_options['ReportStatus'] == $key ? "selected=\"selected\"" : "",
					$key,
					$value['status_text']
				);
		}
		$html[] = "</select>";
		$html[] = "</div>";


	// 	Period select
	// 	--------------------------------------------------------------------------
	if ( count( $periods ) > 0 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Month:</span><br>";
		$html[] = "<select class='x-css' name=\"Period\" id=\"selPeriod\" onchange=\"showReportlist('tls')\">";
		$html[] = "<option value=\"all\"" . ($cookie_options['Period'] ? "" : " selected=\"selected\"") . ">[all]</option>";

		for ($p = 0; $p < sizeof($periods); $p++) {
			$arg = "";
			if( $p == 0 && $cookie_options['Period'] == 1 ) {
				$arg =" selected=\"selected\"";
			}
			$html[] = "<option $arg value=\"$periods[$p]\">$periods[$p]</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}


	//	Domains select
	//	--------------------------------------------------------------------------
	if ( count( $domains ) >= 1 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Domain(s):</span><br>";
		$html[] = "<select class='x-css' name=\"Domain\" id=\"selDomain\" onchange=\"showReportlist('tls')\">";
		$html[] = "<option " . ( $cookie_options['Domain'] ? "" : "selected=\"selected\" " ) . "value=\"all\">[all]</option>";

		foreach( $domains as $d) {
			$html[] = "<option " . ( $cookie_options['Domain'] == $d ? "selected=\"selected\" " : "" ) . "value=\"$d\">$d</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}


	//	Organizations select
	//	--------------------------------------------------------------------------
	if ( count( $orgs ) > 0 ) {
		$html[] = "<div class='options'><span class='optionlabel'>Reporter(s):</span><br>";
		$html[] = "<select class='x-css' name=\"Organisation\" id=\"selOrganisation\" onchange=\"showReportlist('tls')\">";
		$html[] = "<option " . ( $cookie_options['Organisation'] ? "" : "selected=\"selected\" " ) . "selected=\"selected\" value=\"all\">[all]</option>";

		foreach( $orgs as $o) {
			$html[] = "<option " . ( $cookie_options['Organisation'] == $o ? "selected=\"selected\" " : "" ) . "value=\"$o\">" . ( strlen( $o ) > 25 ? substr( $o, 0, 22) . "..." : $o ) . "</option>";
		}

		$html[] = "</select>";
		$html[] = "</div>";
	}

	//	Refresh button
	//	--------------------------------------------------------------------------
	$html[] = "<div class='options'>";
	$html[] = "&nbsp;<br><button type=\"button\" class=\"x-css\" onclick=\"refresh_report_list()\" title=\"Refreshes data with current filter.\" tabindex='0'>Refresh</button>";
	$html[] = "</div>";

	//	Reset button
	//	--------------------------------------------------------------------------
	$html[] = "<div class='options'>";
	$html[] = "&nbsp;<br><button type=\"button\" class=\"x-css\" onclick=\"reset_report_list()\" title=\"Resets the filter to show all records and refreshes the data.\" tabindex='0'>Reset</button>";
	$html[] = "</div>";

	// Configuration Settings icon
	//	--------------------------------------------------------------------------
	$html[] = "<img class='menu_icon' src='menu_icon_" . $cookie_options['cssfile'] . ".png' tabindex='0' onclick='optionMenu(this);'>";

	//	End optionblock
	//	--------------------------------------------------------------------------
	$html[] = "</form></div>";


	// Dropdown menu
	//	--------------------------------------------------------------------------
	$html[] = "<div id='menu' class='menu top'><div class='menu_option' style='border-bottom: 1px solid var(--header);' onclick=\"window.location.href = 'dmarcts-report-viewer.php';\">&nbsp;&nbsp;&nbsp;Go to DMARC Reports</div><div class='menu_option' onclick=\"build_cookie();\">&nbsp;&nbsp;&nbsp;Save Current Settings as Initial View</div><div class='menu_option' onclick=\"window.location.href = 'dmarcts-report-viewer-options.php';\">&nbsp;&nbsp;&nbsp;TLS Report Options...</div></div>";


	//	Report divs
	//	--------------------------------------------------------------------------
	$html[] = "<!-- Start of report list -->";
	$html[] = "<div id='title' class='title'>TLS Reports</div>";
	$html[] = "<div id='report_list' style='overflow-y:auto;'>";
	$html[] = "</div>";
	$html[] = "<!-- End of report list -->";

	$html[] = "<!-- Start of report data -->";
	$html[] = "<div id='report_data' style='overflow-y: hidden;text-align:center;'>";
	$html[] = "</div>";
	$html[] = "<!-- End of report data -->";

	//	Page Footer
	//	--------------------------------------------------------------------------
	$html[] = "  <div id='footer' class='footer'>&copy; 2016-" . date("Y") . " by <a href='http://www.techsneeze.com'>TechSneeze.com</a>, John Bieling and <a href='mailto:dmarcts-report-viewer@hazelden.ca'>John P. New</a></div>";
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

// Get all configuration options
// --------------------------------------------------------------------------
configure();

setcookie("dmarcts-options-tmp", "", time() - 3600, "/");

// Make a DB Connection
// --------------------------------------------------------------------------
$dbh = connect_db($dbtype, $dbhost, $dbport, $dbname, $dbuser, $dbpass);


// Get all domains reported
// --------------------------------------------------------------------------
$sql = "
SELECT DISTINCT
	domain
FROM
	tls_report
ORDER BY
	domain
";

$query = $dbh->query($sql);

foreach($query as $row) {
	$domains[] = $row['domain'];
}

// Get all organisations
// --------------------------------------------------------------------------
$sql = "
SELECT DISTINCT
	org
FROM
	tls_report
ORDER BY
	org
";

$query = $dbh->query($sql);

foreach($query as $row) {
	$orgs[] = $row['org'];
}

// Get all periods
// --------------------------------------------------------------------------
$sql = "
(
	SELECT
		EXTRACT(YEAR FROM mindate) AS year,
		EXTRACT(MONTH FROM mindate) AS month
	FROM
		tls_report
)
UNION
(
	SELECT
		EXTRACT(YEAR FROM mindate) AS year,
		EXTRACT(MONTH FROM mindate) AS month
	FROM
		tls_report
)
ORDER BY
	year DESC,
	month DESC
";

$query = $dbh->query($sql);

foreach($query as $row) {
	$periods[] = sprintf( "%'.04d-%'.02d", $row['year'], $row['month'] );
}

// When arrays of data do not exist, for example in a new & empty set-up, create empty arrays
// --------------------------------------------------------------------------
if(!isset($domains)){
        $domains = array();
}
if(!isset($orgs)){
        $orgs = array();
}
if(!isset($periods)){
        $periods = array();
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo html(
	$domains,
	$orgs,
	$periods
);
?>
