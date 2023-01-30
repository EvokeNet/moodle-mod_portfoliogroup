<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use mod_portfoliogroup\util\group;
use renderable;
use templatable;
use renderer_base;

/**
 * Index renderable class.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class index implements renderable, templatable {

    public $context;
    public $course;

    public function __construct($context, $course) {
        $this->context = $context;
        $this->course = $course;
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
        $portfolioutil = new \mod_portfoliogroup\util\portfolio($this->context, $this->course->id);

        $portfolios = $portfolioutil->get_course_portfolios();

        return [
            'courseid' => $this->course->id,
            'portfolios' => $portfolios,
            'cangrade' => has_capability('mod/portfoliogroup:grade', $this->context),
        ];
    }
}
