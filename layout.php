<?php

/**
 * Prints an instance of mod_portfoliogroup.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

global $DB;

// Course module id.
$id = required_param('id', PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'portfoliogroup');
$portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/portfoliogroup/layout.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($portfoliogroup->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if (!$action) {
    echo $OUTPUT->header();

    $renderer = $PAGE->get_renderer('mod_portfoliogroup');

    $contentrenderable = new \mod_portfoliogroup\output\layout($portfoliogroup, $context);

    echo $renderer->render($contentrenderable);

    echo $OUTPUT->footer();

    exit;
}

$courseid = required_param('courseid', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);
$layout = required_param('layout', PARAM_ALPHA);

$layoututil = new \mod_portfoliogroup\util\layout();

if ($layoututil->set_group_layout($courseid, $groupid, $layout)) {
    redirect(new moodle_url('/mod/portfoliogroup/view.php', ['id' => $id]), 'Preferences successfuly saved.');
}

redirect(new moodle_url('/mod/portfoliogroup/layout.php', ['id' => $id]), 'Error attempting to save your proferences.');