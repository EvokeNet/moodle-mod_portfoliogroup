<?php

declare(strict_types=1);

namespace mod_portfoliogroup\completion;

use core_completion\activity_custom_completion;
use mod_portfoliogroup\util\entry;
use mod_portfoliogroup\util\group;

/**
 * Activity custom completion subclass for the Assign Tutor activity.
 *
 * Class for defining mod_portfoliogroup's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given peerreview instance and a user.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $portfoliogroupid = $this->cm->instance;

        if (!$portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $portfoliogroupid])) {
            throw new \moodle_exception('Unable to find portfoliogroup with id ' . $portfoliogroupid);
        }

        if ($rule == 'completionrequiresubmit') {
            $entryutil = new entry();
            $grouputil = new group();

            if (!$group = $grouputil->get_user_group($portfoliogroup->course, $this->userid)) {
                return COMPLETION_INCOMPLETE;
            }

            if ($entryutil->group_has_entry_in_portfolio_instance($portfoliogroup->id, $group->id)) {
                return COMPLETION_COMPLETE;
            }
        }

        return COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionrequiresubmit'];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionrequiresubmit' => get_string('completionrequiresubmit', 'mod_portfoliogroup')
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionrequiresubmit',
            'completionusegrade'
        ];
    }
}
