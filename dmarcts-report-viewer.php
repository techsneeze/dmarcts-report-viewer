<?php

// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com and John Bieling
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

require_once 'dmarcts-report-common.php';

//####################################################################
//### main ###########################################################
//####################################################################

// Get allowed reports and cache them - using serial as key
$allowed_reports = array(BySerial => array(), ByDomain => array(), ByOrganisation => array());
# Include the rcount via left join, so we do not have to make an sql query for every single report.
$sql = "SELECT report.* , SUM(rptrecord.rcount) AS rcount FROM `report` LEFT JOIN rptrecord ON report.serial = rptrecord.serial GROUP BY serial ORDER BY mindate";
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
    //todo: check ACL if this row is allowed
    if (true) {
        //add data by serial
        $allowed_reports[BySerial][$row['serial']] = $row;
        //make a list of serials by domain and by organisation
        $allowed_reports[ByDomain][$row['domain']][] = $row['serial'];
        $allowed_reports[ByOrganisation][$row['org']][] = $row['serial'];
    }
}

if(isset($_GET['report']) && is_numeric($_GET['report'])){
    $reportid=$_GET['report'];
}elseif(!isset($_GET['report'])){
    $reportid=false;
}else{
    die('Invalid Report ID');
}

// Generate Page with report list and report data (if a report is selected).
echo tmpl_page( ""
    .tmpl_reportList($allowed_reports)
    .tmpl_reportData($reportid, $allowed_reports )
    .tmpl_importForm()
    .submit_handleReport()
);
?>
