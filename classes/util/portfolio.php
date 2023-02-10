<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Portfolio utility class helper
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class portfolio {
    protected $context;
    protected $courseid;

    public function __construct($context, $courseid) {
        $this->context = $context;
        $this->courseid = $courseid;
    }

    public function get_course_portfolios() {
        $groupsutil = new group();

        $groups = $groupsutil->get_course_groups($this->courseid, false);

        if (!$groups) {
            return [];
        }

        $this->fill_portfolios_with_extra_data($groups);

        shuffle($groups);

        return array_values($groups);
    }

    private function fill_portfolios_with_extra_data($groups) {
        $reactionutil = new reaction();
        $commentutil = new comment();
        $entryutil = new entry();
        $layoututil = new layout();
        $logutil = new log();
        $grouputil = new group();

        $lastaccesstoportfolios = $logutil->get_last_time_accessed_portfolios($this->courseid);

        foreach ($groups as $group) {
            $group->totallikes = $reactionutil->get_total_course_reactions($this->courseid, $group->id);
            $group->totalcomments = $commentutil->get_total_course_comments($this->courseid, $group->id);
            $group->totalentries = $entryutil->get_total_course_entries($this->courseid, $group->id);
            $group->layout = $layoututil->get_group_layout($this->courseid, $group->id, 'timeline');
            $group->lastentry = $entryutil->get_last_course_entry($this->courseid, $group->id);
            $group->members = $grouputil->get_group_members($group->id, true, $this->context);

            $group->hasnews = false;

            if ($group->lastentry && $group->lastentry->timecreated > $lastaccesstoportfolios) {
                $group->hasnews = true;
            }
        }
    }
}
