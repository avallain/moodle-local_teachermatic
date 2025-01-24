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
 * Languages configuration for the local_teachermatic plugin.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Teachermatic';
$string['settings:title'] = 'Teachermatic Configurations';
$string['settings:organisationid:title'] = 'Teachermatic organisation ID';
$string['settings:organisationid:description'] = 'This is the ID of the organisation that is registered into Teachermatic system.';
$string['service:invalidemail'] = 'The email address is not registered.';
$string['service:noorganisationid'] = 'Organisation ID has not been set.';
$string['service:invalidorganisationid'] = 'Organisation ID does not match.';
$string['service:create_multichoice:invalidrole'] = 'The user account is not allowed to create question(s).';
$string['service:create_truefalse:invalidrole'] = 'The user account is not allowed to create question(s).';
$string['service:create_shortanswer:invalidrole'] = 'The user account is not allowed to create question(s).';
$string['privacy:metadata'] = 'The Teachermatic external service only intercat with course(s) especially Question Bank it is only uses user email address and the course ID that is stored within Moodle data.';
$string['service:get_course_sections:invalidrole'] = 'The user account is not an editing teacher account.';
$string['service:create_course_mod_resource:invalidrole'] = 'The user account is not an editing teacher account.';
