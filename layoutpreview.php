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
$type = required_param('type', PARAM_ALPHA);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'portfoliogroup');
$portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/portfoliogroup/layout.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($portfoliogroup->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->add_body_class($type);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_portfoliogroup');

$contentrenderable = new \mod_portfoliogroup\output\layoutpreview($portfoliogroup, $context, $type);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
