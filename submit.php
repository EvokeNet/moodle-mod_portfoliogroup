<?php

/**
 * Submit portfolio entry.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$entryid = optional_param('entryid', null, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'portfoliogroup');

require_course_login($course, true);

$portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

$grouputil = new \mod_portfoliogroup\util\group();
$usergroup = $grouputil->get_user_group($portfoliogroup->course);

if (!$usergroup) {
    throw new \Exception('You are not a member of a group!');
}

if ($entryid) {
    $entry = $DB->get_record('portfoliogroup_entries', ['id' => $entryid, 'userid' => $USER->id], '*', MUST_EXIST);
}

$context = context_module::instance($id);

$url = new moodle_url('/mod/portfoliogroup/submit.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_title(format_string($portfoliogroup->name));
$PAGE->set_heading(format_string($portfoliogroup->name));
$PAGE->set_context($context);

$formdata = [
    'courseid' => $course->id,
    'groupid' => $usergroup->id,
    'portfolioid' => $portfoliogroup->id
];

if ($entryid) {
    $formdata['entryid'] = $entryid;
}

$form = new \mod_portfoliogroup\form\submit($url, $formdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/portfoliogroup/view.php', ['id' => $id]));
} else if ($formdata = $form->get_data()) {
    try {
        unset($formdata->submitbutton);

        if (isset($formdata->entryid)) {
            $entry = $DB->get_record('portfoliogroup_entries', ['id' => $formdata->entryid, 'userid' => $USER->id], '*', MUST_EXIST);

            $entry->title = $formdata->title;
            $entry->timemodified = time();

            $entry->content = null;
            $entry->contentformat = null;
            if (isset($formdata->content['text'])) {
                $entry->content = $formdata->content['text'];
                $entry->contentformat = $formdata->content['format'];
            }

            $DB->update_record('portfoliogroup_entries', $entry);

            // Process event.
            $params = array(
                'context' => $context,
                'objectid' => $entry->id,
                'relateduserid' => $entry->userid
            );
            $event = \mod_portfoliogroup\event\entry_updated::create($params);
            $event->add_record_snapshot('portfoliogroup_entries', $entry);
            $event->trigger();

            $redirectstring = get_string('entry:update_success', 'mod_portfoliogroup');
        } else {
            $entry = new \stdClass();
            $entry->courseid = $course->id;
            $entry->portfolioid = $portfoliogroup->id;
            $entry->groupid = $formdata->groupid;
            $entry->userid = $USER->id;
            $entry->title = $formdata->title;
            $entry->content = null;
            $entry->contentformat = null;
            $entry->timecreated = time();
            $entry->timemodified = time();

            if (isset($formdata->content['text'])) {
                $entry->content = $formdata->content['text'];
                $entry->contentformat = $formdata->content['format'];
            }

            $entryid = $DB->insert_record('portfoliogroup_entries', $entry);
            $entry->id = $entryid;

            // Processe event.
            $params = array(
                'context' => $context,
                'objectid' => $entryid,
                'groupid' => $entry->groupid,
                'relateduserid' => $entry->userid
            );
            $event = \mod_portfoliogroup\event\entry_added::create($params);
            $event->add_record_snapshot('portfoliogroup_entries', $entry);
            $event->trigger();

            // Completion progress
            $completion = new completion_info($course);
            $completion->update_state($cm, COMPLETION_COMPLETE);

            $completiondata = $completion->get_data($cm, false, $USER->id);

            // If the user completed the activity and it is a group activity, mark all group members as complete.
            if ($completiondata->completionstate == 1) {
                // TODO: Check completion state.
            }

            $redirectstring = get_string('entry:add_success', 'mod_portfoliogroup');
        }

        // Process attachments.
        $draftitemid = file_get_submitted_draft_itemid('attachments');

        file_save_draft_area_files($draftitemid, $context->id, 'mod_portfoliogroup', 'attachments', $entry->id, ['subdirs' => 0, 'maxfiles' => 10]);

        $url = new moodle_url('/mod/portfoliogroup/view.php', ['id' => $cm->id]);

        redirect($url, $redirectstring, null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (\Exception $e) {
        redirect($url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    echo $OUTPUT->header();

    $form->display();

    echo $OUTPUT->footer();
}
