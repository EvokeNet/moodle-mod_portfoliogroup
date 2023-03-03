<?php

/**
 * Upgrade file.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the eMailTest local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 *
 * @return bool result
 *
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_portfoliogroup_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2023012300) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('portfoliogroup');
        if ($dbman->table_exists($table)) {
            $completionfield = new xmldb_field('completionrequiresubmit', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'grade');

            $dbman->add_field($table, $completionfield);
        }

        upgrade_plugin_savepoint(true, 2023012300, 'mod', 'portfoliogroup');
    }

    if ($oldversion < 2023030200) {
        $entries = $DB->get_records('portfoliogroup_entries');

        $entryutil = new \mod_portfoliogroup\util\entry();

        foreach ($entries as $entry) {
            $entryutil->create_entry_thumbs($entry);
        }

        upgrade_plugin_savepoint(true, 2023030200, 'mod', 'portfoliogroup');
    }

    return true;
}
