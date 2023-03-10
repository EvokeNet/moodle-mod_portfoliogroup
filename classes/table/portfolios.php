<?php

namespace mod_portfoliogroup\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use mod_portfoliogroup\util\entry;
use mod_portfoliogroup\util\grade;
use mod_portfoliogroup\util\group;
use table_sql;
use moodle_url;
use html_writer;

/**
 * Entries table class
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class portfolios extends table_sql {

    protected $context;
    protected $portfoliogroup;

    public function __construct($uniqueid, $context, $portfoliogroup) {
        parent::__construct($uniqueid);

        $this->context = $context;

        $this->portfoliogroup = $portfoliogroup;

        $this->define_columns(['id', 'name', 'members', 'status']);

        $this->define_headers(['ID', get_string('group'),  get_string('groupmembers', 'mod_portfoliogroup'), get_string('status', 'mod_portfoliogroup')]);

        $this->no_sorting('members');

        $this->no_sorting('status');

        $this->define_baseurl(new moodle_url('/mod/portfoliogroup/indextable.php', ['id' => $this->portfoliogroup->course]));

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-portfolios');
    }

    public function base_sql() {
        $fields = 'id, courseid, name';

        $from = '{groups}';

        $where = 'courseid = :courseid';

        $params = ['courseid' => $this->portfoliogroup->course];

        $this->set_sql($fields, $from, $where, $params);
    }

    public function col_members($data) {
        $grouputil = new group();

        $members = $grouputil->get_group_members($data->id, true, $this->context);

        if (!$members) {
            return '';
        }

        $output = '';
        foreach ($members as $member) {
            $output .= '<img class="w-48 userpicture" src="'.$member->userpicture.'" alt="'.$member->fullname.'" title="'.$member->fullname.'" data-toggle="tooltip">';
        }

        return $output;
    }

    public function col_status($data) {
        $gradeutil = new grade();
        $entryutil = new entry();

        $url = new moodle_url('/mod/portfoliogroup/portfolio.php', ['id' => $this->portfoliogroup->course, 'g' => $data->id]);

        $statuscontent = html_writer::link($url, get_string('viewportfolio', 'mod_portfoliogroup'), ['class' => 'btn btn-primary btn-sm']);

        if ($entryutil->get_total_course_entries($this->portfoliogroup->course, $data->id)) {
            $statuscontent .= html_writer::span(get_string('submitted', 'mod_portfoliogroup'), 'badge badge-info ml-2 p-2');
        }

        if ($gradeutil->get_group_grade($this->portfoliogroup, $data->id)) {
            $statuscontent .= html_writer::span(get_string('evaluated', 'mod_portfoliogroup'), 'badge badge-success ml-2 p-2');
        }

        return $statuscontent;
    }
}
