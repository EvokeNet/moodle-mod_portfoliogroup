<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Layout preview renderable class.
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class layoutpreview implements renderable, templatable {

    public $portfoliogroup;
    public $context;
    public $type;

    public function __construct($portfoliogroup, $context, $type) {
        $this->portfoliogroup = $portfoliogroup;
        $this->context = $context;
        $this->type = $type;
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
        $data = [
            'id' => $this->portfoliogroup->id,
            'name' => $this->portfoliogroup->name,
            'cmid' => $this->context->instanceid,
            'courseid' => $this->portfoliogroup->course,
            'contextid' => $this->context->id,
            'type' => $this->type
        ];

        return $data;
    }
}
