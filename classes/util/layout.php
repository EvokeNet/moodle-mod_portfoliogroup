<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Layout utility class helper
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class layout {
    public function group_has_layout($courseid, $groupid) {
        $userlayout = $this->get_group_layout($courseid, $groupid);

        if (!$userlayout) {
            return false;
        }

        return true;
    }

    public function get_group_layout($courseid, $groupid, $layout = null) {
        global $DB;

        $layoutdb = $DB->get_record('portfoliogroup_group_layout', ['courseid' => $courseid, 'groupid' => $groupid]);

        if ($layoutdb) {
            return $layoutdb->layout;
        }

        // If user has not a layout preference, but we pass a forced default, so we return it.
        if ($layout) {
            return $layout;
        }

        return null;
    }

    public function set_group_layout($courseid, $groupid, $layout) {
        global $DB, $USER;

        $layoutdb = $DB->get_record('portfoliogroup_group_layout', ['courseid' => $courseid, 'groupid' => $groupid]);

        if ($layoutdb) {
            $layoutdb->layout = $layout;
            $layoutdb->timemodified = time();
            $layoutdb->userid = $USER->id;

            $DB->update_record('portfoliogroup_group_layout', $layoutdb);

            return $layout;
        }

        $layoutdb = new \stdClass();

        $layoutdb->courseid = $courseid;
        $layoutdb->groupid = $groupid;
        $layoutdb->userid = $USER->id;
        $layoutdb->layout = $layout;
        $layoutdb->timecreated = time();
        $layoutdb->timemodified = time();

        $DB->insert_record('portfoliogroup_group_layout', $layoutdb);

        return $layout;
    }
}
