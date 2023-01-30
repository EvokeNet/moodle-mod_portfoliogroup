<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use mod_portfoliogroup\util\grade;
use mod_portfoliogroup\util\group;
use renderable;
use templatable;
use renderer_base;
use mod_portfoliogroup\util\user;
use mod_portfoliogroup\util\entry;

/**
 * Portfolio renderable class.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class portfolio implements renderable, templatable {
    protected $context;
    protected $course;
    protected $group;

    public function __construct($context, $course, $group) {
        $this->context = $context;
        $this->course = $course;
        $this->group = $group;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $isloggedin = isloggedin();

        $gradeutil = new grade();
        $groupsutil = new group();

        $groupsmembers = $groupsutil->get_groups_members([$this->group], true, $this->context);

        $data = [
            'groupid' => $this->group->id,
            'groupname' => $this->group->name,
            'groupsmembers' => $groupsmembers,
            'courseid' => $this->course->id,
            'isloggedin' => $isloggedin,
            'cangrade' => has_capability('mod/portfoliogroup:grade', $this->context),
            'contextid' => $this->context->id,
            'grade' => $gradeutil->get_group_course_grade($this->course->id, $this->group->id)
        ];

        $userutil = new user();
        $userdata = [
            'id' => $USER->id,
            'fullname' => fullname($USER),
            'picture' => $userutil->get_user_image_or_avatar($USER)
        ];

        $entryutil = new entry();
        $entries = $entryutil->get_group_course_entries($this->course->id, $this->group->id);

        $data['hasentries'] = !empty($entries);

        $layoututil = new \mod_portfoliogroup\util\layout();
        $layout = $layoututil->get_group_layout($this->course->id, $this->group->id, 'timeline');

        $data['entries'] = $output->render_from_template("mod_portfoliogroup/layouts/{$layout}/entries",
            ['entries' => $entries, 'user' => $userdata, 'courseid' => $this->course->id, 'isloggedin' => $isloggedin]);

        return $data;
    }
}
