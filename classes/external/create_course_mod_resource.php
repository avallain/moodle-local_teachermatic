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
use context_module;
use core\exception\invalid_parameter_exception;
use core\exception\moodle_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;
use stdClass;

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/course/modlib.php');

/**
 * Web service to ...
 * where the user is enrolled as 'editingteacher'.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_course_mod_resource extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'The course ID', VALUE_REQUIRED),
            'section_id' => new external_value(PARAM_INT, 'The section ID', VALUE_REQUIRED),
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED),
            'file_name' => new external_value(PARAM_TEXT, 'The file name', VALUE_REQUIRED),
            'file_url' => new external_value(PARAM_URL, 'The file downloadable url', VALUE_REQUIRED),
        ]);
    }

    public static function execute(int $courseId, int $sectionId, string $email, string $fileName, string $fileUrl): array
    {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'email' => $email,
            'course_id' => $courseId,
            'section_id' => $sectionId,
            'file_name' => $fileName,
            'file_url' => $fileUrl,
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
            throw new invalid_parameter_exception(get_string('service:create_course_mod_resource:invalidrole', 'local_teachermatic'));
        }

        // File activity is = 'mod_resource' = 'resource' in 'modules' table
        $modResource = $DB->get_record('modules', ['name' => 'resource'], '*', MUST_EXIST);
        if($modResource->visible == 0){
            throw new moodle_exception(
                'error:modresourcenotavailable',
                '',
                '',
                null,
                'mod_resource is not enable'
            );
        }

        $course = get_course($params['course_id']);

        // Use moodle transaction to prevent creating empty module
        // when there is an error while downloading the file
        $transaction = $DB->start_delegated_transaction();

        $resource = new stdClass();
        $resource->modulename = $modResource->name;
        $resource->module = $modResource->id;
        $resource->section = $params['section_id'];
        $resource->course = $course->id;
        $resource->name = $params['file_name'];
        $resource->introformat = FORMAT_HTML;
        $resource->display = RESOURCELIB_DISPLAY_OPEN;
        $resource->showdescription = 1;
        $resource->visible = 1;

        $newModule = add_moduleinfo($resource, $course, null);

        if (!$newModule) {
            throw new moodle_exception(
                'cannotaddnewmodule',
                '',
                '',
                null,
                'can not add a new module'
            );
        }

        $contextModule = context_module::instance($newModule->coursemodule);
        $fs = get_file_storage();

        // ref: mod/resource/mod_form.php L157
        $fileinfo = [
            'contextid' => $contextModule->id,
            'component' => 'mod_' . $modResource->name, // file upload = mod_resource.
            'filearea'  => 'content',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => basename($params['file_name']),
        ];

        // NOTE: Perhaps we need to handle when the server response with 302 code
        $fs->create_file_from_url($fileinfo, $params['file_url']);

        $transaction->allow_commit();

        return [
            'status' => true,
            'organisation_id' => $storedOrgId,
            'module' => [
                'id' => $newModule->id,
                'name' => $newModule->name,
                'module' => $modResource->name
            ]
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The web service status'),
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation ID'),
            'module' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The created module ID'),
                'name' => new external_value(PARAM_TEXT, 'The created module name'),
                'module' => new external_value(PARAM_TEXT, 'The created module resource'),
            ]),
        ]);
    }
}
