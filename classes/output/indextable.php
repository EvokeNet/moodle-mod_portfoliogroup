<?php

namespace mod_portfoliogroup\output;

defined('MOODLE_INTERNAL') || die();

use mod_portfoliogroup\table\portfolios;
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
class indextable implements renderable, templatable {

    public $context;
    public $portfoliogroup;

    public function __construct($context, $portfoliogroup) {
        $this->context = $context;
        $this->portfoliogroup = $portfoliogroup;
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
        $table = new portfolios(
            'mod-evokeportfolio-portfolios-table',
            $this->context,
            $this->portfoliogroup
        );

        $table->collapsible(false);

        ob_start();
        $table->out(30, true);
        $participantstable = ob_get_contents();
        ob_end_clean();

        $data = [
            'portfolios' => $participantstable
        ];

        return $data;
    }
}
