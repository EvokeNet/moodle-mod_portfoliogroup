<?php

/**
 * The task that provides all the steps to perform a complete backup is defined here.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

require_once($CFG->dirroot.'//mod/portfoliogroup/backup/moodle2/backup_portfoliogroup_stepslib.php');

/**
 * Provides all the settings and steps to perform a complete backup of mod_portfoliogroup.
 */
class backup_portfoliogroup_activity_task extends backup_activity_task {

    /**
     * Defines particular settings for the plugin.
     */
    protected function define_my_settings() {
        return;
    }

    /**
     * Defines particular steps for the backup process.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_portfoliogroup_activity_structure_step('portfoliogroup_structure', 'portfoliogroup.xml'));
    }

    /**
     * Codes the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of choices.;
        $search = "/(".$base."\/mod\/portfoliogroup\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@portfoliogroupINDEX*$2@$', $content);

        // Link to choice view by moduleid.
        $search = "/(".$base."\/mod\/portfoliogroup\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@portfoliogroupVIEWBYID*$2@$', $content);

        return $content;
    }
}
