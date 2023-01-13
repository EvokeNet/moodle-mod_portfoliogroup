<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Grade utility class helper
 *
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class grade {
    public function get_portfolio_with_evaluation($courseid) {
        global $DB;

        $sql = 'SELECT * FROM {portfoliogroup} WHERE course = :courseid AND grade <> 0';

        return $DB->get_record_sql($sql, ['courseid' => $courseid]);
    }

    public function user_has_grade($portfolio, $userid) {
        $usergrade = $this->get_user_grade_object($portfolio, $userid);

        if ($usergrade) {
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

    public function get_group_course_grade($courseid, $userid) {
        $portfolio = $this->get_portfolio_with_evaluation($courseid);

        if (!$portfolio) {
            return false;
        }

        return $this->get_group_grade_string($portfolio, $userid);
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

        $usergrade = $this->get_group_grade($portfolio, $groupid);

        if (!$usergrade) {
            return false;
        }

        if ($portfolio->grade > 0) {
            return (int)$usergrade;
        }

        $scale = $DB->get_record('scale', ['id' => abs($portfolio->grade)], '*', MUST_EXIST);

        $scales = explode(',', $scale->scale);

        $scaleindex = (int)$usergrade - 1;

        return $scales[$scaleindex];
    }

    public function grade_user($portfolio, $userid, $grade) {
        global $CFG;

        $grades[$userid] = new \stdClass();
        $grades[$userid]->userid = $userid;
        $grades[$userid]->rawgrade = $grade;

        $this->update_user_grades($portfolio->id, $grades);

        require_once($CFG->libdir . '/gradelib.php');

        grade_update('mod/portfoliogroup', $portfolio->course, 'mod', 'portfoliogroup', $portfolio->id, 0, $grades);
    }

    private function update_user_grades($portfolioid, $grades) {
        global $DB, $USER;

        foreach ($grades as $grade) {
            $dbgrade = $this->get_user_grade_object($portfolioid, $grade->userid);

            if ($dbgrade) {
                $dbgrade->grader = $USER->id;
                $dbgrade->grade = $grade->rawgrade;
                $dbgrade->timemodified = time();

                $DB->update_record('portfoliogroup_grades', $dbgrade);

                continue;
            }

            $usergrade = new \stdClass();
            $usergrade->portfolioid = $portfolioid;
            $usergrade->userid = $grade->userid;
            $usergrade->grader = $USER->id;
            $usergrade->grade = $grade->rawgrade;
            $usergrade->timecreated = time();
            $usergrade->timemodified = time();

            $DB->insert_record('portfoliogroup_grades', $usergrade);
        }
    }
}
