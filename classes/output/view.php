<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use mod_portfoliogroup\util\grade;
use mod_portfoliogroup\util\user;
use mod_portfoliogroup\util\entry;
use renderable;
use templatable;
use renderer_base;

/**
 * View renderable class.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class view implements renderable, templatable {

    public $portfoliogroup;
    public $context;

    public function __construct($portfoliogroup, $context) {
        $this->portfoliogroup = $portfoliogroup;
        $this->context = $context;
    }

    /**
     * Export the data
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $userutil = new user();
        $userdata = [
            'id' => $USER->id,
            'fullname' => fullname($USER),
            'picture' => $userutil->get_user_image_or_avatar($USER)
        ];

        $groupsutil = new \mod_portfoliogroup\util\group();
        $usergroups = $groupsutil->get_user_groups($this->portfoliogroup->course);

        $usergroup = null;
        $layout = 'timeline';
        $groupsmembers = [];
        $grade = false;
        if ($usergroups) {
            $usergroup = current($usergroups);

            $groupsmembers = $groupsutil->get_groups_members([$usergroup], true, $this->context);

            $layoututil = new \mod_portfoliogroup\util\layout();
            $layout = $layoututil->get_group_layout($this->portfoliogroup->course, $usergroup->id);

            $gradeutil = new grade();
            $grade = $gradeutil->get_group_course_grade($this->portfoliogroup->course, $usergroup->id);
        }

        $publicurl = new \moodle_url('/mod/portfoliogroup/portfolio.php', ['id' => $this->context->instanceid, 'u' => $USER->id]);
        $data = [
            'id' => $this->portfoliogroup->id,
            'name' => $this->portfoliogroup->name,
            'intro' => format_module_intro('portfoliogroup', $this->portfoliogroup, $this->context->instanceid),
            'cmid' => $this->context->instanceid,
            'courseid' => $this->portfoliogroup->course,
            'userid' => $userdata['id'],
            'userfullname' => $userdata['fullname'],
            'userpicture' => $userdata['picture'],
            'contextid' => $this->context->id,
            'cangrade' => has_capability('mod/portfoliogroup:grade', $this->context),
            'grade' => $grade,
            'encodedpublicurl' => htmlentities($publicurl),
            'group' => $usergroup,
            'groupsmembers' => $groupsmembers,
            'hasgroupsmembers' => !empty($groupsmembers),
        ];

        $entries = [];
        if ($usergroup) {
            $entryutil = new entry();
            $entries = $entryutil->get_group_course_entries($this->portfoliogroup->course, $usergroup->id);
        }

        $data['hasentries'] = !empty($entries);

        $data['entries'] = $output->render_from_template("mod_portfoliogroup/layouts/{$layout}/entries",
            ['entries' => $entries, 'user' => $userdata, 'courseid' => $this->portfoliogroup->course, 'isloggedin' => isloggedin()]);

        return $data;
    }
}
