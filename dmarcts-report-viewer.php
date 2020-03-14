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

function get_status_color($row) {
	$status = "";
	if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")) {
		$status="red";
	} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail") || ($row['dkim_align'] == "fail") || ($row['spf_align'] == "fail")) {
		$status="orange";
	} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass") && ($row['dkim_align'] == "pass") && ($row['spf_align'] == "pass")) {
		$status="lime";
	} else {
		$status="yellow";
  }
  // echo $row['dkimresult'] . " - " . $row['dkim_align'] . " - " . $row['spfresult'] . " - " . $row['spf_align'] . " - " . $status . "<BR>";
	return $status;
}

function format_date($date, $format) {
	// $answer = date($format, strtotime($date));
	$answer = date('Y-m-d H:i:s', strtotime($date));
	return $answer;
};

function tmpl_reportList($allowed_reports, $host_lookup = 1, $sort_order, $dom_select = '', $org_select = '', $per_select = '', $reportid, $grp_select = '', $selectSPF = '', $selectDKIM = '') {

  // echo $grp_select;
	$reportlist[] = "";
	$reportlist[] = "<!-- Start of report list -->";

  // $reportlist[] = "<h1 class='main'>DMARC Reports" . ($dom_select == '' ? '' : " for " . htmlentities($dom_select)) . "</h1>";
  $reportlist[] = "<div class='reportgraphs'>";
  $reportlist[] = "<div class='graph_col'>";
  $reportlist[] = "<div class='graphDKIM'>";
  $reportlist[] = "<canvas id='DKIMChart' height='150' width='150'></canvas>";
  $reportlist[] = "</div>";
  $reportlist[] = "<div class='graphSPF'>";
  $reportlist[] = "<canvas id='SPFChart' height='150' width='150'></canvas>";
  $reportlist[] = "</div>";
  $reportlist[] = "<div class='graphDMARC'>";
  $reportlist[] = "<canvas id='DMARCChart' height='150' width='150'></canvas>";
  $reportlist[] = "</div>";
  $reportlist[] = "</div>";
  $reportlist[] = "</div>";
	$reportlist[] = "<div class='reportlist'>";
	$reportlist[] = "<div id= 'showxml' class='showxml'>";
	$reportlist[] = "</div>";
	$reportlist[] = "<div id= 'reportData' class='reportlist_status'>";
	$reportlist[] = "</div>";
	$reportlist[] = "<table class='reportlist'>";
	$reportlist[] = "  <thead>";
	$reportlist[] = "    <tr>";
	$reportlist[] = "      <th></th>";
	$reportlist[] = "      <th>Date Range</th>";
	$reportlist[] = "      <th>Domain</th>";
	$reportlist[] = "      <th>Reporting Organization</th>";
	$reportlist[] = "      <th>Report ID</th>";
	$reportlist[] = "      <th>Messages</th>";
	$reportlist[] = "    </tr>";
	$reportlist[] = "  </thead>";

	$reportlist[] = "  <tbody>";
	$reportsum    = 0;

  if (isset($allowed_reports[BySerial])) {
  foreach ($allowed_reports[BySerial] as $row) {
		$row = array_map('htmlspecialchars', $row);
		$date_output_format = "r";
		$reportlist[] = "    <tr id='" . $row['serial'] . "'>";
		$reportlist[] = "      <td class='right'><span class=\"circle_".get_status_color($row)."\"></span></td>";
		$reportlist[] = "      <td class='right'>". format_date($row['mindate'], $date_output_format). " - ". format_date($row['maxdate'], $date_output_format). "</td>";
    if ($grp_select == 'dom') {
      $url = "'?report=-1" 
        . ( $host_lookup ? "&hostlookup=1" : "&hostlookup=0" ) 
        . ( $sort_order ? "&sortorder=1" : "&sortorder=0" ) 
        . ($grp_select == '' ? '' : "&g=" . urlencode($grp_select)) 
        . ($dom_select == '' ? '' : "&d=" . urlencode($dom_select)) 
        . ($org_select == '' ? '' : "&o=" . urlencode($org_select)) 
        . ($selectSPF == '' ? '' : "&spf=" . urlencode($selectSPF)) 
        . ($selectDKIM == '' ? '' : "&dkim=" . urlencode($selectDKIM)) 
        . ($per_select == '' ? '' : "&p=" . urlencode($per_select)) . "'"; 
      $reportlist[] = "      <td class='center'>"
        . "<a href='#" . $row['serial'] . "' id='" . $row['domain'] ."' OnClick=\"$('#'+" . $row['serial'] . ").css('background-color', 'lightgrey'); reportData(" . -1 . ", $url);\">"
        . $row['domain']. "</a></td>";
    } else {
      $reportlist[] = "      <td class='center'>". $row['domain']. "</td>";
    }
    if ($grp_select == 'org') {
      $url = "'?report=-1" 
        . ( $host_lookup ? "&hostlookup=1" : "&hostlookup=0" ) 
        . ( $sort_order ? "&sortorder=1" : "&sortorder=0" ) 
        . ($grp_select == '' ? '' : "&g=" . urlencode($grp_select)) 
        . ($dom_select == '' ? '' : "&d=" . urlencode($dom_select)) 
        . ($row['org'] == '' ? '' : "&o=" . urlencode($row['org'])) 
        . ($selectSPF == '' ? '' : "&spf=" . urlencode($selectSPF)) 
        . ($selectDKIM == '' ? '' : "&dkim=" . urlencode($selectDKIM)) 
        . ($per_select == '' ? '' : "&p=" . urlencode($per_select)) . "'"; 
      $reportlist[] = "      <td class='center'>"
        . "<a href='#" . $row['serial'] . "' id='" . $row['org'] ."' OnClick=\"$('#'+" . $row['serial'] . ").css('background-color', 'lightgrey'); reportData(" . -1 . ", $url);\">"
        . $row['org']. "</a></td>";
    } elseif ($grp_select == 'dom') {
      $reportlist[] = "      <td class='center'>-</td>";
    } else {
      $reportlist[] = "      <td class='center'>". $row['org']. "</td>";
    }
    if ($grp_select == '') {
      $url = "'?report=" . $row['serial']
        . ( $host_lookup ? "&hostlookup=1" : "&hostlookup=0" ) 
        . ( $sort_order ? "&sortorder=1" : "&sortorder=0" ) 
        . ($grp_select == '' ? '' : "&g=" . urlencode($grp_select)) 
        . ($dom_select == '' ? '' : "&d=" . urlencode($dom_select)) 
        . ($org_select == '' ? '' : "&o=" . urlencode($org_select)) 
        . ($selectSPF == '' ? '' : "&spf=" . urlencode($selectSPF)) 
        . ($selectDKIM == '' ? '' : "&dkim=" . urlencode($selectDKIM)) 
        . ($per_select == '' ? '' : "&p=" . urlencode($per_select)) . "'"; 
      $reportlist[] = "      <td class='center'>"
        . "<a href='#" . $row['serial'] . "' OnClick=\"reportData(" . $row['serial'] . ", $url);\">"
        . $row['reportid']. "</a>"
        . " <a href='#" . $row['serial'] . "' OnClick=\"showXML(" . $row['serial'] . ");\">"
        . "<img alt='View Raw XML Report' class='view' src='./images/loupe2.png'></a></td>";
    } else {
      $reportlist[] = "      <td class='center'>-</td>";
    }
		$reportlist[] = "      <td class='center'>". number_format($row['rcount']+0,0). "</td>";
		$reportlist[] = "    </tr>";
		$reportsum += $row['rcount'];
  }
  } # Fin du Contrôle si Tableau Vide
	$reportlist[] = "<tr class='sum'><td></td><td></td><td></td><td></td><td class='right' style='text-align: right; border-right: 0;'>Sum:</td><td class='center'>".number_format($reportsum,0)."</td></tr>";
	$reportlist[] = "  </tbody>";

	$reportlist[] = "</table>";
	$reportlist[] = "</div>";

	$reportlist[] = "<!-- End of report list -->";
	$reportlist[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportlist);
}

function tmpl_reportData($reportnumber, $allowed_reports, $host_lookup = 1, $sort_order, $org_select, $domain, $where2) {
	if (! is_numeric($reportnumber)) {
		return "";
	}

	$reportdata[] = "";
	$reportdata[] = "<!-- Start of report rata -->";
	$reportsum    = 0;
	if ($reportnumber == -1 && isset($allowed_reports[BySerial])) {
    $first = reset($allowed_reports[BySerial]);
    $row = end($allowed_reports[BySerial]);
		// $row = $allowed_reports[BySerial][$reportnumber];
		$row = array_map('htmlspecialchars', $row);
    $reportdata[] = "<a id='rpt".$reportnumber."'></a>";
    if ($org_select == 'dom') {
      $reportdata[] = "<div class='center reportdesc'><p> Report for ".$row['domain']."<br>(". format_date($first['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</p></div>";
    } else {
      $reportdata[] = "<div class='center reportdesc'><p> Report from ".$row['org']." for ".$row['domain']."<br>(". format_date($first['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</p></div>";
    }
  } elseif (isset($allowed_reports[BySerial][$reportnumber])) {
		$row = $allowed_reports[BySerial][$reportnumber];
		$row = array_map('htmlspecialchars', $row);
		$reportdata[] = "<a id='rpt".$reportnumber."'></a>";
		$reportdata[] = "<div class='center reportdesc'><p> Report from ".$row['org']." for ".$row['domain']."<br>(". format_date($row['mindate'], "r" ). " - ".format_date($row['maxdate'], "r" ).")<br> Policies: adkim=" . $row['policy_adkim'] . ", aspf=" . $row['policy_aspf'] .  ", p=" . $row['policy_p'] .  ", sp=" . $row['policy_sp'] .  ", pct=" . $row['policy_pct'] . "</p></div>";
	} else {
		return "Unknown report number!<BR>";
	}

  // $reportdata[] = "<div class='print'><div class='print_img'><a href=\"javascript: document.getElementById(&quot;CONTENT&quot;).contentWindow.print();\"><img class='print_img' src=\"./images/print.png\"></a></div></div>";
  $reportdata[] = "<div class='print'><div class='print_img'><a href=\"javascript: printData();\"><img class='print_img' src=\"/images/print.png\"></a></div></div>";
	$reportdata[] = "<table class='reportdata'>";
	$reportdata[] = "  <thead>";
	$reportdata[] = "    <tr>";
  $reportdata[] = "      <th rowspan='2'>IP Address</th>";
  if ($host_lookup == 1) {
    $reportdata[] = "      <th rowspan='2'>Host Name</th>";
  }
	$reportdata[] = "      <th rowspan='2'>Message<BR>Count</th>";
	$reportdata[] = "      <th rowspan='2'>Disposition</th>";
	$reportdata[] = "      <th rowspan='2'>Reason</th>";
	$reportdata[] = "      <th rowspan='2'>DKIM<BR>Domain</th>";
	$reportdata[] = "      <th colspan='2'>DKIM Result</th>";
	$reportdata[] = "      <th rowspan='2'>SPF<BR>Domain</th>";
	$reportdata[] = "      <th colspan='2'>SPF Result</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "    <tr>";
	$reportdata[] = "      <th>Result</th>";
	$reportdata[] = "      <th>Alignment</th>";
	$reportdata[] = "      <th>Result</th>";
	$reportdata[] = "      <th>Alignment</th>";
	$reportdata[] = "    </tr>";
	$reportdata[] = "  </thead>";

	$reportdata[] = "  <tbody>";

  $reportnumber2= '';
	global $mysqli;
  if ($reportnumber == -1 && isset($allowed_reports[BySerial])) {
    foreach( $allowed_reports[BySerial] as $reports ) {
      $reportnumber2 .= $reports['serial'] . ", ";
    }
    $reportnumber2 = rtrim($reportnumber2, ", ");
	  $sql = "SELECT *, SUM(rcount) AS rcount FROM rptrecord where serial IN(" . $reportnumber2 . ") $where2 GROUP BY ip, ip6, disposition, reason, dkimdomain, dkimresult, spfdomain, spfresult, dkim_align, spf_align, identifier_hfrom";
    $query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
  } else {
	  $sql = "SELECT * FROM rptrecord where serial = $reportnumber $where2";
    $query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
  }
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

	  $reportdata[] = "    <tr class='".$status."'>";
	  $reportdata[] = "      <td>". $ip. "</td>";
	  if ( $host_lookup == 1 ) {
		  $reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
	  } else {
		  # $reportdata[] = "      <td>#off#</td>";
	  }
	  $reportdata[] = "      <td>". $row['rcount']. "</td>";
    $reportdata[] = "      <td>". $row['disposition']. "</td>";
    if (($row['reason'] == "") && ($status != "lime")) {
      if (($row['identifier_hfrom'] == $row['dkimdomain']) || ($row['identifier_hfrom'] == $row['spfdomain'])) {
        $reportdata[] = "      <td>header_from</td>";
      } else {
        $reportdata[] = "      <td></td>";
      }
    } else {
      $reportdata[] = "      <td>". $row['reason']. "</td>";
    }
    if (($row['dkimresult'] == "pass") && ($row['dkim_align'] == "pass")) {
      $reportdata[] = "      <td class='lime'>". $row['dkimdomain']. "</td>";
      $reportdata[] = "      <td class='lime'>". $row['dkimresult']. "</td>";
      $reportdata[] = "      <td class='lime'>". $row['dkim_align']. "</td>";
    } else {
      if ($row['dkimdomain'] == "") {
        $reportdata[] = "      <td></td>";
        $reportdata[] = "      <td>". $row['dkimresult']. "</td>";
        $reportdata[] = "      <td>". $row['dkim_align']. "</td>";
      } else {
        $reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
        $reportdata[] = "      <td>". $row['dkimresult']. "</td>";
        $reportdata[] = "      <td>". $row['dkim_align']. "</td>";
      }
    }
    if (($row['spfresult'] == "pass") && ($row['spf_align'] == "pass")) {
      if ($row['spfdomain'] == $domain) {
        $reportdata[] = "      <td class='lime'>". $row['spfdomain']. "</td>";
      } else {
        $reportdata[] = "      <td>". $row['spfdomain']. "</td>";
      }
      $reportdata[] = "      <td class='lime'>". $row['spfresult']. "</td>";
      $reportdata[] = "      <td class='lime'>". $row['spf_align']. "</td>";
    } else {
	    $reportdata[] = "      <td>". $row['spfdomain']. "</td>";
      $reportdata[] = "      <td>". $row['spfresult']. "</td>";
      $reportdata[] = "      <td>". $row['spf_align']. "</td>";
    }
	  $reportdata[] = "    </tr>";
 
	  $reportsum += $row['rcount'];
  }
  if ($host_lookup == 1) {
    $reportdata[] = "<tr><td></td><td></td><td>$reportsum</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
  } else {
    $reportdata[] = "<tr><td></td><td>$reportsum</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
  }
	$reportdata[] = "  </tbody>";
	$reportdata[] = "</table>";

	$reportdata[] = "<!-- End of report rata -->";
	$reportdata[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportdata);
}

function tmpl_Recordpage ($body, $recordid, $host_lookup = 1, $sort_order, $dom_select, $domains = array(), $cssfile, $org_select, $orgs = array(), $per_select, $periods = array(), $grp_select, $selectSPF, $selectDKIM, $datachart, $datachart2, $datachart3, $where2) {
  $html[] = "    <link rel='stylesheet' type='text/css' href='./css/$cssfile'>";
  $html[] = "    <link rel='stylesheet' type='text/css' href='./css/print-$cssfile' media='print'>";

  # add body
  #--------------------------------------------------------------------------
  $html[] = $body;
  
  # Close Button
  #--------------------------------------------------------------------------
	$html[] = "  <a class='close' onclick=\"$('#reportData').hide();\">Close</a>";

	return implode("\n",$html);
}

function tmpl_page ($body, $reportid, $host_lookup = 1, $sort_order, $dom_select, $domains = array(), $cssfile, $org_select, $orgs = array(), $per_select, $periods = array(), $grp_select, $selectSPF, $selectDKIM, $datachart, $datachart2, $datachart3, $where2 ) {

	$html       = array();
        $url_hswitch = ( $reportid ? "?report=$reportid&hostlookup=" : "?hostlookup=" )
                . ($host_lookup ? "0" : "1" )
                . ( "&sortorder=" ) . ($sort_order)
                . (isset($selectSPF) && $selectSPF <> "" ? "&spf=$selectSPF" : "" )
                . (isset($selectDKIM) && $selectDKIM <> "" ? "&dkim=$selectDKIM" : "" )
                . (isset($dom_select) && $dom_select <> "" ? "&d=$dom_select" : "" )
                . (isset($grp_select) && $grp_select <> "" ? "&g=$grp_select" : "" )
                ;
        $url_dswitch = "?hostlookup=" . ($host_lookup ? "1" : "0" ) . "&sortorder=" . ($sort_order); // drop selected report on domain switch
        $url_sswitch = ( $reportid ? "?report=$reportid&hostlookup=" : "?hostlookup=" )
                . ($host_lookup)
                . ( "&sortorder=" ) . ($sort_order ? "0" : "1" )
                . (isset($selectSPF) && $selectSPF <> "" ? "&spf=$selectSPF" : "" )
                . (isset($selectDKIM) && $selectDKIM <> "" ? "&dkim=$selectDKIM" : "" )
                . (isset($dom_select) && $dom_select <> "" ? "&d=$dom_select" : "" )
                . (isset($grp_select) && $grp_select <> "" ? "&g=$grp_select" : "" )
                ;

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>DMARC Report Viewer</title>";
  $html[] = "    <link rel='stylesheet' href='./css/$cssfile'>";
  $html[] = "    <script type='text/javascript' src='./scripts/jquery-3.3.1.min.js'></script>";
  $html[] = "    <script type='text/javascript' src='./scripts/Chart.2.5.0.min.js'></script>";
  $html[] = "    <script type='text/javascript' src='./scripts/dmarcts-report-viewer.js'></script>";
	$html[] = "  </head>";

	$html[] = "  <body>";
	$html[] = "<div class='reporttitle'><h1 class='main'>DMARC Reports" . ($dom_select == '' ? '' : " for " . htmlentities($dom_select)) . "</h1></div>";
	
	
  # optionblock form
  #--------------------------------------------------------------------------
	$html[] = "    <div class='optionblock'><form action=\"?\" method=\"post\">";
	
	
  # handle host lookup (on/off should not reset selected report)
  #--------------------------------------------------------------------------
  $html[] = "<div><h1 class='filters'>Filters</h1></div><div class='options'><span class='optionlabel'>Hostname(s):</span> <input type=\"radio\" name=\"selHostLookup\" value=\"1\" onchange=\"this.form.submit()\"" . ($host_lookup ? " checked=\"checked\"" : "" ) . "> on<input type=\"radio\" name=\"selHostLookup\" value=\"0\" onchange=\"this.form.submit()\"" . ($host_lookup ? "" : " checked=\"checked\"" ) . "> off</div>";	
  
  
  # handle sort direction
  #--------------------------------------------------------------------------
  $html[] = "<div class='options'><span class='optionlabel'>Sort order:</span> <input type=\"radio\" name=\"selOrder\" value=\"1\" onchange=\"this.form.submit()\"" . ($sort_order ? " checked=\"checked\"" : "" ) . "> ascending<input type=\"radio\" name=\"selOrder\" value=\"0\" onchange=\"this.form.submit()\"" . ($sort_order ? "" : " checked=\"checked\"" ) . "> decending</div>";	
  
  
  # handle SPF status
  #--------------------------------------------------------------------------
  $html[] = "<div class='options'><span class='optionlabel'>SPF Status:</span> <input type=\"radio\" name=\"selSPF\" value=\"pass\" onchange=\"this.form.submit()\"" . (($selectSPF == "pass") ? " checked=\"checked\"" : "") . "> Pass<input type=\"radio\" name=\"selSPF\" value=\"fail\" onchange=\"this.form.submit()\"" . (($selectSPF == "fail") ? " checked=\"checked\"" : "") . "> Fail<input type=\"radio\" name=\"selSPF\" value=\"other\" onchange=\"this.form.submit()\"" . (($selectSPF == "other") ? " checked=\"checked\"" : "") . "> Other<input type=\"radio\" name=\"selSPF\" value=\"\" onchange=\"this.form.submit()\"" . ($selectSPF ? "" : " checked=\"checked\"" ) . "> [All]</div>";	
  
  
  # handle DKIM status
  #--------------------------------------------------------------------------
  $html[] = "<div class='options'><span class='optionlabel'>DKIM Status:</span> <input type=\"radio\" name=\"selDKIM\" value=\"pass\" onchange=\"this.form.submit()\"" . (($selectDKIM == "pass") ? " checked=\"checked\"" : "") . "> Pass<input type=\"radio\" name=\"selDKIM\" value=\"fail\" onchange=\"this.form.submit()\"" . (($selectDKIM == "fail") ? " checked=\"checked\"" : "") . "> Fail<input type=\"radio\" name=\"selDKIM\" value=\"other\" onchange=\"this.form.submit()\"" . (($selectDKIM == "other") ? " checked=\"checked\"" : "") . "> Other<input type=\"radio\" name=\"selDKIM\" value=\"\" onchange=\"this.form.submit()\"" . ($selectDKIM ? "" : " checked=\"checked\"" ) . "> [All]</div>";	
  
  
  # handle Group direction
  #--------------------------------------------------------------------------
  $html[] = "<div class='options'><span class='optionlabel'>Group by:</span>";	
  $html[] = "<select name=\"selGroup\" id=\"selGroup\" onchange=\"this.form.submit()\">";
  if( $grp_select != "" ) {
    $html[] = "<option value=\"all\">[all]</option>";
    if ($grp_select == "org" ) {
      $html[] = "<option value=\"dom\">Reporting for Domain</option>";
      $html[] = "<option selected=\"selected\" value=\"org\">Reporting Organization</option>";
    } else {
      $html[] = "<option selected=\"selected\" value=\"dom\">Reporting for Domain</option>";
      $html[] = "<option value=\"org\">Reporting Organization</option>";
    }
  } else {
    $html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";
    $html[] = "<option value=\"dom\">Reporting for Domain</option>";
    $html[] = "<option value=\"org\">Reporting Organization</option>";
  }
  $html[] = "</select>";
  $html[] = "</div>";
  
  
  # handle domains
  #--------------------------------------------------------------------------
  if ( count( $domains ) > 1 ) {
    $html[] = "<div class='options'><span class='optionlabel'>Domain(s):</span>";
    $html[] = "<select name=\"selDomain\" id=\"selDomain\" onchange=\"this.form.submit()\">";
    if( $dom_select != "" ) {
      $html[] = "<option value=\"all\">[all]</option>";
    } else {
      $html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";
    }
    foreach( $domains as $d) {
      $arg = "";
      if( $d == $dom_select ) {
        $arg =" selected=\"selected\"";
      }
      $html[] = "<option $arg value=\"$d\">$d</option>";
    }
    $html[] = "</select>";
    $html[] = "</div>";
  }


  # handle orgs
  #--------------------------------------------------------------------------
  if ( count( $orgs ) > 0 ) {
    $html[] = "<div class='options'><span class='optionlabel'>Organisation(s):</span>";
    $html[] = "<select name=\"selOrganisation\" id=\"selOrganisation\" onchange=\"this.form.submit()\">";
    if( $org_select != "" ) {
      $html[] = "<option value=\"all\">[all]</option>";
    } else {
      $html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";
    }
    foreach( $orgs as $o) {
      $arg = "";
      if( $o == $org_select ) {
        $arg =" selected=\"selected\"";
      }
      $html[] = "<option $arg value=\"$o\">" . ( strlen( $o ) > 25 ? substr( $o, 0, 22) . "..." : $o ) . "</option>";
    }
    $html[] = "</select>";
    $html[] = "</div>";
  }
  
  
  #--------------------------------------------------------------------------
  # handle period
  #--------------------------------------------------------------------------
  if ( count( $periods ) > 0 ) {
    $html[] = "<div class='options'><span class='optionlabel'>Time:</span>";
    $html[] = "<select name=\"selPeriod\" id=\"selPeriod\" onchange=\"this.form.submit()\">";
    if( $org_select != "" ) {
      $html[] = "<option value=\"all\">[all]</option>";
    } else {
      $html[] = "<option selected=\"selected\" value=\"all\">[all]</option>";
    }
    foreach( $periods as $p) {
      $arg = "";
      if( $p == $per_select ) {
        $arg =" selected=\"selected\"";
      }
      $html[] = "<option $arg value=\"$p\">$p</option>";
    }
    $html[] = "</select>";
    $html[] = "</div>";
  }
  
  
  # end optionblock
  #--------------------------------------------------------------------------
  $html[] = "</div>";
  $html[] = "</form></div>";   

  
  # add body
  #--------------------------------------------------------------------------
  $html[] = $body;

  
  # add Graph
  #--------------------------------------------------------------------------
  $html[] = "<script type='text/javascript'>";
  $html[] = "  var ctx = document.getElementById('DKIMChart').getContext('2d');";
  $html[] = "  var myChart = new Chart(ctx, {";
  $html[] = "    type: 'pie',";
  $html[] = "    $datachart,";
  $html[] = "    options: {";
  $html[] = "      events: false,";
  $html[] = "      cutoutPercentage : 25,";
  $html[] = "      tooltips: {";
  $html[] = "        enabled: false";
  $html[] = "      },";
  $html[] = "      hover: {";
  $html[] = "        animationDuration: 0,";
  $html[] = "      },";
  $html[] = "      rotation: -0.5 * Math.PI,";
  $html[] = "      animation: {";
  $html[] = "        duration: 2000,";
  $html[] = "        animationSteps: 10000,";
  $html[] = "        onComplete: function() {";
  $html[] = "          var chartInstance = this.chart,";
  $html[] = "          ctx = chartInstance.ctx;";
  $html[] = "";
  $html[] = "          ctx.font = Chart.helpers.fontString(11, 'bold', Chart.defaults.global.defaultFontFamily);";
  $html[] = "          ctx.textAlign = 'center';";
  $html[] = "          ctx.textBaseline = 'middle';";
  $html[] = "";
  $html[] = "          this.data.datasets.forEach(function(dataset) {";
  $html[] = "            for (var i = 0; i < dataset.data.length; i++) {";
  $html[] = "              var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,";
  $html[] = "              total = dataset._meta[Object.keys(dataset._meta)[0]].total,";
  $html[] = "              mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius)/2,";
  $html[] = "              start_angle = model.startAngle,";
  $html[] = "              end_angle = model.endAngle,";
  $html[] = "              angle = end_angle - start_angle,";
  $html[] = "              mid_angle = start_angle + (end_angle - start_angle)/2;";
  $html[] = "";
  $html[] = "              var x = mid_radius * Math.cos(mid_angle);";
  $html[] = "              var y = mid_radius * Math.sin(mid_angle);";
  $html[] = "";
  $html[] = "              ctx.fillStyle = '#fff';";
  $html[] = "              if ((i == 1) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#28AD4E';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else if ((i == 0) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#D9534F';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else {";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              }";
  $html[] = "            }";
  $html[] = "          });";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      legend: {";
  $html[] = "        display: false,";
  $html[] = "        reverse: false,";
  $html[] = "        position: 'right',";
  $html[] = "        labels: {";
  $html[] = "          fontSize: 8";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      title: {";
  $html[] = "        display: true,";
  $html[] = "        text: 'DKIM Compliance',";
  $html[] = "        fontSize: 12";
  $html[] = "      }";
  $html[] = "    }";
  $html[] = "  });";
  $html[] = "</script>";
  $html[] = "";
  $html[] = "<script type='text/javascript'>";
  $html[] = "  var ctx = document.getElementById('SPFChart').getContext('2d');";
  $html[] = "  var myChart = new Chart(ctx, {";
  $html[] = "    type: 'pie',";
  $html[] = "    $datachart2,";
  $html[] = "    options: {";
  $html[] = "      events: false,";
  $html[] = "      cutoutPercentage : 25,";
  $html[] = "      tooltips: {";
  $html[] = "        enabled: false";
  $html[] = "      },";
  $html[] = "      hover: {";
  $html[] = "        animationDuration: 0,";
  $html[] = "      },";
  $html[] = "      rotation: -0.5 * Math.PI,";
  $html[] = "      animation: {";
  $html[] = "        duration: 2000,";
  $html[] = "        animationSteps: 10000,";
  $html[] = "        onComplete: function() {";
  $html[] = "          var chartInstance = this.chart,";
  $html[] = "          ctx = chartInstance.ctx;";
  $html[] = "";
  $html[] = "          ctx.font = Chart.helpers.fontString(11, 'bold', Chart.defaults.global.defaultFontFamily);";
  $html[] = "          ctx.textAlign = 'center';";
  $html[] = "          ctx.textBaseline = 'middle';";
  $html[] = "";
  $html[] = "          this.data.datasets.forEach(function(dataset) {";
  $html[] = "            for (var i = 0; i < dataset.data.length; i++) {";
  $html[] = "              var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,";
  $html[] = "              total = dataset._meta[Object.keys(dataset._meta)[0]].total,";
  $html[] = "              mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius)/2,";
  $html[] = "              start_angle = model.startAngle,";
  $html[] = "              end_angle = model.endAngle,";
  $html[] = "              angle = end_angle - start_angle,";
  $html[] = "              mid_angle = start_angle + (end_angle - start_angle)/2;";
  $html[] = "";
  $html[] = "              var x = mid_radius * Math.cos(mid_angle);";
  $html[] = "              var y = mid_radius * Math.sin(mid_angle);";
  $html[] = "";
  $html[] = "              ctx.fillStyle = '#fff';";
  $html[] = "              if ((i == 1) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#28AD4E';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else if ((i == 0) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#D9534F';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else {";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              }";
  $html[] = "            }";
  $html[] = "          });";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      legend: {";
  $html[] = "        display: false,";
  $html[] = "        reverse: false,";
  $html[] = "        position: 'right',";
  $html[] = "        labels: {";
  $html[] = "          fontSize: 8";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      title: {";
  $html[] = "        display: true,";
  $html[] = "        text: 'SPF Compliance',";
  $html[] = "        fontSize: 12";
  $html[] = "      }";
  $html[] = "    }";
  $html[] = "  });";
  $html[] = "</script>";
  $html[] = "";
  $html[] = "<script type='text/javascript'>";
  $html[] = "  var ctx = document.getElementById('DMARCChart').getContext('2d');";
  $html[] = "  var myChart = new Chart(ctx, {";
  $html[] = "    type: 'pie',";
  $html[] = "    $datachart3,";
  $html[] = "    options: {";
  $html[] = "      events: false,";
  $html[] = "      cutoutPercentage : 25,";
  $html[] = "      tooltips: {";
  $html[] = "        enabled: false";
  $html[] = "      },";
  $html[] = "      hover: {";
  $html[] = "        animationDuration: 0,";
  $html[] = "      },";
  $html[] = "      rotation: -0.5 * Math.PI,";
  $html[] = "      animation: {";
  $html[] = "        duration: 2000,";
  $html[] = "        animationSteps: 10000,";
  $html[] = "        onComplete: function() {";
  $html[] = "          var chartInstance = this.chart,";
  $html[] = "          ctx = chartInstance.ctx;";
  $html[] = "";
  $html[] = "          ctx.font = Chart.helpers.fontString(11, 'bold', Chart.defaults.global.defaultFontFamily);";
  $html[] = "          ctx.textAlign = 'center';";
  $html[] = "          ctx.textBaseline = 'middle';";
  $html[] = "";
  $html[] = "          this.data.datasets.forEach(function(dataset) {";
  $html[] = "            for (var i = 0; i < dataset.data.length; i++) {";
  $html[] = "              var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,";
  $html[] = "              total = dataset._meta[Object.keys(dataset._meta)[0]].total,";
  $html[] = "              mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius)/2,";
  $html[] = "              start_angle = model.startAngle,";
  $html[] = "              end_angle = model.endAngle,";
  $html[] = "              angle = end_angle - start_angle,";
  $html[] = "              mid_angle = start_angle + (end_angle - start_angle)/2;";
  $html[] = "";
  $html[] = "              var x = mid_radius * Math.cos(mid_angle);";
  $html[] = "              var y = mid_radius * Math.sin(mid_angle);";
  $html[] = "";
  $html[] = "              ctx.fillStyle = '#fff';";
  $html[] = "              if ((i == 1) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#28AD4E';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else if ((i == 0) && (angle == 0)) {";
  $html[] = "                ctx.fillStyle = '#D9534F';";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              } else {";
  $html[] = "                ctx.fillText(dataset.data[i], model.x + x, model.y + y);";
  $html[] = "              }";
  $html[] = "            }";
  $html[] = "          });";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      legend: {";
  $html[] = "        display: false,";
  $html[] = "        reverse: false,";
  $html[] = "        position: 'right',";
  $html[] = "        labels: {";
  $html[] = "          fontSize: 8";
  $html[] = "        }";
  $html[] = "      },";
  $html[] = "      title: {";
  $html[] = "        display: true,";
  $html[] = "        text: 'DMARC Compliance',";
  $html[] = "        fontSize: 12";
  $html[] = "      }";
  $html[] = "    }";
  $html[] = "  });";
  $html[] = "</script>";

  
  # footer
  #--------------------------------------------------------------------------
	$html[] = "  <div class='footer'>Brought to you by <a href='http://www.techsneeze.com'>TechSneeze.com</a> - <a href='mailto:dave@techsneeze.com'>dave@techsneeze.com</a><br>";
	$html[] = "  Modified and brought to you by <a href='https://cert.civis.net'>CIVIS.net</a> - <a href='mailto:security@civis.net'>security@civis.net</a></div>";
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
$grp_select= '';
$org_select= '';
$per_select= '';
$selectSPF= '';
$selectDKIM= '';
$where = '';
$where2 = '';

if(!isset($dbport)) {
  $dbport="3306";
}
if(!isset($cssfile)) {
  $cssfile="default.css";
}

// parameters of by GET / POST - POST has priority
// --------------------------------------------------------------------------
if(isset($_GET['report']) && is_numeric($_GET['report'])){
  $reportid=$_GET['report']+0;
}elseif(!isset($_GET['report'])){
  $reportid=false;
}else{
  die('Invalid Report ID');
}
if(isset($_POST['selHostLookup']) && is_numeric($_POST['selHostLookup'])){
  $hostlookup=$_POST['selHostLookup']+0;
} elseif(isset($_GET['hostlookup']) && is_numeric($_GET['hostlookup'])){
  $hostlookup=$_GET['hostlookup']+0;
}elseif(!isset($_GET['hostlookup'])){
  $hostlookup= isset( $default_lookup ) ? $default_lookup : 1;
}else{
  die('Invalid hostlookup flag');
}
if(isset($_POST['selOrder']) && is_numeric($_POST['selOrder'])){
  $sortorder=$_POST['selOrder']+0;
} elseif(isset($_GET['sortorder']) && is_numeric($_GET['sortorder'])){
  $sortorder=$_GET['sortorder']+0;
}elseif(!isset($_GET['sortorder'])){
  $sortorder= isset( $default_sort ) ? $default_sort : 1;
}else{
  die('Invalid sortorder flag');
}
if(isset($_POST['selSPF'])){
  $selectSPF=$_POST['selSPF'];
} elseif(isset($_GET['spf'])){
  $selectSPF=$_GET['spf'];
} else {
  $selectSPF= '';
}
if(isset($_POST['selDKIM'])){
  $selectDKIM=$_POST['selDKIM'];
} elseif(isset($_GET['dkim'])){
  $selectDKIM=$_GET['dkim'];
} else {
  $selectDKIM= '';
}
if(isset($_POST['selGroup'])){
  $grp_select=$_POST['selGroup'];
} elseif(isset($_GET['g'])){
  $grp_select=$_GET['g'];
}else{
  $grp_select= '';
}
if( $grp_select == "all" ) {
  $grp_select= '';
}
if(isset($_POST['selDomain'])){
  $dom_select=$_POST['selDomain'];
} elseif(isset($_GET['d'])){
  $dom_select=$_GET['d'];
}else{
  $dom_select= '';
}
if( $dom_select == "all" ) {
  $dom_select= '';
}
if(isset($_POST['selOrganisation'])){
  $org_select=$_POST['selOrganisation'];
} elseif(isset($_GET['o'])){
  $org_select=$_GET['o'];
}else{
  $org_select= '';
}
if( $org_select == "all" ) {
  $org_select= '';
}
if(isset($_POST['selPeriod'])){
  $per_select=$_POST['selPeriod'];
} elseif(isset($_GET['p'])){
  $per_select=$_GET['p'];
}else{
  // $per_select= date( 'Y-m' );
  $per_select= '';
}
if( $per_select == "all" ) {
  $per_select= '';
}
// Debug
//echo "D=$dom_select <br /> O=$org_select <br /> P=$per_select <br />";
//echo "G=$grp_select <br /> O=$org_select <br />";

// Make a MySQL Connection using mysqli
// --------------------------------------------------------------------------
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

// get group
// --------------------------------------------------------------------------
// $grp_select


// get all domains reported
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT domain FROM `report` ORDER BY domain";
$domains= array();
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
  $domains[] = $row['domain'];
}
if( $dom_select <> '' && array_search($dom_select, $domains) === FALSE ) {
  $dom_select = '';
}
if( $dom_select <> '' ) {
  $where .= ( $where <> '' ? " AND" : " WHERE" ) . " domain='" . $mysqli->real_escape_string($dom_select) . "'";
} 

// get organisations
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT org FROM `report`" . ($dom_select == '' ? "" : "WHERE `domain`='" . $mysqli->real_escape_string($dom_select). "'" ) . " ORDER BY org";
$orgs= array();
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
  $orgs[] = $row['org'];
}
if( $org_select <> '' && array_search($org_select, $orgs) === FALSE ) {
  $org_select = '';
}
if( $org_select <> '' ) {
  $where .= ( $where <> '' ? " AND" : " WHERE" ) . " org='" . $mysqli->real_escape_string($org_select) . "'";
} 

// get period
// --------------------------------------------------------------------------
$sql="SELECT DISTINCT DISTINCT year(mindate) as year, month(mindate) as month FROM `report` $where ORDER BY year desc,month desc";
$periods= array();
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
  $periods[] = sprintf( "%'.04d-%'.02d", $row['year'], $row['month'] );
}
if( $per_select <> '' && array_search($per_select, $periods) === FALSE ) {
  $per_select = '';
}
if( $per_select <> '' ) {
  $ye = substr( $per_select, 0, 4) + 0;
  $mo = substr( $per_select, 5, 2) + 0;
  $where .= ( $where <> '' ? " AND" : " WHERE" ) . " year(mindate)=$ye and month(mindate)=$mo ";

} 

// get SPF Status
// --------------------------------------------------------------------------
if( $selectSPF <> '' ) {
  if ($selectSPF == "pass") {
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " rptrecord.spfresult = 'pass' AND rptrecord.spf_align = 'pass'";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " rptrecord.spfresult = 'pass' AND rptrecord.spf_align = 'pass'";
  } elseif ($selectSPF == "fail") {
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " rptrecord.spfresult = 'fail' AND rptrecord.spf_align = 'fail'";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " rptrecord.spfresult = 'fail' AND rptrecord.spf_align = 'fail'";
  } else {
    // $where .= ( $where <> '' ? " AND" : " WHERE" ) . " ((rptrecord.spfresult != 'pass' OR rptrecord.spfresult != 'fail') AND (rptrecord.spf_align != 'pass' OR rptrecord.spf_align != 'fail'))";
    // $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " ((rptrecord.spfresult != 'pass' OR rptrecord.spfresult != 'fail') AND (rptrecord.spf_align != 'pass' OR rptrecord.spf_align != 'fail'))";
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " ((rptrecord.spfresult NOT IN ('pass', 'fail')) AND (rptrecord.spf_align NOT IN ('pass', 'fail')))";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " ((rptrecord.spfresult NOT IN ('pass', 'fail')) AND (rptrecord.spf_align NOT IN ('pass', 'fail')))";
  }
} 

// get DKIM Status
// --------------------------------------------------------------------------
if( $selectDKIM <> '' ) {
  if ($selectDKIM == "pass") {
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " rptrecord.dkimresult = 'pass' AND rptrecord.dkim_align = 'pass'";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " rptrecord.dkimresult = 'pass' AND rptrecord.dkim_align = 'pass'";
  } elseif ($selectDKIM == "fail") {
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " rptrecord.dkimresult = 'fail' AND rptrecord.dkim_align = 'fail'";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " rptrecord.dkimresult = 'fail' AND rptrecord.dkim_align = 'fail'";
  } else {
    // $where .= ( $where <> '' ? " AND" : " WHERE" ) . " ((rptrecord.dkimresult != 'pass' OR rptrecord.dkimresult != 'fail') AND (rptrecord.dkim_align != 'pass' OR rptrecord.dkim_align != 'fail'))";
    // $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " ((rptrecord.dkimresult != 'pass' OR rptrecord.dkimresult != 'fail') AND (rptrecord.dkim_align != 'pass' OR rptrecord.dkim_align != 'fail'))";
    $where .= ( $where <> '' ? " AND" : " WHERE" ) . " ((rptrecord.dkimresult NOT IN ('pass', 'fail')) AND (rptrecord.dkim_align NOT IN ('pass', 'fail')))";
    $where2 .= ( $where2 <> '' ? " AND" : " AND" ) . " ((rptrecord.dkimresult NOT IN ('pass', 'fail')) AND (rptrecord.dkim_align NOT IN ('pass', 'fail')))";
  }
} 

// Get allowed reports and cache them - using serial as key
// --------------------------------------------------------------------------
$allowed_reports = array();

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
# echo "reportid = '$reportid'<BR>";
if( $grp_select == 'org' ) {
  if ( $reportid <> '-1') {
    $sql = "SELECT report.serial, MIN(report.mindate) AS mindate, MAX(report.maxdate) AS maxdate, report.domain, report.org, report.reportid, report.email, report.extra_contact_info, report.policy_adkim, report.policy_aspf, report.policy_p, report.policy_sp, report.policy_pct, report.raw_xml, sum(rptrecord.rcount) AS rcount, MIN(rptrecord.dkimresult) AS dkimresult, MIN(rptrecord.spfresult) AS spfresult, MIN(rptrecord.dkim_align) AS dkim_align, MIN(rptrecord.spf_align) AS spf_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(spfresult, 'none') AS spfresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spf_align, 'none') AS spf_align, serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY org ORDER BY maxdate $sort, org";
  } else {
    $sql = "SELECT rptrecord.serial, MIN(report.mindate) AS mindate, MAX(report.maxdate) AS maxdate, report.domain, report.org, report.policy_adkim, report.policy_aspf, report.policy_p, report.policy_sp, report.policy_pct FROM `rptrecord` LEFT JOIN `report` ON rptrecord.serial=report.serial $where GROUP BY rptrecord.serial ORDER BY maxdate ASC, rptrecord.serial ASC";
  }
} elseif( $grp_select == 'dom' ) {
  if ( $reportid <> '-1') {
    $sql = "SELECT report.serial, MIN(report.mindate) AS mindate, MAX(report.maxdate) AS maxdate, report.domain, report.org, report.reportid, report.email, report.extra_contact_info, report.policy_adkim, report.policy_aspf, report.policy_p, report.policy_sp, report.policy_pct, report.raw_xml, sum(rptrecord.rcount) AS rcount, MIN(rptrecord.dkimresult) AS dkimresult, MIN(rptrecord.spfresult) AS spfresult, MIN(rptrecord.dkim_align) AS dkim_align, MIN(rptrecord.spf_align) AS spf_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(spfresult, 'none') AS spfresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spf_align, 'none') AS spf_align, serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY domain ORDER BY maxdate $sort, domain";
  } else {
    $sql = "SELECT rptrecord.serial, MIN(report.mindate) AS mindate, MAX(report.maxdate) AS maxdate, report.domain, report.org, report.policy_adkim, report.policy_aspf, report.policy_p, report.policy_sp, report.policy_pct FROM `rptrecord` LEFT JOIN `report` ON rptrecord.serial=report.serial $where GROUP BY rptrecord.serial ORDER BY maxdate ASC, rptrecord.serial ASC";
  }
} elseif( $reportid <> '' ) {
  $sql = "SELECT report.* , sum(rptrecord.rcount) AS rcount, MIN(rptrecord.dkimresult) AS dkimresult, MIN(rptrecord.spfresult) AS spfresult, MIN(rptrecord.dkim_align) AS dkim_align, MIN(rptrecord.spf_align) AS spf_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(spfresult, 'none') AS spfresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spf_align, 'none') AS spf_align, serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial WHERE report.serial='$reportid' GROUP BY serial ORDER BY maxdate $sort, org";
} else {
  $sql = "SELECT report.* , sum(rptrecord.rcount) AS rcount, MIN(rptrecord.dkimresult) AS dkimresult, MIN(rptrecord.spfresult) AS spfresult, MIN(rptrecord.dkim_align) AS dkim_align, MIN(rptrecord.spf_align) AS spf_align, rptrecord.dkimresult AS dkimresult2, rptrecord.spfresult AS spfresult2 FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(spfresult, 'none') AS spfresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spf_align, 'none') AS spf_align, serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY serial ORDER BY maxdate $sort, org";
}

// Debug
// echo "sql reports = $sql<BR>";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
  //todo: check ACL if this row is allowed
  if (true) {
		//add data by serial
    $allowed_reports[BySerial][$row['serial']] = $row;
		//make a list of serials by domain and by organisation
		//$allowed_reports[ByDomain][$row['domain']][] = $row['serial'];
		$allowed_reports[ByOrganisation][$row['org']][] = $row['org'];
	}
}

if (isset($allowed_reports[BySerial])) {
// Generate Compliance Data
$sql = "SELECT sum(rptrecord.rcount) AS rcount, rptrecord.dkimresult AS dkimresult, rptrecord.dkim_align AS dkim_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spfresult, 'none') AS spfresult, COALESCE(spf_align, 'none') AS spf_align, rptrecord.serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY dkimresult, dkim_align ORDER BY dkimresult, dkim_align";

// Debug
// echo "sql reports = $sql<BR>";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
$total = 0;
$compliant = 0;
$non_compliant = 0;
while($row = $query->fetch_assoc()) {
	//todo: check ACL if this row is allowed
  if (true) {
    if (($row['dkimresult'] == "pass") && ($row['dkim_align'] == "pass")) {
      $compliant += $row['rcount'];
      $total += $row['rcount'];
    } else {
      $non_compliant += $row['rcount'];
      $total += $row['rcount'];
    }
	}
}
$pct_compliant = ($compliant / $total) * 100;
$pct_non_compliant = ($non_compliant / $total) * 100;
// echo $total . " - " . $compliant . " - " . $non_compliant . "<BR>";
// echo number_format($pct_compliant, 1) . " - " . number_format($pct_non_compliant, 1);

$datachart =  "     data: {";
$datachart .= "       labels: ['Compliant', 'Non Compliant'],";
$datachart .= "       datasets: [{";
$datachart .= "         data: [" . number_format($pct_compliant, 1) . ", " . number_format($pct_non_compliant, 1) . "],";
$datachart .= "         backgroundColor: [";
$datachart .= "           'rgba(40,173,78,1)',";
$datachart .= "           'rgba(217,83,79,1)',";
$datachart .= "         ],";
$datachart .= "         borderColor: [";
$datachart .= "           'rgba(40,173,78,0)',";
$datachart .= "           'rgba(217,83,79,0)',";
$datachart .= "         ],";
$datachart .= "         borderWidth: 1";
$datachart .= "       }]";
$datachart .= "     }";

$sql = "SELECT sum(rptrecord.rcount) AS rcount, rptrecord.spfresult AS spfresult, rptrecord.spf_align AS spf_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spfresult, 'none') AS spfresult, COALESCE(spf_align, 'none') AS spf_align, rptrecord.serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY spfresult, spf_align ORDER BY spfresult, spf_align";

// Debug
// echo "sql reports = $sql<BR>";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
$total = 0;
$compliant = 0;
$non_compliant = 0;
while($row = $query->fetch_assoc()) {
	//todo: check ACL if this row is allowed
  if (true) {
    if (($row['spfresult'] == "pass") && ($row['spf_align'] == "pass")) {
      $compliant += $row['rcount'];
      $total += $row['rcount'];
    } else {
      $non_compliant += $row['rcount'];
      $total += $row['rcount'];
    }
	}
}
$pct_compliant = ($compliant / $total) * 100;
$pct_non_compliant = ($non_compliant / $total) * 100;
// echo $total . " - " . $compliant . " - " . $non_compliant . "<BR>";
// echo number_format($pct_compliant, 1) . " - " . number_format($pct_non_compliant, 1);

$datachart2 =  "     data: {";
$datachart2 .= "       labels: ['Compliant', 'Non Compliant'],";
$datachart2 .= "       datasets: [{";
$datachart2 .= "         data: [" . number_format($pct_compliant, 1) . ", " . number_format($pct_non_compliant, 1) . "],";
$datachart2 .= "         backgroundColor: [";
$datachart2 .= "           'rgba(40,173,78,1)',";
$datachart2 .= "           'rgba(217,83,79,1)',";
$datachart2 .= "         ],";
$datachart2 .= "         borderColor: [";
$datachart2 .= "           'rgba(40,173,78,0)',";
$datachart2 .= "           'rgba(217,83,79,0)',";
$datachart2 .= "         ],";
$datachart2 .= "         borderWidth: 1";
$datachart2 .= "       }]";
$datachart2 .= "     }";

$sql = "SELECT sum(rptrecord.rcount) AS rcount, rptrecord.dkimresult AS dkimresult, rptrecord.dkim_align AS dkim_align, rptrecord.spfresult, rptrecord.spf_align AS spf_align FROM report LEFT JOIN (SELECT rcount, COALESCE(dkimresult, 'none') AS dkimresult, COALESCE(dkim_align, 'none') AS dkim_align, COALESCE(spfresult, 'none') AS spfresult, COALESCE(spf_align, 'none') AS spf_align, rptrecord.serial FROM rptrecord) AS rptrecord ON report.serial = rptrecord.serial $where GROUP BY dkimresult, dkim_align, spfresult, spf_align ORDER BY dkimresult, dkim_align, spfresult, spf_align";

// Debug
// echo "sql reports = $sql<BR>";

$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
$total = 0;
$compliant = 0;
$non_compliant = 0;
while($row = $query->fetch_assoc()) {
	//todo: check ACL if this row is allowed
  if (true) {
    if (($row['dkimresult'] == "pass") && ($row['dkim_align'] == "pass") && ($row['spfresult'] == "pass") && ($row['spf_align'] == "pass")) {
      $compliant += $row['rcount'];
      $total += $row['rcount'];
    } else {
      $non_compliant += $row['rcount'];
      $total += $row['rcount'];
    }
	}
}
$pct_compliant = ($compliant / $total) * 100;
$pct_non_compliant = ($non_compliant / $total) * 100;
// echo $total . " - " . $compliant . " - " . $non_compliant . "<BR>";
// echo number_format($pct_compliant, 1) . " - " . number_format($pct_non_compliant, 1);

$datachart3 =  "     data: {";
$datachart3 .= "       labels: ['Compliant', 'Non Compliant'],";
$datachart3 .= "       datasets: [{";
$datachart3 .= "         data: [" . number_format($pct_compliant, 1) . ", " . number_format($pct_non_compliant, 1) . "],";
$datachart3 .= "         backgroundColor: [";
$datachart3 .= "           'rgba(40,173,78,1)',";
$datachart3 .= "           'rgba(217,83,79,1)',";
$datachart3 .= "         ],";
$datachart3 .= "         borderColor: [";
$datachart3 .= "           'rgba(40,173,78,0)',"; #28AD4E
$datachart3 .= "           'rgba(217,83,79,0)',"; #D9534F
$datachart3 .= "         ],";
$datachart3 .= "         borderWidth: 1";
$datachart3 .= "       }]";
$datachart3 .= "     }";
} else {
  $datachart =  "      data: {";
  $datachart .= "      }";
  $datachart2 =  "      data: {";
  $datachart2 .= "      }";
  $datachart3 =  "      data: {";
  $datachart3 .= "      }";
}

// Generate Page with report list and report data (if a report is selected).
// --------------------------------------------------------------------------
if(isset($_GET['showxml'])){
  $sql = "SELECT raw_xml, reportid FROM report WHERE serial='" . $_GET['showxml'] . "'";

  // Debug
  // echo "sql reports = $sql<BR>";

  $query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
  while($row = $query->fetch_assoc()) {
	  echo "<center><B>XML Report " . $row['reportid'] . "</B></center>";
    echo "<pre>";
    $row['raw_xml'] = tidy_repair_string($row['raw_xml'], ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0]);
    echo htmlentities($row['raw_xml']);
    echo "</pre><br>";
  }
	echo "<center><a class='close' onclick=\"$('#showxml').hide();\">Close</a></center>";
} elseif (! is_numeric($reportid)) {
// if (($reportid == "") || ($reportid <> 0)) {
  echo tmpl_page( ""
    .tmpl_reportList($allowed_reports, $hostlookup, $sortorder, $dom_select, $org_select, $per_select, $reportid, $grp_select, $selectSPF, $selectDKIM)
	  , $reportid
	  , $hostlookup
	  , $sortorder
	  , $dom_select
	  , $domains
	  , $cssfile
	  , $org_select
	  , $orgs
	  , $per_select
	  , $periods
    , $grp_select
    , $selectSPF
    , $selectDKIM 
	  , $datachart
	  , $datachart2
	  , $datachart3
	  , $where2
  );
} else {
  echo tmpl_Recordpage( ""
    .tmpl_reportData($reportid, $allowed_reports, $hostlookup, $sortorder, $grp_select, $dom_select, $where2)
	  , $reportid
	  , $hostlookup
	  , $sortorder
	  , $dom_select
	  , $domains
	  , $cssfile
	  , $org_select
	  , $orgs
	  , $per_select
	  , $periods
	  , $grp_select
    , $selectSPF
    , $selectDKIM 
	  , $datachart
	  , $datachart2
	  , $datachart3
	  , $where2
  );
}
?>
