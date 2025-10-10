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

use core\exception\invalid_parameter_exception;
use core\exception\required_capability_exception;
use dml_missing_record_exception;
use externallib_advanced_testcase;

/**
 * Unit tests for the create_course_mod_resource function.
 *
 * @package             local_teachermatic
 * @group               local_teachermatic
 * @category            external
 * @coversDefaultClass  \local_teachermatic\external\create_course_mod_resource
 * @copyright           2024, Teachermatic <teachermatic.com>
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class create_course_mod_resource_test extends externallib_advanced_testcase {
    /** @var \stdClass */
    protected $course;

    /** @var \stdClass */
    protected $teacher;

    /** @var \stdClass */
    protected $student;

    /** @var int */
    protected $sectionid;

    /** @var string */
    protected $tempfilepath;

    /**
     * Set up the test environment before each test.
     * @return void
     */
    protected function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
        $this->student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, 'student');

        set_config('organisationid', 'test-org-123', 'local_teachermatic');

        $this->sectionid = $DB->get_field('course_sections', 'id', ['course' => $this->course->id, 'section' => 1], MUST_EXIST);
    }

    /**
     * Clean up the temporary file after each test.
     * @return void
     */
    protected function tearDown(): void {
        if (!empty($this->tempfilepath) && file_exists($this->tempfilepath)) {
            unlink($this->tempfilepath);
        }
        parent::tearDown();
    }

    /**
     * Test the successful creation of a resource by a teacher.
     * @covers ::execute
     */
    public function test_execute_with_valid_teacher(): void {
        $this->markTestSkipped('Test doing real HTTP request, need to mock it.');

        $this->setUser($this->teacher);

        $fs = get_file_storage();
        $filerecord = [
            'contextid' => \context_system::instance()->id,
            'component' => 'local_teachermatic',
            'filearea'  => 'test_files',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => 'test.txt',
        ];

        $dummyfilepath = $fs->create_file_from_string($filerecord, 'This is a test file.');
        $dummyfileurl = \moodle_url::make_pluginfile_url(
            $dummyfilepath->get_contextid(),
            $dummyfilepath->get_component(),
            $dummyfilepath->get_filearea(),
            $dummyfilepath->get_itemid(),
            $dummyfilepath->get_filepath(),
            $dummyfilepath->get_filename()
        )->out(false);

        $result = create_course_mod_resource::execute(
            $this->course->id,
            $this->sectionid,
            $this->teacher->email,
            'Test File Name',
            $dummyfileurl
        );

        $this->assertTrue($result['status']);
        $this->assertEquals('test-org-123', $result['organisation_id']);
        $this->assertEquals('Test File Name', $result['module']['name']);
        $this->assertRecordExists('course_modules', ['course' => $this->course->id, 'instance' => $result['module']['id']]);
        $this->assertRecordExists('resource', ['id' => $result['module']['id'], 'name' => 'Test File Name']);
    }

    /**
     * Test that the function fails when the user is a student.
     * @covers ::execute
     */
    public function test_execute_fails_for_student(): void {
        $this->setUser($this->student);

        $this->expectException(required_capability_exception::class);

        create_course_mod_resource::execute(
            $this->course->id,
            $this->sectionid,
            $this->student->email,
            'Student File',
            'http://example.com/file.pdf'
        );
    }

    /**
     * Test that the function fails with a non-existent email.
     * @covers ::execute
     */
    public function test_execute_fails_with_invalid_email(): void {
        $this->setUser($this->teacher);

        $this->expectException(invalid_parameter_exception::class);

        create_course_mod_resource::execute(
            $this->course->id,
            $this->sectionid,
            'notauser@example.com',
            'Invalid Email File',
            'http://example.com/file.pdf'
        );
    }

    /**
     * Test that the function fails with a non-existent course ID.
     * @covers ::execute
     */
    public function test_execute_fails_with_invalid_course_id(): void {
        $this->setUser($this->teacher);

        $this->expectException(dml_missing_record_exception::class);

        create_course_mod_resource::execute(
            9999, // Non-existent course ID.
            0,
            $this->teacher->email,
            'Invalid Course File',
            'http://example.com/file.pdf'
        );
    }

    /**
     * Test that the function fails if the organisation ID is not set.
     * @covers ::execute
     */
    public function test_execute_fails_without_organisation_id(): void {
        $this->setUser($this->teacher);
        set_config('organisationid', null, 'local_teachermatic');

        $this->expectException(invalid_parameter_exception::class);
        $this->expectExceptionMessage(get_string('service:noorganisationid', 'local_teachermatic'));

        create_course_mod_resource::execute(
            $this->course->id,
            $this->sectionid,
            $this->teacher->email,
            'No Org ID File',
            'http://example.com/file.pdf'
        );
    }
}
