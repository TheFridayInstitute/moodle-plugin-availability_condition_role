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
 * Front-end class.
 *
 * @package availability_role
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_role;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package availability_role
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /** @var array Array of group info for course */
    protected $allgroups;
    /** @var int Course id that $allgroups is for */
    protected $allgroupscourseid;

    protected function get_javascript_strings() {
        return array('anygroup');
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        // Get all groups for course.
        $groups = $this->get_all_roles($course->id);

        // Change to JS array format and return.
        $jsarray = array();
        $context = \context_course::instance($course->id);
        foreach ($groups as $rec) {
            if(strlen($rec->coursealias)==0)$name = $rec->name;
            else $name = $rec->coursealias;
            if(strlen($name)==0)$name = $rec->shortname;
            $jsarray[] = (object)array('id' => $rec->id, 'name' =>
                    format_string($name, true, array('context' => $context)));
        }
        return array($jsarray);
    }

    /**
     * Gets all groups for the given course.
     *
     * @param int $courseid Course id
     * @return array Array of all the group objects
     */
    protected function get_all_roles($courseid) {
        global $CFG;

        if ($courseid != $this->allgroupscourseid) {
            $this->allgroups = get_all_roles(\context_course::instance($courseid));
            $this->allgroupscourseid = $courseid;
        }
        return $this->allgroups;
    }

    protected function allow_add($course, \cm_info $cm = null,
            \section_info $section = null) {
        global $CFG;

        // Only show this option if there are some groups.
        return true;
    }
}
