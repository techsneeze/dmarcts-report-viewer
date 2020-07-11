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

function main() {

	include "dmarcts-report-viewer-config.php";

}


function get_status_color($row) {
	$status = "";
	$status_num = "";
    if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")) {
	    $status="red";
		$status_num="4";
    } elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")) {
	    $status="orange";
	    $status_num="3";
    } elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")) {
	    $status="lime";
	    $status_num="1";
    } else {
	    $status="yellow";
	    $status_num="2";
    }
#	$status .= "\"><span style='display:none;'>" . $status_content . "</span></span>";
#	$status_num .= "\"><span style='display:none;'>" . $status_content . "</span></span>";
    return array($status, $status_num);
}

function format_date($date, $format) {
    $answer = date($format, strtotime($date));
    return $answer;
};

