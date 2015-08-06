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
 * Condition main class.
 *
 * @package availability_role
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_role;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package availability_role
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int ID of role that this condition requires, or 0 = any role */
    protected $roleid;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get group id.
        if (!property_exists($structure, 'id')) {
            $this->roleid = 0;
        } else if (is_int($structure->id)) {
            $this->roleid = $structure->id;
        } else {
            throw new \coding_exception('Invalid ->id for role condition');
        }
    }

    public function save() {
        $result = (object)array('type' => 'role');
        if ($this->roleid) {
            $result->id = $this->roleid;
        }
        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $course = $info->get_course();
        $context = \context_course::instance($course->id);
        $allow = false;
        if(user_has_role_assignment($userid, $this->roleid, $context->id)){
            $allow = true;
        }

        if ($not) {
                $allow = !$allow;
            }

        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        global $DB;

        if ($this->roleid) {
            // Need to get the name for the role. Unfortunately this requires
            // a database query. To save queries, get all role for course at
            // once in a static cache.
            $course = $info->get_course();
            $context = \context_course::instance($course->id);
            $roles = get_all_roles($context);
            foreach($roles as $g){
                if($g->id==$this->roleid){
                    if(strlen($g->coursealias)==0)$name = $g->name;
                     else $name = $g->coursealias;
                    if(strlen($name)==0)$name = $g->shortname;
                    break;
                }
            }

            // If it still doesn't exist, it must have been misplaced.
            if (strlen($name)==0) {
                $name = get_string('missing', 'availability_role');
            }
        } else {
            return get_string($not ? 'requires_notanyrole' : 'requires_anyrole',
                    'availability_role');
        }

        return get_string($not ? 'requires_notrole' : 'requires_role',
                'availability_role', $name);
    }

    protected function get_debug_string() {
        return $this->roleid ? '#' . $this->roleid : 'any';
    }

 

    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'groups' && (int)$this->roleid === (int)$oldid) {
            $this->roleid = $newid;
            return true;
        } else {
            return false;
        }
    }


    public function is_applied_to_user_lists() {
        return false;
    }

}
