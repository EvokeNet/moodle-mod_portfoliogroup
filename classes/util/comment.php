<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Reaction utility class helper
 *
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class comment {
    public function get_total_course_comments($courseid, $groupid) {
        global $DB;

        $sql = 'SELECT count(*)
                FROM {portfoliogroup_comments} c
                INNER JOIN {portfoliogroup_entries} e ON e.id = c.entryid
                WHERE e.courseid = :courseid AND e.groupid = :groupid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid, 'groupid' => $groupid]);
    }
}
