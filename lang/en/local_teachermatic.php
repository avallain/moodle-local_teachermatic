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

$string['error:cannotaddcoursemoduletosection'] = 'Can not add course module to section.';
$string['error:coursemodulenotenabled'] = 'Course module is not enabled';
$string['error:event:createcoursemodresourcefailed'] = 'Course mod resource creation failed.';
$string['error:event:createcoursemodresourcefaileddescription'] = 'User {$a->userid} failed to export file from TeacherMatic in course {$a->courseid}. Error: {$a->message}';
$string['error:event:createmultichoicefailed'] = 'Multichoice question creation failed.';
$string['error:event:createmultichoicefaileddescription'] = 'User {$a->userid} failed to export multichoice questions from TeacherMatic in course {$a->courseid}. Error: {$a->message}';
$string['error:event:createshortanswerfailed'] = 'Short answer question creation failed.';
$string['error:event:createshortanswerfaileddescription'] = 'User {$a->userid} failed to export shortanswer questions from TeacherMatic in course {$a->courseid}. Error: {$a->message}';
$string['error:event:createtruefalsefailed'] = 'True false question creation failed.';
$string['error:event:createtruefalsefaileddescription'] = 'User {$a->userid} failed to export truefalse questions from TeacherMatic in course {$a->courseid}. Error: {$a->message}';
$string['error:event:trace'] = ' | Trace: {$a->trace}';
$string['error:invalidquestioncategory'] = 'Invalid question category.';
$string['pluginname'] = 'Teachermatic';
$string['privacy:metadata'] = 'The Teachermatic external service only intercat with course(s) especially Question Bank it is only uses user email address and the course ID that is stored within Moodle data.';
$string['service:invalidemail'] = 'The email address is not registered.';
$string['service:invalidorganisationid'] = 'Organisation ID does not match.';
$string['service:noorganisationid'] = 'Organisation ID has not been set.';
$string['settings:organisationid:description'] = 'This is the ID of the organisation that is registered into Teachermatic system.';
$string['settings:organisationid:title'] = 'Teachermatic organisation ID';
$string['settings:title'] = 'Teachermatic Configurations.';
