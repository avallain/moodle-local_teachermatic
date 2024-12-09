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

namespace local_teachermatic\external;

use core\exception\invalid_parameter_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;

/**
 * Web service to get available courses by the user email address
 * where the user is enrolled as 'editingteacher'.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED)
        ]);
    }

    public static function execute(string $email): array
    {
        global $DB;
        
        $params = self::validate_parameters(self::execute_parameters(), ['email' => $email]);

        $storedOrgId = get_config('local_teachermatic', 'organisationid');
        if (!$storedOrgId) {
            throw new invalid_parameter_exception(get_string('service:noorganisationid', 'local_teachermatic'));
        }
        
        $selectedUser = core_user::get_user_by_email($params['email'], 'id');
        if (!$selectedUser) {
            throw new invalid_parameter_exception(get_string('service:invalidemail', 'local_teachermatic'));
        }

        $editingTeacherRole = $DB->get_record(
            'role', 
            ['shortname' => 'editingteacher'],
            '*',
            MUST_EXIST
        );

        $userCoursesQuery = "
            SELECT c.id, c.fullname, c.shortname
            FROM {role_assignments} ra
            JOIN {context} ctx ON ctx.id = ra.contextid
            JOIN {course} c ON c.id = ctx.instanceid
            WHERE ra.userid = :userid
              AND ra.roleid = :roleid
              AND ctx.contextlevel = :contextlevel
        ";
        $userCourses = $DB->get_records_sql($userCoursesQuery, [
            'userid' => $selectedUser->id,
            'roleid' => $editingTeacherRole->id,
            'contextlevel' => CONTEXT_COURSE
        ]);

        return [
            'status' => true,
            'organisation_id' => $storedOrgId,
            'courses' => (!empty($userCourses)) ? $userCourses : [],
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The web service status'),
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation ID'),
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'The course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'The course id'),
                    'fullname' => new external_value(PARAM_TEXT, 'The course id'),
                ])
            )
        ]);
    }
}
