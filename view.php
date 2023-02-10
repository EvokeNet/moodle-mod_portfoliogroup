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
$id = optional_param('id', null, PARAM_INT);
$portfolioid = optional_param('portfolioid', null, PARAM_INT);

if (!$id && !$portfolioid) {
    throw new Exception('Illegal access');
}

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'portfoliogroup');
    $portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($portfolioid) {
    list ($course, $cm) = get_course_and_cm_from_instance($portfolioid, 'portfoliogroup');
    $portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);
}

$context = context_module::instance($cm->id);

if (has_capability('mod/portfoliogroup:grade', $context) || is_siteadmin()) {
    redirect(new moodle_url('/mod/portfoliogroup/indextable.php', ['id' => $id]));
}

$groupsutil = new \mod_portfoliogroup\util\group();
$usergroups = $groupsutil->get_user_groups($course->id);

$layoututil = new \mod_portfoliogroup\util\layout();
if (!empty($usergroups) && !$layoututil->group_has_layout($course->id, current($usergroups)->id)) {
    redirect(new moodle_url('/mod/portfoliogroup/layout.php', ['id' => $id]));
}

require_course_login($course, true, $cm);

$event = \mod_portfoliogroup\event\course_module_viewed::create(array(
    'context' => $context,
    'objectid' => $portfoliogroup->id,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('portfoliogroup', $portfoliogroup);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/portfoliogroup/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($portfoliogroup->name));
$PAGE->set_heading(format_string($portfoliogroup->name));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_portfoliogroup');

$contentrenderable = new \mod_portfoliogroup\output\view($portfoliogroup, $context);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
