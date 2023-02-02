<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Reaction utility class helper
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class reaction {
    const LIKE = 1;

    public function toggle_reaction($entryid, $reactionid) {
        global $USER, $DB;

        $params = [
            'entryid' => $entryid,
            'userid' => $USER->id,
            'reaction' => $reactionid
        ];

        $reaction = $DB->get_record('portfoliogroup_reactions', $params);

        if ($reaction) {
            $DB->delete_records('portfoliogroup_reactions', ['id' => $reaction->id]);
        } else {
            $params['timecreated'] = time();
            $params['timemodified'] = time();

            $insertedid = $DB->insert_record('portfoliogroup_reactions', $params);

            $params['id'] = $insertedid;

            $reaction = (object) $params;

            $this->dispatch_event($reaction);
        }

        return $this->get_total_reactions($entryid, $reactionid);
    }

    private function dispatch_event($reaction) {
        global $DB;

        $sql = 'SELECT p.id, p.course, e.userid, e.groupid
                FROM {portfoliogroup_entries} e
                INNER JOIN {portfoliogroup} p ON p.id = e.portfolioid
                WHERE e.id = :entryid';

        $portfolio = $DB->get_record_sql($sql, ['entryid' => $reaction->entryid]);

        $cm = get_coursemodule_from_instance('portfoliogroup', $portfolio->id);

        $context = \context_module::instance($cm->id);

        $eventparams = array(
            'context' => $context,
            'objectid' => $reaction->id,
            'courseid' => $portfolio->course,
            'relateduserid' => $portfolio->userid,
            'other' => [
                'groupid' => $portfolio->groupid,
            ]
        );

        $event = \mod_portfoliogroup\event\like_sent::create($eventparams);
        $event->add_record_snapshot('portfoliogroup_reactions', $reaction);
        $event->trigger();
    }

    public function get_total_reactions($entryid, $reactionid = 1) {
        global $DB;

        return $DB->count_records('portfoliogroup_reactions', [
            'entryid' => $entryid,
            'reaction' => $reactionid
        ]);
    }

    public function get_total_course_reactions($courseid, $groupid, $reactionid = 1) {
        global $DB;

        $sql = 'SELECT count(*)
                FROM {portfoliogroup_reactions} r
                INNER JOIN {portfoliogroup_entries} e ON e.id = r.entryid
                WHERE e.courseid = :courseid AND e.groupid = :groupid AND r.reaction = :reactionid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid, 'groupid' => $groupid, 'reactionid' => $reactionid]);
    }

    public function user_reacted($entryid, $reactionid) {
        global $USER, $DB;

        $params = [
            'entryid' => $entryid,
            'userid' => $USER->id,
            'reaction' => $reactionid
        ];

        $reaction = $DB->get_record('portfoliogroup_reactions', $params);

        if ($reaction) {
            return true;
        }

        return false;
    }
}
