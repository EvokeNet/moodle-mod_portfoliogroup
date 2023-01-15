<?php

/**
 * Prints user's public portfolio page.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

global $DB;

// Course id.
$id = required_param('id', PARAM_INT);
$groupid = required_param('g', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$group = $DB->get_record('groups', ['id' => $groupid], '*', MUST_EXIST);

$groupsutil = new \mod_portfoliogroup\util\group();

$usergroup = $groupsutil->get_user_group($course->id);

if ($usergroup->id == $group->id) {
    $instances = get_all_instances_in_course('portfoliogroup', $course);

    if ($instances) {
        $current = current($instances);

        redirect(new moodle_url('/mod/portfoliogroup/view.php', ['id' => $current->coursemodule]));
    }
}

$context = context_course::instance($course->id);

$PAGE->set_context($context);
$PAGE->set_url('/mod/portfoliogroup/portfolio.php', ['id' => $id, 'g' => $groupid]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->add_body_class('path-mod-portfoliogroup');

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_portfoliogroup');

$contentrenderable = new \mod_portfoliogroup\output\portfolio($context, $course, $group);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
