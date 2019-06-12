<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    filter_html5avtomp4
 * @copyright  2019 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('FILTER_HTML5AVTOMP4_JOBSTATUS_INITIAL', 0);
define('FILTER_HTML5AVTOMP4_JOBSTATUS_RUNNING', 1);
define('FILTER_HTML5AVTOMP4_JOBSTATUS_DONE', 2);
define('FILTER_HTML5AVTOMP4_JOBSTATUS_FAILED', 3);
define('FILTER_HTML5AVTOMP4_INPUTFILE_PLACEHOLDER', '{%INPUTFILE%}');
define('FILTER_HTML5AVTOMP4_OUTPUTFILE_PLACEHOLDER', '{%OUTPUTFILE%}');

/**
 * @param int|null  $jobid
 * @param bool|null $displaytrace
 *
 * @throws dml_exception
 * @throws file_exception
 */
function filter_html5avtomp4_processjobs(?int $jobid = null, ?bool $displaytrace = true) {
    $pathtoffmpeg = get_config('filter_html5avtomp4', 'pathtoffmpeg');

    if (empty($pathtoffmpeg) || !is_executable(trim($pathtoffmpeg))) {
        // don't bother if ffmpeg is not usable
        if ($displaytrace) {
            mtrace('ffmpeg not available, aborting');
        }

        return;
    }

    global $DB;

    if ($jobid > 0) {
        $job = $DB->get_record('filter_html5avtomp4_jobs', [
                'id'     => $jobid,
                'status' => FILTER_HTML5AVTOMP4_JOBSTATUS_INITIAL
        ]);
    }
    else {
        // take one job at a time
        $job = $DB->get_record_select('filter_html5avtomp4_jobs', 'status = ? ORDER BY id ASC LIMIT 1',
                [FILTER_HTML5AVTOMP4_JOBSTATUS_INITIAL]);
    }
    if (!$job) {
        if ($displaytrace) {
            mtrace('no jobs found');
        }

        return;
    }

    $fs = get_file_storage();
    $inputfile = $fs->get_file_by_id($job->fileid);

    if (!$inputfile) {
        $job->status = FILTER_HTML5AVTOMP4_JOBSTATUS_FAILED;
        $DB->update_record('filter_html5avtomp4_jobs', $job);

        if ($displaytrace) {
            mtrace('file ' . $job->fileid . ' not found');
        }

        return;
    }

    // to make sure we don't try to run the same job twice
    $job->status = FILTER_HTML5AVTOMP4_JOBSTATUS_RUNNING;
    $DB->update_record('filter_html5avtomp4_jobs', $job);

    $tempdir = make_temp_directory('filter_html5avtomp4');
    $tmpinputfilepath = $inputfile->copy_content_to_temp('filter_html5avtomp4');
    $tmpoutputfilename = str_replace('.ogg', '.m4a', $inputfile->get_filename());
    $tmpoutputfilename = str_replace('.webm', '.mp4', $tmpoutputfilename);
    $tmpoutputfilename = str_replace('.ogv', '.mp4', $tmpoutputfilename);
    $tmpoutputfilepath = $tempdir . DIRECTORY_SEPARATOR . $tmpoutputfilename;

    $type = (strpos($tmpoutputfilename, '.m4a') !== false)
            ? 'audio'
            : 'video';

    $inputfileplaceholder_preg = preg_quote(FILTER_HTML5AVTOMP4_INPUTFILE_PLACEHOLDER, '/');
    $outputfileplaceholder_preg = preg_quote(FILTER_HTML5AVTOMP4_OUTPUTFILE_PLACEHOLDER, '/');
    $ffmpegoptions = preg_replace('/^(.*)' . $inputfileplaceholder_preg . '(.*)' . $outputfileplaceholder_preg . '(.*)$/', '$1 ' . escapeshellarg($tmpinputfilepath) . ' $2 ' . escapeshellarg($tmpoutputfilepath) . ' $3', get_config('filter_html5avtomp4', $type . 'ffmpegsettings'));

    $command = escapeshellcmd(trim($pathtoffmpeg) . ' ' . $ffmpegoptions);
    if ($displaytrace) {
        mtrace($command);
    }

    $output = null;
    $return = null;
    exec($command, $output, $return);
    if ($output) {
        print_r($output);
    }
    if ($displaytrace) {
        mtrace('...returned ' . $return);
    }

    unlink($tmpinputfilepath); // not needed anymore

    if (!file_exists($tmpoutputfilepath) || !is_readable($tmpoutputfilepath)) {
        $job->status = FILTER_HTML5AVTOMP4_JOBSTATUS_FAILED;
        $DB->update_record('filter_html5avtomp4_jobs', $job);

        if ($displaytrace) {
            mtrace('output file not found');
        }

        return;
    }

    $fs = get_file_storage();
    $inputfile_properties = $DB->get_record('files', ['id' => $inputfile->get_id()]);
    $outputfile_properties = [
            'contextid'    => $inputfile_properties->contextid,
            'component'    => $inputfile_properties->component,
            'filearea'     => $inputfile_properties->filearea,
            'itemid'       => $inputfile_properties->itemid,
            'filepath'     => $inputfile_properties->filepath,
            'filename'     => $tmpoutputfilename,
            'userid'       => $inputfile_properties->userid,
            'author'       => $inputfile_properties->author,
            'license'      => $inputfile_properties->license,
            'timecreated'  => time(),
            'timemodified' => time()
    ];
    try {
        $outputfile = $fs->create_file_from_pathname($outputfile_properties, $tmpoutputfilepath);
    }
    catch (Exception $exception) {
        $job->status = FILTER_HTML5AVTOMP4_JOBSTATUS_FAILED;
        $DB->update_record('filter_html5avtomp4_jobs', $job);

        if ($displaytrace) {
            mtrace('file could not be saved: ' . $exception->getMessage());
        }

        return;
    }
    unlink($tmpoutputfilepath); // not needed anymore

    $job->status = FILTER_HTML5AVTOMP4_JOBSTATUS_DONE;
    $DB->update_record('filter_html5avtomp4_jobs', $job);

    if ($displaytrace) {
        mtrace('created file id ' . $outputfile->get_id());
    }
}
