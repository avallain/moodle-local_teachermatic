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

namespace local_teachermatic\event;

use core\event\base;

/**
 * Failed to create truefalse question event
 *
 * @package   local_teachermatic
 * @copyright 2024, Teachermatic <teachermatic.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_truefalse_failed extends base
{
    /**
     * Initialize event
     *
     * @return void
     */
    protected function init() {
        $this->data["crud"] = "c";
        $this->data["edulevel"] = self::LEVEL_TEACHING;
    }

    /**
     * Event name
     *
     * @return void
     */
    public static function get_name() {
        return get_string(
            "error:event:createtruefalsefailed",
            "local_teachermatic",
        );
    }

    /**
     * Event description
     *
     * @return void
     */
    public function get_description() {
        global $CFG;

        $message = $this->other["message"] ?? "Unknown.";
        if (
            !empty($this->other["file"]) &&
            defined("DEBUG_DEVELOPER") &&
            $CFG->debug == DEBUG_DEVELOPER
        ) {
            $message .= ' | ' . $this->other['file'];
        }

        if (
            !empty($this->other["line"]) &&
            defined("DEBUG_DEVELOPER") &&
            $CFG->debug == DEBUG_DEVELOPER
        ) {
            $message .= ':' . $this->other['line'];
        }

        $description = get_string(
            "error:event:createtruefalsefaileddescription",
            "local_teachermatic",
            [
                "userid" => $this->userid,
                "courseid" => $this->courseid,
                "message" => $message,
            ],
        );

        if (
            !empty($this->other["trace"]) &&
            defined("DEBUG_DEVELOPER") &&
            $CFG->debug == DEBUG_DEVELOPER
        ) {
            $description .= get_string(
                "error:event:trace",
                "local_teachermatic",
                [
                    "trace" => $this->other["trace"],
                ],
            );
        }

        return $description;
    }

    /**
     * Course Url
     *
     * @return void
     */
    public function get_url() {
        return new \moodle_url("/course/view.php", ["id" => $this->courseid]);
    }
}
