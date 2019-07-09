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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/filter/html5avtomp4/locallib.php');

class filter_html5avtomp4 extends moodle_text_filter {

    function filter($text, array $options = array()) {

        if (!is_string($text)) {
            // non string data can not be filtered anyway
            return $text;
        }

        if (get_config('filter_html5avtomp4', 'convertaudio') === false && get_config('filter_html5avtomp4',
                        'convertvideo') === false) {
            // plugin is not configured to convert anything
            return $text;
        }

        if ((get_config('filter_html5avtomp4', 'convertaudio') === false || strpos($text,
                                '</audio>') === false) && (get_config('filter_html5avtomp4',
                                'convertvideo') === false || strpos($text, '</video>') === false)) {
            // nothing to do
            return $text;
        }

        $pattern = '/(<(audio|video)[^>]+>)[^<]*(<source src="[^"]+"[^>]*>[^<]+?)+?[^<]*(<\/\2>)/sU';
        $text = preg_replace_callback($pattern, 'filter_html5avtomp4_checksources', $text);

        return $text;
    }

}

/**
 * @param $matches
 *
 * @return mixed|string
 * @throws coding_exception
 * @throws dml_exception
 * @throws file_exception
 */
function filter_html5avtomp4_checksources($matches) {
    global $CFG;

    $fullmatch = array_shift($matches);

    $tag_open = array_shift($matches);
    $tag_close = array_pop($matches);

    // now we just need to get rid of the capturing group that was used for backreference ("\2" in regex)
    array_shift($matches);

    $type = preg_replace('/[^a-z]/', '', strtolower($tag_close)); // 'audio' or 'video'

    $source_tags = $matches;
    $extra_source_tags = [];

    if (count($source_tags) > 1) {
        // there are several source tags, we therefore assume it's sufficient
        return $fullmatch;
    }

    // so we can now assume there is only one element in $source_tags
    $source_tag = array_pop($source_tags);
    $src_url = preg_replace('/^.*src="([^"]+)".*$/s', '$1', $source_tag);

    if (strpos($src_url, $CFG->wwwroot) === false) {
        // file is not hosted on the Moodle server, abort
        return $fullmatch;
    }

    $toconvert_fileextensions = array_map('trim', explode(',', get_config('filter_html5avtomp4', 'convertonlyexts')));
    $src_fileextension = preg_replace('/^.*\.([a-zA-Z0-9]{3,4})$/', '$1', strtolower($src_url));
    if (!in_array($src_fileextension, $toconvert_fileextensions)) {
        // no need to continue as no extra source is required
        return $fullmatch;
    }

    $filepath = str_replace($CFG->wwwroot . '/pluginfile.php/', '', $src_url);
    $filepathargs = explode('/', ltrim($filepath, '/'));

    if (count($filepathargs) < 4) { // always at least context, component and filearea
        print_error('invalidarguments');
    }

    $contextid = (int)array_shift($filepathargs);
    $component = clean_param(array_shift($filepathargs), PARAM_COMPONENT);
    $filearea = clean_param(array_shift($filepathargs), PARAM_AREA);
    $inputfilename = clean_param(array_pop($filepathargs), PARAM_FILE);

    $outputfile_ext = ($type == 'audio')
            ? 'm4a'
            : 'mp4';
    $outputfilename = preg_replace('/\.' . $src_fileextension . '/i', '.' . $outputfile_ext, $inputfilename);

    $inputfile = null;
    $outputfile = null;

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, $component, $filearea);

    foreach ($files as $file) {
        if ($file->get_filename() === $inputfilename) {
            // this is the OGG or WEBM file
            $inputfile = $file;
        }
        if ($file->get_filename() === $outputfilename) {
            // this is the M4V or MP4 file
            $outputfile = $file;
        }
    }

    if (is_null($inputfile)) {
        // could not find input file, abort
        return $fullmatch;
    }

    if (is_null($outputfile) && get_config('filter_html5avtomp4', 'convert' . $type)) {
        global $DB;

        $existingjob = $DB->get_record('filter_html5avtomp4_jobs', ['fileid' => $inputfile->get_id()]);
        // first make sure there's not yet a job planned for this file

        if (!$existingjob) {
            // let's create a job to convert this file
            $job = (object)[
                    'fileid' => $inputfile->get_id(),
                    'status' => FILTER_HTML5AVTOMP4_JOBSTATUS_INITIAL
            ];
            $jobid = $DB->insert_record('filter_html5avtomp4_jobs', $job);

            if ($type == 'audio') {
                // process audio jobs immediately
                \filter_html5avtomp4_processjobs($jobid, false);
            }
        }

        return $fullmatch;
    }
    else {
        // we're good to display the MP4 :))

        $extra_source_tags = [
                '<source src="' . str_replace($inputfilename, $outputfilename, $src_url) . '" type="' . $outputfile->get_mimetype() . '">'
        ];
    }

    $alltags = array_merge([$tag_open], [$source_tag], $extra_source_tags, [$tag_close]);

    return implode("\n", $alltags);
}