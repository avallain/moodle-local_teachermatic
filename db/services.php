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
 * External service definition for the local_teachermatic plugin.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_teachermatic_ping' => [
        'classname'   => 'local_teachermatic\external\ping',
        'methodname' => 'execute',
        'description' => 'Get the web service status.',
        'type' => 'read',
        'ajax' => true
    ],
    'local_teachermatic_get_courses' => [
        'classname'   => 'local_teachermatic\external\get_courses',
        'methodname' => 'execute',
        'description' => 'Get the user enrolled courses by email address.',
        'type' => 'read',
        'ajax' => true
    ],
    'local_teachermatic_create_multichoice' => [
        'classname'   => 'local_teachermatic\external\create_multichoice',
        'methodname' => 'execute',
        'description' => 'Create multiple choice questions.',
        'type' => 'create',
        'ajax' => true
    ],
    'local_teachermatic_create_truefalse' => [
        'classname'   => 'local_teachermatic\external\create_truefalse',
        'methodname' => 'execute',
        'description' => 'Create true-false questions.',
        'type' => 'create',
        'ajax' => true
    ],
    'local_teachermatic_create_shortanswer' => [
        'classname'   => 'local_teachermatic\external\create_shortanswer',
        'methodname' => 'execute',
        'description' => 'Create short-answer questions.',
        'type' => 'create',
        'ajax' => true
    ],
];

$services = [
    'Teachermatic External Service' => [
        'functions' => [
            'local_teachermatic_ping',
            'local_teachermatic_get_courses',
            'local_teachermatic_create_multichoice',
            'local_teachermatic_create_truefalse',
            'local_teachermatic_create_shortanswer',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'teachermatic'
    ]
];
