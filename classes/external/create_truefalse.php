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
use question_bank;
use stdClass;

/**
 * Web service to create truefalse questions
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_truefalse extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'The course ID', VALUE_REQUIRED),
            'email' => new external_value(PARAM_EMAIL, 'The user email address', VALUE_REQUIRED),
            'questions' => new external_multiple_structure(
                new external_single_structure([
                    'question' => new external_value(PARAM_TEXT, 'The generated question'),
                    'answer' => new external_value(PARAM_BOOL, 'The correct answer'),
                ])
            ), 
        ]);
    }

    public static function execute(int $courseId, string $email, array $questions): array
    {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $courseId,
            'email' => $email,
            'questions' => $questions
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
        
        // Perhaps we need to perform capability check(?)
        // require_capability('moodle/question:add', $courseContext, $selectedUser->id);
        
        $selectedUserRoles = get_user_roles_in_course($selectedUser->id, $params['course_id']);
        $selectedUserRoles = explode(',', strtolower($selectedUserRoles));
        $selectedUserRoles = array_map(fn($value): string => trim(strip_tags($value)), $selectedUserRoles);

        if(!in_array('teacher', $selectedUserRoles)){ // teacher == editingteacher
            throw new invalid_parameter_exception(get_string('service:create_truefalse:invalidrole', 'local_teachermatic'));
        }
        
        foreach ($params['questions'] as $question) {

            // Context thingy
            $contexts = new \core_question\local\bank\question_edit_contexts($contextCourse);
            $defaultCategory = question_make_default_categories($contexts->all());
            
            // ensure the qtype is enabled
            \core_question\local\bank\helper::require_plugin_enabled('qbank_editquestion');

            // create empty question
            $emptyquestion = new \stdClass();
            $emptyquestion->qtype = 'truefalse';

            // create question_bank instance
            $qtypeobj = question_bank::get_qtype($emptyquestion->qtype);
            // simulate form thingy
            $newquestion = new \stdClass();
            $newquestion->category = "{$defaultCategory->id},{$defaultCategory->contextid}";
            $newquestion->name = $question['question'];
            $newquestion->qtype = $emptyquestion->qtype;
            $newquestion->parent = 0;
            $newquestion->length = 1;
            $newquestion->penalty = 1.0; // default penalty value
            $newquestion->questiontext['text'] = $question['question'];
            $newquestion->questiontext['format'] = FORMAT_HTML;
            $newquestion->status = 'ready'; //ready;draft
            $newquestion->defaultmark = 1; // 1 is default value
            $newquestion->generalfeedback['text'] = '';
            $newquestion->generalfeedback['format'] = FORMAT_HTML;
            $newquestion->showstandardinstruction = 0; // 0=no;1=yes

            $newquestion->correctanswer = $question['answer'] ? 1 : 0; // 0=false;1=true;
            $newquestion->feedbacktrue['text'] = '';
            $newquestion->feedbacktrue['format'] = FORMAT_HTML;
            $newquestion->feedbackfalse['text'] = '';
            $newquestion->feedbackfalse['format'] = FORMAT_HTML;


            // The save_question() func already implemented DB Transaction
            $qtypeobj->save_question($emptyquestion, $newquestion);
            
        }
        
        return [
            'status' => true,
            'organisation_id' => $storedOrgId,
            'questions' => $params['questions']
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The questions creation status'),
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation ID'),
            'questions' => new external_multiple_structure(
                new external_single_structure([
                    'question' => new external_value(PARAM_TEXT, 'The generated question'),
                    'answer' => new external_value(PARAM_BOOL, 'The correct answer'),
                ])
            ), 
        ]);
    }
}
