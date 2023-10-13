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
 *
 * @package    local_gustaffview
 * @copyright  2023
 * @author
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_gustaffview_extend_navigation_course($parentnode, $course, $context) {
    global $USER;
    $cntstaff = block_newgu_spdetails_external::checkrole($USER->id, $course->id);

    // if (!has_capability('local/gustaffview:view', $context)) {
    if ($cntstaff!=1) {
        return;
    }
    $url = new moodle_url('/local/gustaffview/sduserdetails.php', ['courseid' => $course->id]);
    $name = get_string('pluginname', 'local_gustaffview');
    $icon = new pix_icon('t/grades', '');
    $parentnode->add($name, $url, navigation_node::NODETYPE_LEAF, 'gustaffview', null, $icon);
    $parentnode->make_active();
}