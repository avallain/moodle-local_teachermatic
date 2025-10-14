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
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
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

    /**
     * Insert truefalse question into course question bank
     * @param integer $courseid
     * @param string $email
     * @param array $questions
     * @return array
     */
    public static function execute(int $courseid, string $email, array $questions): array {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');

        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $courseid,
            'email' => $email,
            'questions' => $questions,
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

        require_capability('moodle/question:add', $contextcourse, $user->id);

        foreach ($params['questions'] as $question) {
            // Context thingy.
            $contexts = new \core_question\local\bank\question_edit_contexts($contextcourse);
            $category = question_make_default_categories($contexts->all());

            // Ensure the qtype is enabled.
            \core_question\local\bank\helper::require_plugin_enabled('qbank_editquestion');

            $emptyquestion = new stdClass();
            $emptyquestion->qtype = 'truefalse';
            $qtypeobj = question_bank::get_qtype($emptyquestion->qtype);

            // Simulate form thingy.
            $newquestion = new stdClass();
            $newquestion->category = "{$category->id},{$category->contextid}";
            $newquestion->name = $question['question'];
            $newquestion->qtype = $emptyquestion->qtype;
            $newquestion->parent = 0;
            $newquestion->length = 1;
            $newquestion->penalty = 1.0; // Default penalty value.
            $newquestion->questiontext['text'] = $question['question'];
            $newquestion->questiontext['format'] = FORMAT_HTML;
            $newquestion->status = 'ready';
            $newquestion->defaultmark = 1;
            $newquestion->generalfeedback['text'] = '';
            $newquestion->generalfeedback['format'] = FORMAT_HTML;
            $newquestion->showstandardinstruction = 0;

            $newquestion->correctanswer = $question['answer'] ? 1 : 0;
            $newquestion->feedbacktrue['text'] = '';
            $newquestion->feedbacktrue['format'] = FORMAT_HTML;
            $newquestion->feedbackfalse['text'] = '';
            $newquestion->feedbackfalse['format'] = FORMAT_HTML;

            // The save_question() func already implemented DB Transaction.
            $qtypeobj->save_question($emptyquestion, $newquestion);
        }

        return [
            'status' => true,
            'organisation_id' => $organisationid,
            'questions' => $params['questions'],
        ];
    }

    /**
     * Return description of method result value
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
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
