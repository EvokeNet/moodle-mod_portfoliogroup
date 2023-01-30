<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Grade utility class helper
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class grade {
    public function get_portfolio_with_evaluation($courseid) {
        global $DB;

        $sql = 'SELECT * FROM {portfoliogroup} WHERE course = :courseid AND grade <> 0';

        return $DB->get_record_sql($sql, ['courseid' => $courseid]);
    }

    public function group_has_grade($portfolio, $groupid) {
        $groupgrade = $this->get_group_grade_object($portfolio, $groupid);

        if ($groupgrade) {
            return true;
        }

        return false;
    }

    public function get_group_grade($portfolio, $groupid) {
        $groupgrade = $this->get_group_grade_object($portfolio, $groupid);

        if (!$groupgrade) {
            return false;
        }

        return $groupgrade->grade;
    }

    public function get_group_course_grade($courseid, $groupid) {
        $portfolio = $this->get_portfolio_with_evaluation($courseid);

        if (!$portfolio) {
            return false;
        }

        return $this->get_group_grade_string($portfolio, $groupid);
    }

    public function get_group_grade_object($portfolio, $groupid) {
        global $DB;

        if ($portfolio->grade == 0) {
            return false;
        }

        return $DB->get_record('portfoliogroup_grades', [
            'portfolioid' => $portfolio->id,
            'groupid' => $groupid
        ]);
    }

    public function get_group_grade_string($portfolio, $groupid) {
        global $DB;

        $groupgrade = $this->get_group_grade($portfolio, $groupid);

        if (!$groupgrade) {
            return false;
        }

        if ($portfolio->grade > 0) {
            return (int)$groupgrade;
        }

        $scale = $DB->get_record('scale', ['id' => abs($portfolio->grade)], '*', MUST_EXIST);

        $scales = explode(',', $scale->scale);

        $scaleindex = (int)$groupgrade - 1;

        return $scales[$scaleindex];
    }

    public function grade_group($portfolio, $groupid, $grade) {
        global $CFG;

        $grouputil = new group();

        $group = $grouputil->get_group($groupid);

        $groupmembers = $grouputil->get_group_members($group->id, false);

        if (!$groupmembers) {
            return true;
        }

        $this->update_group_grade($portfolio, $group->id, $grade);

        $grades = [];
        foreach ($groupmembers as $groupmember) {
            $grades[$groupmember->id] = new \stdClass();
            $grades[$groupmember->id]->userid = $groupmember->id;
            $grades[$groupmember->id]->rawgrade = $grade;
        }

        require_once($CFG->libdir . '/gradelib.php');

        return grade_update('mod/portfoliogroup', $portfolio->course, 'mod', 'portfoliogroup', $portfolio->id, 0, $grades);
    }

    private function update_group_grade($portfolio, $groupid, $grade) {
        global $DB, $USER;

        $dbgrade = $this->get_group_grade_object($portfolio, $groupid);

        if ($dbgrade) {
            $dbgrade->grader = $USER->id;
            $dbgrade->grade = $grade;
            $dbgrade->timemodified = time();

            $DB->update_record('portfoliogroup_grades', $dbgrade);

            return $dbgrade;
        }

        $groupgrade = new \stdClass();
        $groupgrade->portfolioid = $portfolio->id;
        $groupgrade->groupid = $groupid;
        $groupgrade->grader = $USER->id;
        $groupgrade->grade = $grade;
        $groupgrade->timecreated = time();
        $groupgrade->timemodified = time();

        $id = $DB->insert_record('portfoliogroup_grades', $groupgrade);

        $groupgrade->id = $id;

        return $groupgrade;
    }
}
