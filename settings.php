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

require_once($CFG->dirroot . '/filter/html5avtomp4/locallib.php');

$settings->add(new admin_setting_configexecutable('filter_html5avtomp4/pathtoffmpeg',get_string('pathtoffmpeg','filter_html5avtomp4'),get_string('pathtoffmpeg_desc','filter_html5avtomp4'),'/usr/bin/ffmpeg'));

$settings->add(new admin_setting_configtext('filter_html5avtomp4/convertonlyexts', get_string('convertonlyexts', 'filter_html5avtomp4'), get_string('convertonlyexts_desc', 'filter_html5avtomp4'), 'ogg, ogv, webm'));

$settings->add(new admin_setting_configcheckbox('filter_html5avtomp4/convertaudio', get_string('convertaudio', 'filter_html5avtomp4'), get_string('convertaudio_desc', 'filter_html5avtomp4'), true, true, false));

$settings->add(new admin_setting_configtext('filter_html5avtomp4/audioffmpegsettings', get_string('audioffmpegsettings', 'filter_html5avtomp4'), get_string('audioffmpegsettings_desc', 'filter_html5avtomp4'), '-i ' . FILTER_HTML5AVTOMP4_INPUTFILE_PLACEHOLDER . ' ' . FILTER_HTML5AVTOMP4_OUTPUTFILE_PLACEHOLDER, PARAM_RAW, 80));

$settings->add(new admin_setting_configcheckbox('filter_html5avtomp4/convertvideo', get_string('convertvideo', 'filter_html5avtomp4'), get_string('convertvideo_desc', 'filter_html5avtomp4'), true, true, false));

$settings->add(new admin_setting_configtext('filter_html5avtomp4/videoffmpegsettings', get_string('videoffmpegsettings', 'filter_html5avtomp4'), get_string('videoffmpegsettings_desc', 'filter_html5avtomp4'), '-i ' . FILTER_HTML5AVTOMP4_INPUTFILE_PLACEHOLDER . ' ' . FILTER_HTML5AVTOMP4_OUTPUTFILE_PLACEHOLDER, PARAM_RAW, 80));

