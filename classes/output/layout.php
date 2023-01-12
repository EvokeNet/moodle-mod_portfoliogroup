<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Layout renderable class.
 *
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class layout implements renderable, templatable {

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
        $groupsutil = new \mod_portfoliogroup\util\group();
        $usergroup = $groupsutil->get_user_group($this->portfoliogroup->course);

        if (!$usergroup) {
            throw new \Exception('You are not a member of a group!');
        }

        $layoututil = new \mod_portfoliogroup\util\layout();
        $layout = $layoututil->get_group_layout($this->portfoliogroup->course, $usergroup->id);

        $data = [
            'id' => $this->portfoliogroup->id,
            'name' => $this->portfoliogroup->name,
            'cmid' => $this->context->instanceid,
            'courseid' => $this->portfoliogroup->course,
            'groupid' => $usergroup->id,
            'contextid' => $this->context->id,
            'sesskey' => sesskey(),
            'istimeline' => $layout === 'timeline',
            'ismansory' => $layout === 'mansory',
            'isblog' => $layout === 'blog',
        ];

        return $data;
    }
}
