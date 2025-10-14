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

defined('MOODLE_INTERNAL') || die();

use core\exception\invalid_parameter_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;

global $CFG;
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * Web service to get available courses and it's sections by the user email address
 * where the user is enrolled as 'editingteacher'.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_with_sections extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED),
        ]);
    }

    /**
     * Fetch and returns courses and its sections where $email
     * is enrolled as editingteacher.
     * @param string $email
     * @return array
     */
    public static function execute(string $email): array {
        $params = self::validate_parameters(self::execute_parameters(), ['email' => $email]);

        $organisationid = get_config('local_teachermatic', 'organisationid');
        if (!$organisationid) {
            throw new invalid_parameter_exception(get_string('service:noorganisationid', 'local_teachermatic'));
        }

        $user = core_user::get_user_by_email($params['email'], 'id');
        if (!$user) {
            throw new invalid_parameter_exception(get_string('service:invalidemail', 'local_teachermatic'));
        }

        [, $courses] = get_user_capability_contexts(
            'moodle/course:update',
            false,
            $user->id,
            true,
            'fullname,shortname,format'
        );

        $normalizedcourses = [];
        if (!empty($courses)) {
            foreach ($courses as $usercourse) {
                $format = course_get_format($usercourse);
                if ($format->get_format() == 'singleactivity') {
                    continue;
                }

                $normalizedsections = [];
                $sections = get_fast_modinfo($usercourse)->get_section_info_all();
                foreach ($sections as $section) {
                    $name = $section->name;
                    if ($name == null) {
                        $name = $format->get_default_section_name($section);
                    };

                    $normalizedsections[] = [
                        'id' => $section->section,
                        'name' => $name,
                    ];
                }

                $normalizedcourses[] = [
                    'id' => $usercourse->id,
                    'fullname' => $usercourse->fullname,
                    'shortname' => $usercourse->shortname,
                    'sections' => $normalizedsections,
                ];
            }
        }

        return [
            'status' => true,
            'organisation_id' => $organisationid,
            'courses' => (count($normalizedcourses) > 0) ? $normalizedcourses : [],
        ];
    }

    /**
     * Return description of method result value
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The web service status'),
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation id'),
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'The course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'The course short name'),
                    'fullname' => new external_value(PARAM_TEXT, 'The course full name'),
                    'sections' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'The section id'),
                            'name' => new external_value(PARAM_TEXT, 'The section name'),
                        ])
                    ),
                ])
            ),
        ]);
    }
}
