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
 * Create admin bookmarks.
 *
 * @package    local_gustaffview
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $DB, $USER;

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/gustaffview/index.php');
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();


echo $OUTPUT->footer();
