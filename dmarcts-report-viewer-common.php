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

//####################################################################
//### variables ######################################################
//####################################################################

$cookie_name = "dmarcts-options";

// The order in which the options appear here is the order they appear in the DMARC Results dropdown box
$dmarc_result = array(

	'DMARC_PASS' => array(
		'text' => 'Pass',
		'status_text' => 'All Passed',
		'color' => 'green',
		'status_sort_key' => 3,
		'status_sql_where' => "dkim_align_min = 2 AND spf_align_min = 2 AND dkim_result_min = 2 AND spf_result_min = 2 AND dmarc_result_min = 2 AND dmarc_result_max = 2",
	),
	'DMARC_FAIL' => array(
		'text' => 'Fail',
		'status_text' => 'All Failed',
		'color' => 'red',
		'status_sort_key' => 0,
		'status_sql_where' => "dkim_align_min = 0 AND spf_align_min = 0 AND dkim_result_min = 0 AND spf_result_min = 0 AND dmarc_result_min = 0 AND dmarc_result_max = 0",
	),
	'DMARC_PASS_AND_FAIL' => array(
		'text' => 'Mixed',
		'status_text' => 'At least one failed result',
		'color' => 'orange',
		'status_sort_key' => 1,
		'status_sql_where' => "( dkim_align_min = 0 OR spf_align_min = 0 OR dkim_result_min = 0 OR spf_result_min = 0 OR dmarc_result_min = 0 OR dmarc_result_max = 0 )",
	),
	'DMARC_OTHER_CONDITION' => array(
		'text' => 'Other',
		'status_text' => 'Other condition',
		'color' => 'yellow',
		'status_sort_key' => 2,
		'status_sql_where' => "( dkim_align_min = 1 OR spf_align_min = 1 OR dkim_result_min = 1 OR spf_result_min = 1 OR dmarc_result_min >= 3 OR dmarc_result_max >= 3 )",
	),
);

// The order in which the options appear here is the order they appear in the TLS-RPT Report Status dropdown box
$tls_result = array(

	'TLS_PASS' => array(
		'text' => 'Pass',
		'status_text' => 'All Passed',
		'color' => 'green',
		'status_sort_key' => 3,
		'status_sql_where' => " summary_successful > 0 AND summary_failure = 0",
	),
	'TLS_FAIL' => array(
		'text' => 'Fail',
		'status_text' => 'All Failed',
		'color' => 'red',
		'status_sort_key' => 0,
		'status_sql_where' => " summary_successful = 0 AND summary_failure > 0",
	),
	'TLS_PASS_AND_FAIL' => array(
		'text' => 'Mixed',
		'status_text' => 'Mixed result',
		'color' => 'orange',
		'status_sort_key' => 1,
		'status_sql_where' => " summary_successful > 0 AND summary_failure > 0",
	),
);

// Sortable Report List column headers
// --------------------------------------------------------------------------
// Array to be used in 'Default sort column' option in dmarcts-report-viewer-options.php

$report_list_columns = array(
	"mindate" => "Start Date",
	"maxdate" => "End Date",
	"domain" => "Domain",
	"org" => "Reporter",
	"reportid" => "Report ID",
	"rcount" => "# Messages"
);

// Program Options
// --------------------------------------------------------------------------

// When a new option is added, check the size of the cookie stored. The cookie size should be less than half of the maximum cookie size allowed per domain.
// Less than half because sometimes the cookie is stored twice (once as dmarcts-options and once as dmarcts-options-tmp). Most browsers have a cookie limit of 4KB.
// Currently, the following options generate a cookie of about 0.5KB

// Option Names must be unique.
// The order in which the options appear below is the order they are rendered in the browser.
// If sections are re-arranged, you don't have to re-name the corresponding heading (i.e. the heading option name (e.g. option_group_3_heading)) because it has no bearing on the order rendered in the browser.
$options = array(
	"option_group_1_heading" => array(
			"option_type" => "heading",
			"option_label" => "Appearance",
			"option_values" => "",
			"option_value" => "",
			"option_description" => "",
	),
	"cssfile" => array(
			"option_type" => "select",
			"option_label" => "Default css file",
			"option_values" => "\$cssfiles",
			"option_value" => "default.css",
			"option_description" => "Name of the css file to be used.<br>The dropdown list is automatically generated from any css files in the main dmarcts-report-viewer directory. The css is immediately applied to this page when selected.",
	),
	"date_format" => array(
			"option_type" => "text",
			"option_label" => "Date Format",
			"option_values" => "",
			"option_value" => "Y-m-d G:i:s T",
			"option_description" => "String to format the dates in the Report List and the Report Description.<br>The default format string is Y-m-d G:i:s T which shows, for example, 2020-01-01 00:00:00 UTC.<br>For the allowable options in the format string, see <a href='https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters' target='_blank'>the documentation</a>.",
	),
"option_group_2_heading" => array(
			"option_type" => "heading",
			"option_label" => "Filters",
			"option_values" => "",
			"option_value" => "",
			"option_description" => "Default filters",
	),
	"DMARC" => array(
			"option_type" => "select",
			"option_label" => "Default DMARC Result",
			"option_values" => "\$dmarc_result_select",
			"option_value" => "all",
			"option_description" => "Default for DMARC Result drop-down list.",
	),
	"dmarc_results_matching_only" => array(
			"option_type" => "radio",
			"option_label" => "Show Only Matching Report Data records.",
			"option_values" => array(1,"On",0,"Off"),
			"option_value" => 0,
			"option_description" => "When enabled, only those records matching the DMARC Results dropdown box are shown in the Report Data table.",
	),
	"ReportStatus" => array(
			"option_type" => "select",
			"option_label" => "Default Report Data Status",
			"option_values" => "\$report_status_select",
			"option_value" => "all",
			"option_description" => "Default for Report Data Status drop-down list.",
	),
	"Period" => array(
			"option_type" => "radio",
			"option_label" => "Default period",
			"option_values" => array(0,"All",1,"Current Month"),
			"option_value" => 1,
			"option_description" => "Default for the Month drop-down.",
	),
	"Domain" => array(
			"option_type" => "select",
			"option_label" => "Default domain",
			"option_values" => "\$domains",
			"option_value" => "all",
			"option_description" => "Default for the Domain(s) drop-down list.",
	),
	"Organisation" => array(
			"option_type" => "select",
			"option_label" => "Default reporter",
			"option_values" => "\$orgs",
			"option_value" => "all",
			"option_description" => "Default for the Reporter(s) drop-down list.",
	),
	"option_group_3_heading" => array(
			"option_type" => "heading",
			"option_label" => "Initial Settings",
			"option_values" => "",
			"option_value" => "",
			"option_description" => "Startup Defaults",
	),
	"HostLookup" => array(
			"option_type" => "radio",
			"option_label" => "Host lookup",
			"option_values" => array(1,"On",0,"Off"),
			"option_value" => 1,
			"option_description" => "Turning off host lookup speeds up the display of the results, especially in the case of mail servers that have ceased to exist.",
	),
	"report_list_height_percent" => array(
			"option_type" => "number",
			"option_label" => "Report List - Initial Height",
			"option_values" => array("units"=>"percent","min"=>"0","max"=>100),
			"option_value" => 60,
			"option_description" => "Initial height of the Report List window, a percentage of the height of the main browser window.",
	),
	"sort_column" => array(
			"option_type" => "select",
			"option_label" => "Default sort column",
			"option_values" => "\$report_list_columns",
			"option_value" => "maxdate",
			"option_description" => "Report List column to sort initially.",
	),
	"sort" => array(
			"option_type" => "radio",
			"option_label" => "Default sort order",
			"option_values" => array(1,"Ascending",0,"Descending"),
			"option_value" => 0,
			"option_description" => "Default sort order of Report List column chosen above.",
	),
	"option_group_4_heading" => array(
			"option_type" => "heading",
			"option_label" => "XML Data",
			"option_values" => "",
			"option_value" => "",
			"option_description" => "Startup Defaults",
	),
	"xml_data_open" => array(
			"option_type" => "radio",
			"option_label" => "Show Report Data XML",
			"option_values" => array(1,"On",0,"Off"),
			"option_value" => 0,
			"option_description" => "When a report is selected in the Report List, automatically open the XML view along with the Report Table.",
	),
	"report_data_xml_width_percent" => array(
			"option_type" => "number",
			"option_label" => "Report Data XML - Initial Width",
			"option_values" => array("units"=>"percent","min"=>"0","max"=>"100"),
			"option_value" => 25,
			"option_description" => "Initial width of the Report Data XML window when it is opened, a percentage of the width of the main browser window.",
	),
	"xml_data_highlight" => array(
			"option_type" => "radio",
			"option_label" => "Use Report Data to Raw XML Highlighting",
			"option_values" => array(1,"On",0,"Off"),
			"option_value" => "1",
			"option_description" => "When the raw XML view is open, and when the mouse hovers over, or clicks on, a line of the Report Data table or the Report Data description, highlight the section in the raw XML that corresponds to that row or description. Also works in the opposite direction (i.e. hover/click on a XML record to highlight the corresponding Report Data table line or description). Facilitates determining which XML record corresponds to which line of the table.",
	),
	"xml_data_hljs" => array(
			"option_type" => "radio",
			"option_label" => "Use XML Syntax Highlighting",
			"option_values" => array(1,"On",0,"Off"),
			"option_value" => "1",
			"option_description" => "Use syntax highlighting on the Raw XML. This uses a small external javascript file which may or may not slow down the program.",
	)
	// This option will be implemented in a future version of dmarcts-reports-viewer.
	// ),
	// "alignment_unknown" => array(
	// 		"option_type" => "radio",
	// 		"option_label" => "Unknown SPF/DKIM Alignments",
	// 		"option_values" => array(1,"Consider \"Failed\"",0,"Keep as \"Unknown\""),
	// 		"option_value" => "0",
	// 		"option_description" => "The DMARC specification dictates that reporting SPF/DKIM alignments is mandatory. However, there could be a situation where this information is not included. This option specifies whether or not those unknown results are included as an \"alignment failure\" or remain as \"unknown\".",
	// )
);


//####################################################################
//### functions ######################################################
//####################################################################

function main() {

	include "dmarcts-report-viewer-config.php";
}

// This function sets variables for the DMARC Result portion (left half-circle) in the Report List
function get_dmarc_result($row) {

	global $dmarc_result;
	$color = "";
	$color_sort_key = "";
	$result_text = "";

	if (($row['dmarc_result_min'] == 0) && ($row['dmarc_result_max'] == 0)) {
		$color     = $dmarc_result['DMARC_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_FAIL']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_FAIL']['text'];
	} elseif (($row['dmarc_result_min'] == 0) && ($row['dmarc_result_max'] == 1 || $row['dmarc_result_max'] == 2)) {
		$color     = $dmarc_result['DMARC_PASS_AND_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_PASS_AND_FAIL']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_PASS_AND_FAIL']['text'];
	} elseif (($row['dmarc_result_min'] == 1 || $row['dmarc_result_min'] == 2) && ($row['dmarc_result_max'] == 1 || $row['dmarc_result_max'] == 2)) {
		$color     = $dmarc_result['DMARC_PASS']['color'];
		$color_sort_key = $dmarc_result['DMARC_PASS']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_PASS']['text'];
	} else {
		$color     = $dmarc_result['DMARC_OTHER_CONDITION']['color'];
		$color_sort_key = $dmarc_result['DMARC_OTHER_CONDITION']['status_sort_key'];
		$result_text = $dmarc_result['DMARC_OTHER_CONDITION']['text'];
	}
	return array('color' => $color, 'status_sort_key' => $color_sort_key, 'result' => $result_text);
}

// This function sets variables for the All Results portion (right half-circle) in the Report List table
function get_report_status($row) {

	global $dmarc_result;
	$color = "";
	$color_sort_key = "";
	$status_text = "";
	$status_sql_where = "";

	$report_status_min = min($row['dkim_align_min'],$row['spf_align_min'],$row['dkim_result_min'],$row['spf_result_min'],$row['dmarc_result_min']);

	if ($row['dkim_align_min'] == 0 && $row['spf_align_min'] == 0 && $row['dkim_result_min'] == 0 && $row['spf_result_min'] == 0 && $row['dmarc_result_min'] == 0) {
		$color = $dmarc_result['DMARC_FAIL']['color'];
		$color_sort_key = $dmarc_result['DMARC_FAIL']['status_sort_key'];
		$status_text = $dmarc_result['DMARC_FAIL']['status_text'];
	} else {
		switch ($report_status_min) {
			case 0:
				$color = $dmarc_result['DMARC_PASS_AND_FAIL']['color'];
				$color_sort_key = $dmarc_result['DMARC_PASS_AND_FAIL']['status_sort_key'];
				$status_text = $dmarc_result['DMARC_PASS_AND_FAIL']['status_text'];
				break;
			case 1:
				$color = $dmarc_result['DMARC_OTHER_CONDITION']['color'];
				$color_sort_key = $dmarc_result['DMARC_OTHER_CONDITION']['status_sort_key'];
				$status_text = $dmarc_result['DMARC_OTHER_CONDITION']['status_text'];
				break;
			case 2:
				$color = $dmarc_result['DMARC_PASS']['color'];
				$color_sort_key = $dmarc_result['DMARC_PASS']['status_sort_key'];
				$status_text = $dmarc_result['DMARC_PASS']['status_text'];
				break;
			default:
				break;
		}
	}

	return array('color' => $color, 'status_sort_key' => $color_sort_key, 'status_text' => $status_text);
}

// This function sets variables for the TLS-RPT Result in the Report List
function get_tls_result($row) {

		global $tls_result;
		$color = "";
		$color_sort_key = "";
		$result_text = "";

		if (($row['summary_successful'] == 0) && ($row['summary_failure'] > 0)) {
			$color     = $tls_result['TLS_FAIL']['color'];
			$color_sort_key = $tls_result['TLS_FAIL']['status_sort_key'];
			$result_text = $tls_result['TLS_FAIL']['text'];
		} elseif (($row['summary_successful'] > 0) && ($row['summary_failure'] > 0)) {
			$color     = $tls_result['TLS_PASS_AND_FAIL']['color'];
			$color_sort_key = $tls_result['TLS_PASS_AND_FAIL']['status_sort_key'];
			$result_text = $tls_result['TLS_PASS_AND_FAIL']['text'];
		} elseif (($row['summary_successful'] > 0) && ($row['summary_failure'] == 0)) {
			$color     = $tls_result['TLS_PASS']['color'];
			$color_sort_key = $tls_result['TLS_PASS']['status_sort_key'];
			$result_text = $tls_result['TLS_PASS']['text'];
		} else {
			$color     = $tls_result['TLS_OTHER_CONDITION']['color'];
			$color_sort_key = $tls_result['TLS_OTHER_CONDITION']['status_sort_key'];
			$result_text = $tls_result['TLS_OTHER_CONDITION']['text'];
		}
		return array('color' => $color, 'status_sort_key' => $color_sort_key, 'result' => $result_text);
}

// This function sets variables for individual cells in the Report Data table
function get_status_color($result) {

	global $dmarc_result;
	$color = "";
	$color_sort_key = "";

	if ($result == "fail") {
		$color = $dmarc_result['DMARC_FAIL']['color'];
#		$color_sort_key = $dmarc_result['STATUS_FAIL']['status_sort_key'];
	} elseif ($result == "pass") {
		$color = $dmarc_result['DMARC_PASS']['color'];
#		$color_sort_key = $dmarc_result['STATUS_PASS']['status_sort_key'];
	} else {
		$color = $dmarc_result['DMARC_OTHER_CONDITION']['color'];
#		$color_sort_key = $dmarc_result['STATUS_OTHER_CONDITION']['status_sort_key'];
	}

    return array('color' => $color, 'status_sort_key' => $color_sort_key);
}

function format_date($date, $format) {

    $answer = date($format, strtotime($date));
    return $answer;
};

// null-safe version of htmlspecialchars for PHP 8+ compatibility
// --------------------------------------------------------------------------
function html_escape($str) {
	if ($str == null) {
		return null;
	}
	return htmlspecialchars($str);
}

// Get all configuration options
// --------------------------------------------------------------------------
function configure() {

	global $cookie_name;
	global $cookie_options;
	global $options;

	$option = array_keys($options);
	$cookie_options = array();
	$cookie_timeout = 60*60*24*365;

	if(!isset($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] == "" ) {
		// No Cookie
		foreach ($option as $option_name) {
			if ( $options[$option_name]['option_type'] != "heading" ) {
				$cookie_options += array($option_name => $options[$option_name]['option_value']);
			// 		foreach($options[$option_name] as $key=>$value) {
			}
		}
		setcookie($cookie_name, json_encode($cookie_options), time() + $cookie_timeout, "/");
	} else {
		// Cookie exists
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			// POST
			foreach ($option as $option_name) {
				if ( $options[$option_name]['option_type'] != "heading" ) {
					if ( is_null($_POST[$option_name]) ) {
						$cookie_options += array($option_name => "");
					} else {
						$cookie_options += array($option_name => test_input($_POST[$option_name]));
					}
				}
			}
			setcookie($cookie_name, json_encode($cookie_options), time() + $cookie_timeout, "/");
			header("Location: dmarcts-report-viewer.php");
			exit;
		} else {	// Not POST
			$cookie_options = json_decode($_COOKIE[$cookie_name], true);

			// Check if any options have been removed or added to $options[]
			// Update $cookie_options with any new options from $options (excluding headings)
			foreach ($option as $option_name) {
				if ( $options[$option_name]['option_type'] != "heading" && is_null($cookie_options[$option_name]) ) {
					$cookie_options[$option_name] = $options[$option_name]['option_value'];
				}
			}
			// Remove any options from $cookie_options which are not in $options
			foreach ($cookie_options as $key => $value) {
				if ( $options[$key] == null ) {
					unset($cookie_options[$key]);
				}
			}
			setcookie($cookie_name, json_encode($cookie_options), time() + $cookie_timeout, "/");
		}
	}
}

function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);

  return $data;
}

// This functions opens a connection to the database using PDO
function connect_db($dbtype, $dbhost, $dbport, $dbname, $dbuser, $dbpass) {
	$dbtype = $dbtype ?: 'mysql';
	try {
		$dbh = new PDO("$dbtype:host=$dbhost;port=$dbport;dbname=$dbname", $dbuser, $dbpass);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $dbh;
	} catch (PDOException $e) {
		echo "Error: Failed to make a database connection<br />";
		echo "Error: " . $e->getMessage() . " ";
	// Debug ONLY. This will expose database credentials when database connection fails
	// 	echo "Database connection information: <br />dbhost: " . $dbhost . "<br />dbuser: " . $dbuser . "<br />dbpass: " . $dbpass . "<br />dbname: " . $dbname . "<br />dbport: " . $dbport . "<br />";
		exit;
	}
}
