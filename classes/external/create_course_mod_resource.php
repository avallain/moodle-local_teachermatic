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

global $CFG;
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/course/modlib.php');

/**
 * Web service to create fileupload mod_resource into course
 * where the user is enrolled as 'editingteacher'.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_course_mod_resource extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'The course ID', VALUE_REQUIRED),
            'section_id' => new external_value(PARAM_INT, 'The section ID', VALUE_REQUIRED),
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED),
            'file_name' => new external_value(PARAM_TEXT, 'The file name', VALUE_REQUIRED),
            'file_url' => new external_value(PARAM_URL, 'The file downloadable url', VALUE_REQUIRED),
        ]);
    }

    /**
     * Create a fileupload mod_resource within $courseid
     * @param integer $courseid
     * @param integer $sectionid
     * @param string $email
     * @param string $filename
     * @param string $fileurl
     * @return array
     */
    public static function execute(int $courseid, int $sectionid, string $email, string $filename, string $fileurl): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'email' => $email,
            'course_id' => $courseid,
            'section_id' => $sectionid,
            'file_name' => $filename,
            'file_url' => $fileurl,
        ]);

        $contextcourse = context_course::instance($params['course_id']);
        self::validate_context($contextcourse);

        $organisationid = get_config('local_teachermatic', 'organisationid');
        if (!$organisationid) {
            throw new invalid_parameter_exception(get_string('service:noorganisationid', 'local_teachermatic'));
        }

        $user = core_user::get_user_by_email($params['email'], 'id');
        if (!$user) {
            throw new invalid_parameter_exception(get_string('service:invalidemail', 'local_teachermatic'));
        }

        require_capability('mod/resource:addinstance', $contextcourse, $user->id);

        // File activity is = 'mod_resource' = 'resource' in 'modules' table.
        $modresource = $DB->get_record('modules', ['name' => 'resource'], '*', MUST_EXIST);
        if ($modresource->visible == 0) {
            throw new moodle_exception(
                'error:coursemodulenotenabled',
                'local_teachermatic',
                '',
                null,
                'mod_resource is not enable'
            );
        }

        $course = get_course($params['course_id']);

        // Use moodle transaction to prevent creating empty module
        // when there is an error while downloading the file.
        $transaction = $DB->start_delegated_transaction();

        $draftitemid = 0;
        file_prepare_draft_area(
            $draftitemid,
            $contextcourse->id,
            'mod_resource',
            'content',
            0,
            ['subdirs' => true]
        );

        $resource = new stdClass();
        $resource->modulename = $modresource->name;
        $resource->module = $modresource->id;
        $resource->section = $params['section_id'];
        $resource->course = $course->id;
        $resource->name = $params['file_name'];
        $resource->introformat = FORMAT_HTML;
        $resource->display = RESOURCELIB_DISPLAY_OPEN;
        $resource->showdescription = 1;
        $resource->visible = 1;
        $resource->files = 0;

        $module = add_moduleinfo($resource, $course, null);

        if (!$module) {
            throw new moodle_exception(
                'error:cannotaddcoursemoduletosection',
                'local_teachermatic',
                '',
                null,
                'can not add a new module'
            );
        }

        $contextmodule = context_module::instance($module->coursemodule);
        $fs = get_file_storage();

        // Ref: mod/resource/mod_form.php L157.
        $fileinfo = [
            'contextid' => $contextmodule->id,
            'component' => 'mod_' . $modresource->name, // File upload = mod_resource.
            'filearea'  => 'content',
            'itemid'    => 0,
            'filepath'  => '/',
            'file_name'  => basename($params['file_name']),
        ];

        // NOTE: Perhaps we need to handle when the server response with 302 code.
        $fs->create_file_from_url($fileinfo, $params['file_url']);

        $transaction->allow_commit();

        return [
            'status' => true,
            'organisation_id' => $organisationid,
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'module' => $modresource->name,
            ],
        ];
    }

    /**
     * Return description of method result value
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
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
