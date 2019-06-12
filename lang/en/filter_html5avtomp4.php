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
 * @package    filter_html5avtomp4s
 * @copyright  2019 Université de Lausanne
 * @author     Nicolas.Dunand@unil.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'HTML5 audio/video to MP4 filter';
$string['pathtoffmpeg'] = 'Path to ffmpeg';
$string['pathtoffmpeg_desc'] = 'Path to the ffmpeg executable';
$string['convertaudio'] = 'Convert audio';
$string['convertaudio_desc'] = 'Convert audio OGG files to MP4 (M4A)';
$string['convertvideo'] = 'Convert video';
$string['convertvideo_desc'] = 'Convert video WEBM files to MP4';
$string['processjobs_task'] = 'Process reencoding non-MP4 files';
$string['audioffmpegsettings'] = 'ffmpeg settings for audio conversion';
$string['audioffmpegsettings_desc'] = 'This should contain at least "-i {%INPUTFILE%} {%OUTPUTFILE%}"; place your options around these as needed';
$string['videoffmpegsettings'] = 'ffmpeg settings for video conversion';
$string['videoffmpegsettings_desc'] = 'This should contain at least "-i {%INPUTFILE%} {%OUTPUTFILE%}"; place your options around these as needed';
$string['convertonlyexts'] = 'convert only these extensions';
$string['convertonlyexts_desc'] = 'Comma-separated list of file extensions to be converted to mp4';
