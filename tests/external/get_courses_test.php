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
global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use externallib_advanced_testcase;

/**
 * Unit tests for the get_courses function.
 *
 * @package     local_teachermatic
 * @category    external
 * @copyright   2024, Teachermatic <teachermatic.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_test extends externallib_advanced_testcase
{

    public function test_execute_with_valid_teacher_email(): void
    {
        $this->resetAfterTest(true);

        set_config('organisationid', 'org1', 'local_teachermatic');
        
        $user = $this->getDataGenerator()->create_user([
            'email' => 'teacher1@local.test'
        ]);
        
        $course = $this->getDataGenerator()->create_course([
            'name' => 'Test Course'
        ]);
        
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        
        $response = get_courses::execute('teacher1@local.test');
        $response = \core_external\external_api::clean_returnvalue(
            get_courses::execute_returns(),
            $response
        );

        $this->assertNotNull($response);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertTrue($response['status']);
        $this->assertArrayHasKey('courses', $response);
        $this->assertArrayHasKey('organisation_id', $response);
    }
}
