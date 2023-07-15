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

		$row['raw_xml'] = formatXML($row['raw_xml'], $reportnumber);
		foreach ($row as $k => $v) {
			if ($k !== 'raw_xml') {
				$row[$k] = html_escape($v);
			}
		}

		$reportdata[] = "<div id='report_desc_container' class='center reportdesc_container'>";
		$reportdata[] = "<div id='report_desc' class='center reportdesc'  class='hilighted' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'>Report from ".$row['org']." for ".$row['domain']."<br>". format_date($row['mindate'], $cookie_options['date_format']). " to ".format_date($row['maxdate'], $cookie_options['date_format'])."<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</div>";

		$reportdata[] = "<div style='display:inline-block;margin-left:20px;'><img src='xml.png' id='xml_html_img' width='30px' alt='Show Raw Report XML' title='Show Raw Report XML' onclick='report_data_xml_display_toggle()'></div>";

		$reportdata[] = "</div>";

	} else {
		return "Unknown report number!";
	}

	$reportdata[] = "<div id='report_data_xml' style='display:none; float:right; overflow-y:auto; border-left: 2px solid var(--shadow); text-align:left;padding-left: 7px;'>";
	$reportdata[] =  $row['raw_xml'];
	$reportdata[] = "</div>";

	$reportdata[] = "<div id='report_data_table_div' style='overflow-y:auto;'>";
	if ( $cookie_options['dmarc_results_matching_only'] ) {
		$reportdata[] = "\"Show Only Matching Report Data records\" option is \"On\".<br><span style='color: var(--red);'>Some report records may not be displayed.</span>";
	}
	$reportdata[] = "<table id='report_data_table' class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th class=\"asc_triangle\" title='" . $title_message . "'>IP</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Host<br />Name</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Message<br />Count</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Disposition</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>Reason</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Auth</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Domain</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Auth</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DKIM<br />Align</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>SPF<br />Align</th>";
	$reportdata[] = "      <th title='" . $title_message . "'>DMARC</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";
	$reportdata[] = "  <tbody>";

	global $dbtype;
	global $dbh;

$sql = "
SELECT
  *,
  (CASE
		WHEN dkim_align = 'fail' THEN 0
		WHEN dkim_align = 'pass' THEN 1
		ELSE 3
  END)
  +
  (CASE
		WHEN spf_align = 'fail' THEN 0
		WHEN spf_align = 'pass' THEN 1
		ELSE 3
		END)
	AS dmarc_result_min,
	(CASE
		WHEN dkim_align = 'fail' THEN 0
		WHEN dkim_align = 'pass' THEN 1
		ELSE 3
  END)
  +
  (CASE
		WHEN spf_align = 'fail' THEN 0
		WHEN spf_align = 'pass' THEN 1
		ELSE 3
	END)
	AS dmarc_result_max
FROM
  rptrecord
WHERE
	serial = " . $reportnumber . ( $dmarc_where ? " AND $dmarc_where" : "" ) . "
ORDER BY
	ip ASC
";

	$query = $dbh->query($sql);
	foreach($query as $row) {
		if ( $row['ip'] ) {
			$ip = long2ip($row['ip']);
		} elseif ( $row['ip6'] ) {
			if ( $dbtype == 'pgsql') {
				$row['ip6'] = stream_get_contents($row['ip6']);
			}
			$ip = inet_ntop($row['ip6']);
		} else {
			$ip = "-";
		}

		/* escape html characters after exploring binary values, which will be messed up */
		$row = array_map('html_escape', $row);

		$reportdata[] = "    <tr id='line" . $row['id'] . "' class='" . get_dmarc_result($row)['color'] . "' title='DMARC Result: " . get_dmarc_result($row)['result'] . "'  onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this);'>";
		$reportdata[] = "      <td>". htmlspecialchars($ip) . "</td>";
		if ( $host_lookup ) {
			$reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
		} else {
			$reportdata[] = "      <td>#off#</td>";
		}
		$reportdata[] = "      <td>". $row['rcount']. "</td>";
		$reportdata[] = "      <td>". $row['disposition']. "</td>";
		$reportdata[] = "      <td>". $row['reason']. "</td>";
		$reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['dkimresult'])['color'] . "'>". $row['dkimresult']. "</td>";
		$reportdata[] = "      <td>". $row['spfdomain']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['spfresult'])['color'] . "'>". $row['spfresult']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['dkim_align'])['color'] . "'>". $row['dkim_align']. "</td>";
		$reportdata[] = "      <td class='" . get_status_color($row['spf_align'])['color'] . "'>". $row['spf_align']. "</td>";
		$reportdata[] = "      <td>" . get_dmarc_result($row)['result'] . "</td>";
		$reportdata[] = "    </tr>";

		$reportsum += $row['rcount'];
	}
	$reportdata[] = "  </tbody>";
	$reportdata[] = "<tr class='sum'><td></td><td class='right'>Sum:</td><td class='sum'>$reportsum</td><td colspan='9'></td></tr>";
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

	// Extract and print <?xml ...> from raw_xml, if it matches the regex pattern.
    if (preg_match("/<\?xml([^?>]*)\?>/", $raw_xml, $matches)) {
        $html .= "<pre><code class='xml'>" . htmlspecialchars($matches[0]) . "</code></pre>";
    }

    // Extract and print root <feedback> from raw_xml.
    $root = $dom->firstChild;
    if (preg_match("/<". $root->localName ."([^>]*)>/", $raw_xml, $matches)) {
        $html .= "<pre><code class='xml'>" . htmlspecialchars($matches[0]) . "</code></pre>";
    }

    // Print all child nodes
    foreach ($root->childNodes as $element) {
        $out = $dom->saveXML($element);
        $out = htmlspecialchars($out);

        $elementName = $element->localName;

        // If element is a 'record', append database id to unique HTML id
        if ($elementName === "record") {
            $elementName .= $id_min;
            $id_min++;
        }

        $html .= "<div id='". $elementName ."' onmouseover='highlight(this);' onmouseout='unhighlight(this);' onclick='pin(this)'><pre><code class='xml'>" . $out . "</code></pre></div>";
	}

    // Extract closing </feedback> from raw_xml.
    if (preg_match("/<\/". $root->localName .">/", $raw_xml, $matches)) {
        $html .= "<pre><code class='xml'>" . htmlspecialchars($matches[0]) . "</code></pre>";
    }

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

if( $cookie_options['dmarc_results_matching_only'] && isset($_GET['dmarc']) ) {
	$dmarc_select=$_GET['dmarc'];
}else{
	$dmarc_select= '';
}

if( $dmarc_select == "all" ) {
	$dmarc_select= '';
}

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
switch ($dmarc_select) {
	case "DMARC_PASS": // DKIM and SPF Pass: Green
		$dmarc_where = "(rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass')";
		break;
	case "DMARC_PASS_AND_FAIL": // DKIM or SPF Fail: Orange
		$dmarc_where = "(rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail')";
		break;
	case "DMARC_FAIL": // DKIM and SPF Fail: Red
		$dmarc_where = "(rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail')";
		break;
	case "DMARC_OTHER_CONDITION": // Other condition: Yellow
		$dmarc_where = "NOT ((rptrecord.dkimresult='pass' AND rptrecord.spfresult='pass') OR (rptrecord.dkimresult='fail' OR rptrecord.spfresult='fail') OR (rptrecord.dkimresult='fail' AND rptrecord.spfresult='fail'))"; // In other words, "NOT" all three other conditions
		break;
	default:
		break;
}

// Include the rcount via left join, so we do not have to make an sql query
// for every single report.
// --------------------------------------------------------------------------

$sql = "
SELECT
	*
FROM
	report
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
