<?php

//####################################################################
//### defines ########################################################
//####################################################################

define("BySerial", 1);
define("ByDomain", 2);
define("ByOrganisation", 3);

// The $_FILES key for uploaded reports
define('REPORT_FILE', 'report_file');

//####################################################################
//### utility functions ##############################################
//####################################################################

function util_formatDate($date, $format) {
    $answer = date($format, strtotime($date));
    return $answer;
};


function util_checkUploadedFile($file_name, &$options = array()) {
    if (!isset($_FILES[$file_name]) || $_FILES[$file_name]['size'] == 0) {
        throw new Exception("missing file");
    }
    if (!is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
        throw new Exception("non-uploaded file");
    }

    if (isset($options['mimetype'])) {
        $mimetypes = !is_array($options['mimetype']) ? array($options['mimetype']) : $options['mimetype'];
        if (!in_array($_FILES[$file_name]['type'], $mimetypes)) {
            throw new Exception("invalid filetype: {$_FILES[$file_name]['type']}");
        }
        $options['mimetype'] = $_FILES[$file_name]['type'];
    }
    return $_FILES[$file_name]['tmp_name'];
}

function util_extractGZip($file_name, &$xml_file_name) {
    $gz_fd = fopen('compress.zlib://'.$file_name, 'r');
    if (!$gz_fd) {
        throw new Exception("unable to open gzip file");
    }

    $dot = strrpos($file_name, '.');
    $xml_file_name = ($dot !== false ? substr($file_name, $dot) : $file_name);

    return $gz_fd;
}

function util_extractZip($file_name, &$xml_file_name) {
    // We must decompress the zip file
    $zip = new ZipArchive();
    if (!$zip->open($file_name)) {
        throw new Exception("unable to open zipfile: ".$zip->getStatusString());
    }

    if ($zip->numFiles != 1) {
        $numFiles = $zip->numFiles;
        $zip->close();
        throw new Exception("unexpected file count in zipfile: $numFiles");
    }

    $xml_file_name = $zip->getNameIndex(0);
    if (!$xml_file_name || strcasecmp(substr($xml_file_name, -3), 'xml')) {
        throw new Exception("expected xml file, got: $xml_file_name");
    }

    $xml_fd = $zip->getStream($xml_file_name);
    if (!$xml_fd) {
        $msg = sprintf("failed to get stream for zip entry %s: %s", $zip->getNameIndex(0), $zip->getStatusString());
        $zip->close();
        throw new Exception($msg);
    }
    $zip->close();

    return $xml_fd;
}

//####################################################################
//### template functions #############################################
//####################################################################

function tmpl_reportList($allowed_reports) {
    $reportlist[] = "";
    $reportlist[] = "<!-- Start of report list -->";

    $reportlist[] = "<h1>DMARC Reports</h1>";
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

    foreach ($allowed_reports[BySerial] as $row) {
        $date_output_format = "r";
        $reportlist[] =  "    <tr>";
        $reportlist[] =  "      <td class='right'>". util_formatDate($row['mindate'], $date_output_format). "</td>";
        $reportlist[] =  "      <td class='right'>". util_formatDate($row['maxdate'], $date_output_format). "</td>";
        $reportlist[] =  "      <td class='center'>". $row['domain']. "</td>";
        $reportlist[] =  "      <td class='center'>". $row['org']. "</td>";
        $reportlist[] =  "      <td class='center'><a href='?report=". $row['serial']. "#rpt". $row['serial']. "'>". $row['reportid']. "</a></td>";
        $reportlist[] =  "      <td class='center'>". $row['rcount']. "</td>";
        $reportlist[] =  "    </tr>";
    }
    $reportlist[] =  "  </tbody>";

    $reportlist[] =  "</table>";

    $reportlist[] = "<!-- End of report list -->";
    $reportlist[] = "";

    #indent generated html by 2 extra spaces
    return implode("\n  ",$reportlist);
}

function tmpl_reportData($reportnumber, $allowed_reports) {

    if (!$reportnumber) {
        return "";
    }

    $reportdata[] = "";
    $reportdata[] = "<!-- Start of report rata -->";

    if (isset($allowed_reports[BySerial][$reportnumber])) {
        $row = $allowed_reports[BySerial][$reportnumber];
        $reportdata[] = "<div class='center reportdesc'><p> Report from ".$row['org']." for ".$row['domain']."<br>(". util_formatDate($row['mindate'], "r" ). " - ".util_formatDate($row['maxdate'], "r" ).")</p></div>";
    } else {
        return "Unknown report number!";
    }

    $reportdata[] = "<a id='rpt".$reportnumber."'></a>";
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
    $sql = "SELECT * FROM rptrecord WHERE serial = $reportnumber";
    $query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
    while($row = $query->fetch_assoc()) {
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
        $reportdata[] = "      <td>". gethostbyaddr($ip). "</td>";
        $reportdata[] = "      <td>". $row['rcount']. "</td>";
        $reportdata[] = "      <td>". $row['disposition']. "</td>";
        $reportdata[] = "      <td>". $row['reason']. "</td>";
        $reportdata[] = "      <td>". $row['dkimdomain']. "</td>";
        $reportdata[] = "      <td>". $row['dkimresult']. "</td>";
        $reportdata[] = "      <td>". $row['spfdomain']. "</td>";
        $reportdata[] = "      <td>". $row['spfresult']. "</td>";
        $reportdata[] = "    </tr>";
    }
    $reportdata[] = "  </tbody>";
    $reportdata[] = "</table>";

    $reportdata[] = "<!-- End of report rata -->";
    $reportdata[] = "";

    #indent generated html by 2 extra spaces
    return implode("\n  ",$reportdata);
}

function tmpl_page ($body) {
    $html = array();

    $html[] = "<!DOCTYPE html>";
    $html[] = "<html>";
    $html[] = "  <head>";
    $html[] = "    <title>DMARC Report Viewer</title>";
    $html[] = "    <link rel='stylesheet' href='default.css'>";
    $html[] = "  </head>";

    $html[] = "  <body>";

    $html[] = $body;

    $html[] = "  <div class='footer'>Brought to you by <a href='http://www.techsneeze.com'>TechSneeze.com</a> - <a href='mailto:dave@techsneeze.com'>dave@techsneeze.com</a></div>";
    $html[] = "  </body>";
    $html[] = "</html>";

    return implode("\n",$html);
}

function tmpl_importForm() {
    $replace_checked = (isset($_POST['replace_report']) && $_POST['replace_report'] == "1" ? ' checked' : '');
    $html = <<<HTML
    <h1>DMARC Import</h1>
    <form class="import_report" method="post" enctype="multipart/form-data">
        <label for="report_file">DMARC report:</label>&nbsp;
        <input type="file" name="report_file" id="report_file">
        <input type="checkbox" name="replace_report" id="replace_report" value="1"{$replace_checked}>&nbsp;
        <label for="replace_report">Replace report</label>
        <input type="submit" name="submit_report">
    </form>
HTML;
    return $html;
}

//####################################################################
//### report-wranglin functions ######################################
//####################################################################


function report_checkXML($xml_data) {
    $data = @simplexml_load_string($xml_data);
    if ($data === false) {
        throw new Exception("failed to load xml data from file");
    }

    $root_name = $data->getName();
    if ($root_name != 'feedback') {
        throw new Exception("unexpected xml root element: {$root_name}");
    }
    return $data;
}

function report_importXML($xml, $replace_report, &$log = null) {
    $metadata = $xml->xpath('(/feedback/report_metadata)')[0];
    $policy = $xml->xpath('/feedback/policy_published')[0];

    $serial = null;
    $reports = db_execute("SELECT org,reportid,serial FROM report WHERE reportid = ?", (string)$metadata->report_id);

    if (!empty($reports) && count($reports) > 1) {
        $log[] = "unexpected number of reports for id {$metadata->report_id}";
        return false;
    }

    // We already have that report, replace if asked to, else use it
    if (!empty($reports)) {
        if ($replace_report) {
            $log[] = "Replacing old report {$reports[0]['org']}, {$reports[0]['reportid']}";
            db_execute('DELETE FROM rptrecord WHERE serial=?', $reports[0]['serial']);
            db_execute('DELETE FROM report WHERE serial=?', $reports[0]['serial']);
        } else {
            $log[] = "Report {$reports[0]['org']}, {$reports[0]['reportid']} already known";
            return true;
        }
    }

    // This is a report we don't know about
    $stmt = db_execute("INSERT INTO report(mindate,maxdate,
        domain,org,reportid,
        email,extra_contact_info,
        policy_adkim,policy_aspf,policy_p,policy_sp,policy_pct)
    VALUES(FROM_UNIXTIME(?),FROM_UNIXTIME(?),?,?,?,?,?,?,?,?,?,?)",
        $metadata->date_range->begin, $metadata->date_range->end,
        $policy->domain, $metadata->org_name, $metadata->report_id,
        $metadata->email, $metadata->extra_contact_info,
        $policy->adkim, $policy->aspf, $policy->p, $policy->sp, (int)$policy->pct
    );
    $serial = $stmt->insert_id;

    $records = $xml->xpath('/feedback/record');
    foreach ($records as $record) {
        $ip = $ip6 = null;
        $ipval = $record->row->source_ip;
        if (ip2long($ipval)) {
            $ip = unpack("N", inet_pton($ipval));
            $ip = $ip[1];
        } else {
            $ip6 = unpack("H*", inet_pton($ipval));
            $ip6 = $ip6[1];
        }

        $success = db_execute("INSERT INTO rptrecord(
            serial,ip,ip6,rcount,
            disposition,spf_align,dkim_align,reason,
            dkimdomain,dkimresult,
            spfdomain,spfresult,
            identifier_hfrom)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)",
            $serial, $ip, $ip6, $record->row->count,
            $record->row->policy_evaluated->disposition,
            $record->row->policy_evaluated->spf, $record->row->policy_evaluated->dkim,
            $record->row->policy_evaluated->reason,
            $record->auth_results->dkim->domain, $record->auth_results->dkim->result,
            $record->auth_results->spf->domain,  $record->auth_results->spf->result,
            $record->identifiers->header_from
        );
    }

    return true;
}

function report_importFile($filename, $replace_report, &$log = array()) {
    $xml_fd = null;
    $xml_file_name = null;
    $type = mime_content_type($filename);
    switch ($type) {
        case 'application/zip':
            $xml_fd = util_extractZip($filename, $xml_file_name);
            break;

        case 'application/x-gzip':
            $xml_fd = util_extractGZip($filename, $xml_file_name);
            break;

        case 'application/xml':
            $xml_fd = fopen($filename, 'r');
            if (!$xml_fd) {
                throw new Exception("failed to open xml file: $file_name");
            }
            $xml_file_name = $filename;
            break;

        default:
            throw new Exception("unknown file type \"{$type}\" for \"{$filename}\"");
            break;
    }

    $xml_data = stream_get_contents($xml_fd);
    if (!$xml_data) {
        throw new Exception("file was empty: $xml_file_name");
    }

    $report_data = report_checkXML($xml_data);

    $success = report_importXML($report_data, $replace_report, $log);
    if (!$success) {
        throw new Exception("failed to import report");
    }
    return $success;
}

//####################################################################
//### submit handlers ################################################
//####################################################################

function submit_handleReport() {
    if (!isset($_POST['submit_report'])) return;

    $replace_report = (isset($_POST['replace_report']) && $_POST['replace_report'] == "1");

    try {
        $options = array('mimetype' => array('text/xml', 'application/zip', 'application/x-gzip'));
        $file_name = util_checkUploadedFile(REPORT_FILE, $options);

        $log = array();
        $success = report_importFile($file_name, $replace_report, $log);

        $log = implode("\n", $log);

        $html = <<<HTML
        <div class="message success">successfully imported report from file</div>
        <div class="output">Output:<br><pre>{$log}</pre></div>
HTML;
        return $html;
    } catch (Exception $e) {
        return "<div class=\"message error\">".$e->getMessage()."</div>";
    }
}

//####################################################################
//### database functions #############################################
//####################################################################

// The file is expected to be in the same folder as this script, and it
// must exist.
include "dmarcts-report-config.php";

// Make a MySQL Connection using mysqli
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    exit;
}

function db_execute($sql) {
    global $mysqli;
    $args = func_get_args();
    array_shift($args); // Drop $sql parameter from our arguments

    $stmt = mysqli_stmt_init($mysqli);

    $success = $stmt->prepare($sql);
    if (!$success) {
        $msg = sprintf("mysqli_prepare: %s (%d)", $mysqli->error, $mysqli->errno);
        throw new Exception($msg);
    }

    $type = '';
    $refs = array();
    foreach ($args as $key => $arg) {
        if (is_integer($arg))    $type .= 'i';
        elseif (is_double($arg)) $type .= 'd';
        else                     $type .= 's';
        $refs[$key] = &$args[$key];
    }

    array_unshift($refs, $type);

    call_user_func_array(array($stmt, 'bind_param'), $refs);

    $result = $stmt->execute();
    if (!$result) {
        $msg = sprintf("mysqli_stmt_execute: %s (%d)", $stmt->error, $stmt->errno);
        throw new Exception($msg);
    }

    if ($stmt->affected_rows != -1) {
        // this looks like a not-SELECT, return our statement
        return $stmt;
    }

    // This was a SELECT, grab the results
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();

    return $results;
}

?>
