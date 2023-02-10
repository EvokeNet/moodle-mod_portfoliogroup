<?php

/**
 * Display information about all the mod_portfoliogroup modules in the requested course.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

require_once(__DIR__.'/lib.php');

// Course module id.
$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'portfoliogroup');
$portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course);

$context = context_module::instance($id);

require_capability('mod/portfoliogroup:grade', $context);

$pagetitle = format_string($course->fullname);

$PAGE->set_url('/mod/portfoliogroup/indextable.php', ['id' => $id]);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_context($context);

$PAGE->navbar->add($pagetitle);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_portfoliogroup');

$contentrenderable = new \mod_portfoliogroup\output\indextable($context, $portfoliogroup);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
