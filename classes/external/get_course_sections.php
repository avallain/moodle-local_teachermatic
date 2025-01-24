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

use context_course;
use core\exception\invalid_parameter_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;

/**
 * Web service to get course sections by the course id and the user email address
 * where the user is enrolled as 'editingteacher'.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_course_sections extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'The course ID', VALUE_REQUIRED),
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED),
        ]);
    }

    public static function execute(int $courseId, string $email): array
    {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'email' => $email,
            'course_id' => $courseId,
        ]);

        $storedOrgId = get_config('local_teachermatic', 'organisationid');
        if (!$storedOrgId) {
            throw new invalid_parameter_exception(get_string('service:noorganisationid', 'local_teachermatic'));
        }

        $selectedUser = core_user::get_user_by_email($params['email'], 'id');
        if (!$selectedUser) {
            throw new invalid_parameter_exception(get_string('service:invalidemail', 'local_teachermatic'));
        }

        $contextCourse = context_course::instance($params['course_id']);
        self::validate_context($contextCourse);

        $selectedUserRoles = get_user_roles_in_course($selectedUser->id, $params['course_id']);
        $selectedUserRoles = explode(',', strtolower($selectedUserRoles));
        $selectedUserRoles = array_map(fn($value): string => trim(strip_tags($value)), $selectedUserRoles);

        if(!in_array('teacher', $selectedUserRoles)){ // teacher == editingteacher
            throw new invalid_parameter_exception(get_string('service:get_course_sections:invalidrole', 'local_teachermatic'));
        }

        $course = get_course($params['course_id']);
        $courseFormat = course_get_format($course);
        $courseSections = get_fast_modinfo($course->id)->get_section_info_all();

        $formattedCourseSections = [];

        // Excluding sections for course that use singleactivity format
        // since there is no sections available
        if ($courseFormat->get_format() !== 'singleactivity') {
            foreach ($courseSections as $courseSection) {

                $sectionName = $courseSection->name;

                if ($sectionName == null) {
                    $sectionName = course_get_format($course)
                        ->get_default_section_name($courseSection);
                };

                $formattedCourseSections[] = [
                    'id' => $courseSection->section,
                    'name' => $sectionName,
                ];
            }
        }

        return [
            'status' => true,
            'organisation_id' => $storedOrgId,
            'course' => [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'format' => $courseFormat->get_format(),
                'sections' => $formattedCourseSections
            ]
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The web service status'),
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation ID'),
            'course' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The course id'),
                'shortname' => new external_value(PARAM_TEXT, 'The course shortname'),
                'fullname' => new external_value(PARAM_TEXT, 'The course fullname'),
                'format' => new external_value(PARAM_TEXT, 'The course format'),
                'sections' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'The section id'),
                        'name' => new external_value(PARAM_TEXT, 'The section name'),
                    ])
                )
            ]),
        ]);
    }
}
