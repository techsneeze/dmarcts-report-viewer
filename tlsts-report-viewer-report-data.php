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

	global $dmarc_where;
	global $cookie_options;

	$title_message = "Click to toggle sort direction by this column";

	if (!$reportnumber) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<script type=\"text/javascript\">sorttable();</script>";

	$reportdata[] = "<!-- Start of report data -->";

	$reportsum    = 0;

	if (isset($reports[$reportnumber])) {
		$row = $reports[$reportnumber];

		// $row['raw_xml'] = formatXML($row['raw_xml'], $reportnumber);

		$reportdata[] = "<div id='report_desc_container' class='center reportdesc_container'>";
		$reportdata[] = "	<div id='report_desc' class='center reportdesc'  class='hilighted' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'>";
		$reportdata[] = "		Report from ".$row['org']." for ".$row['domain']."<br>";
		$reportdata[] = 		format_date($row['mindate'], $cookie_options['date_format']). " to ".format_date($row['maxdate'], $cookie_options['date_format'])."<br>";
		$reportdata[] = "		Policy: -Type: " . $row['policy_type'] . ". -String: " . $row['policy_string'];
		$reportdata[] = "	</div>";

		$reportdata[] = "	<div style='display:inline-block;margin-left:20px;'>";
		$reportdata[] = "		<img src='json.png' id='xml_html_img' width='30px' alt='Show Raw Report JSON' title='Show Raw Report JSON' onclick='report_data_xml_display_toggle()'>";
		$reportdata[] = "	</div>";

		$reportdata[] = "</div>";

	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<div id='report_data_xml' style='display:none; float:right; overflow-y:auto; border-left: 2px solid var(--shadow); text-align:left;padding-left: 7px;'>";
	$reportdata[] =  "<pre><code class='json'>";
	// $reportdata[] =  "<pre>";
	$reportdata[] =  json_encode(json_decode($row['raw_json']),JSON_PRETTY_PRINT) ;
	// $reportdata[] =  "</pre>";
	$reportdata[] =  "</code></pre>";
	$reportdata[] = "</div>";

	$reportdata[] = "<div id='report_data_table_div' style='overflow-y:auto;'>";
	// if ( $cookie_options['dmarc_results_matching_only'] ) {
	// 	$reportdata[] = "\"Show Only Matching Report Data records\" option is \"On\".<br><span style='color: var(--red);'>Some report records may not be displayed.</span>";
	// }
	$reportdata[] = "<table id='report_data_table' class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th title='" . $title_message . "'>Result<br />Type</th>";
	$reportdata[] = "      <th class=\"asc_triangle\" title='" . $title_message . "'>Sending<br>MTA IP</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Sending MTA <br />Host Name</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Receiving<br />IP</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Receiving MX<br />Host Name</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Receiving MX<br />HELO</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Failed Session<br />Count</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Additional<br />Information</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Failure Reason<br />Code</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";
	$reportdata[] = "  <tbody>";

	global $dbtype;
	global $dbh;

$sql = "
SELECT
  *
FROM
  tls_rptrecord
WHERE
	serial = " . $reportnumber . ( $dmarc_where ? " AND $dmarc_where" : "" ) . "
ORDER BY
	sending_mta_ip ASC
";

	$query = $dbh->query($sql);
	foreach($query as $row) {
		if ( $row['sending_mta_ip'] ) {
			$sending_mta_ip = long2ip($row['sending_mta_ip']);
		} elseif ( $row['sending_mta_ip6'] ) {
			if ( $dbtype == 'pgsql') {
				$row['sending_mta_ip6'] = stream_get_contents($row['sending_mta_ip6']);
			}
			$sending_mta_ip = inet_ntop($row['sending_mta_ip6']);
		} else {
			$sending_mta_ip = "-";
		}

		if ( $row['receiving_ip'] ) {
			$receiving_ip = long2ip($row['receiving_ip']);
		} elseif ( $row['receiving_ip6'] ) {
			if ( $dbtype == 'pgsql') {
				$row['receiving_ip6'] = stream_get_contents($row['receiving_ip6']);
			}
			$receiving_ip = inet_ntop($row['receiving_ip6']);
		} else {
			$receiving_ip = "-";
		}

		/* escape html characters after exploring binary values, which will be messed up */
		$row = array_map('html_escape', $row);

		$reportdata[] = "    <tr id='line" . $row['id'] . "' class='" . get_dmarc_result($row)['color'] . "' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this);'>";
		$reportdata[] = "      <td>". $row['result_type']. "</td>";
		$reportdata[] = "      <td>". $sending_mta_ip. "</td>";
		if ( $host_lookup ) {
			$reportdata[] = "      <td>". gethostbyaddr($sending_mta_ip). "</td>";
		} else {
			$reportdata[] = "      <td>#off#</td>";
		}
		$reportdata[] = "      <td>" . $receiving_ip . "</td>";
		// if ( $host_lookup ) {
		// 	$reportdata[] = "      <td>". gethostbyaddr($receiving_ip). "</td>";
		// } else {
		// 	$reportdata[] = "      <td>#off#</td>";
		// }
		$reportdata[] = "      <td>". $row['receiving_mx_hostname']. "</td>";
		$reportdata[] = "      <td>". $row['receiving_mx_helo ']. "</td>";
		$reportdata[] = "      <td>". $row['failed_session_count']. "</td>";
		$reportdata[] = "      <td>". $row['additional_information']. "</td>";
		$reportdata[] = "      <td>". $row['failure_reason_code']. "</td>";
		$reportdata[] = "    </tr>";

		$reportsum += $row['failed_session_count'];
	}
	$reportdata[] = "  </tbody>";
	$reportdata[] = "<tr class='sum'><td colspan='5'></td><td class='right'>Sum:</td><td class='sum'>$reportsum</td><td colspan='2'></td></tr>";
	$reportdata[] = "</table>";

	$reportdata[] = "</div>";

	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

function formatXML($raw_xml, $reportnumber) {

	global $dbh;

	$out = "";
	$html = "";

	$sql = "
	SELECT
		MIN(id) AS id_min,
		MAX(id) AS id_max
	FROM
		rptrecord
	WHERE
		serial = $reportnumber;
	";

	$query = $dbh->query($sql);

	foreach($query as $row) {
		$id_min = $row['id_min'];
		$id_max = $row['id_max'];
	}

	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($raw_xml);

	// These next few lines adding <?xml version=\"1.0\" encoding=\"UTF-8\" > and <feedback> (as well as the lines adding the closing </feedback> tag) are are very risky because they assume that the first two lines and the last line of the raw_xml are well-formed
	// Hopefully not too risky as the raw_xml has already gone through the dmarcts-parser routine that looks for bad XML.
	// If someone can code a proper way to get those lines, it would be appreciated.
	$xml_arr = explode(PHP_EOL,$raw_xml);
	$out = $xml_arr[0] . "\n" . $xml_arr[1];
	// Should return first 2 lines of xml: <?xml version=\"1.0\" encoding=\"UTF-8\"> and <feedback>
	$html = "<pre><code class='xml'>" . htmlspecialchars($out) . "</code></pre>";

	$out = $dom->saveXML($dom->getElementsByTagName("report_metadata")[0]);
	$out = htmlspecialchars($out);

	$html .= "<div id='report_metadata' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'><pre><code class='xml'>" . $out . "</code></pre></div>";

	$records = $dom->getElementsByTagName("record");
	$i = 0;
	// $i++;
	foreach ( $records as $record) {
		$out = $dom->saveXML($dom->getElementsByTagName("record")[$i]);
		$out = htmlspecialchars($out);
		$html .= "<div id='record$id_min' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'><pre><code class='xml'>";
		$html .= $out;
		$html .= "</code></pre></div>";
		$id_min++;
		$i++;
	}

	$out = $xml_arr[sizeof($xml_arr)-2];
	$out = htmlspecialchars($out);
		$html .= "<pre><code class='xml'>" . $out . "</code></pre>";

	return $html;
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

// if( $cookie_options['dmarc_results_matching_only'] && isset($_GET['dmarc']) ) {
// 	$dmarc_select=$_GET['dmarc'];
// }else{
// 	$dmarc_select= '';
// }
//
// if( $dmarc_select == "all" ) {
// 	$dmarc_select= '';
// }

// Debug
//echo "<br />D=$dom_select <br /> O=$org_select <br />";

// Make a DB Connection
// --------------------------------------------------------------------------
$dbh = connect_db($dbtype, $dbhost, $dbport, $dbname, $dbuser, $dbpass);

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

// DMARC
// dkimresult spfresult
// --------------------------------------------------------------------------
// switch ($dmarc_select) {
// 	case "DMARC_PASS": // DKIM and SPF Pass: Green
// 		$dmarc_where = "(rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass')";
// 		break;
// 	case "DMARC_PASS_AND_FAIL": // DKIM or SPF Fail: Orange
// 		$dmarc_where = "(rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail')";
// 		break;
// 	case "DMARC_FAIL": // DKIM and SPF Fail: Red
// 		$dmarc_where = "(rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail')";
// 		break;
// 	case "DMARC_OTHER_CONDITION": // Other condition: Yellow
// 		$dmarc_where = "NOT ((rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass') OR (rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail') OR (rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail'))"; // In other words, "NOT" all three other conditions
// 		break;
// 	default:
// 		break;
// }

// Include the rcount via left join, so we do not have to make an sql query
// for every single report.
// --------------------------------------------------------------------------

$sql = "
SELECT
	*
FROM
	tls_report
WHERE
	serial = " . $dbh->quote($reportid)
;

// Debug
// echo "<br /><b>Data Report sql:</b> $sql<br />";

$query = $dbh->query($sql);
foreach($query as $row) {
	if (true) {
		//add data by serial
		$reports[$row['serial']] = $row;
	}
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
echo tmpl_reportData($reportid, $reports, $hostlookup );

?>
