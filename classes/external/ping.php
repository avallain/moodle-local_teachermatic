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
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web service to check/ping the plugin status.
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ping extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'organisation_id' => new external_value(PARAM_TEXT, 'The organisation ID', VALUE_DEFAULT, '-1')
        ]);
    }

    public static function execute(string $organisationId): array
    {
        $params = self::validate_parameters(self::execute_parameters(), ['organisation_id' => $organisationId]);

        if ($params['organisation_id'] == '-1') {
            // just tell them that we are live :)
            return [
                'status' => true,
            ];
        }

        $storedOrgId = get_config('local_teachermatic', 'organisationid');
        if (!$storedOrgId) {
            throw new invalid_parameter_exception(get_string('service:noorganisationid', 'local_teachermatic'));
        }

        if ($storedOrgId != $params['organisation_id']) {
            throw new invalid_parameter_exception(get_string('service:invalidorganisationid', 'local_teachermatic'));
        }

        return [
            'status' => true,
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'The web service status'),
        ]);
    }
}
