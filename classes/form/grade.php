<?php

namespace mod_portfoliogroup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/formslib.php');

use mod_portfoliogroup\util\grade as gradeutil;
use mod_portfoliogroup\util\portfolio;

/**
 * Grade form.
 *
 * @package     mod_portfoliogroup
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class grade extends \moodleform {

    private $groupid = null;

    public function __construct($formdata, $customdata = null) {
        parent::__construct(null, $customdata, 'post',  '', ['class' => 'portfoliogroup-grade-form'], true, $formdata);

        $this->set_display_vertical();
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $this->groupid = $this->_customdata['groupid'];

        $mform->addElement('hidden', 'groupid', $this->_customdata['groupid']);
        $mform->setType('groupid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $gradeutil = new gradeutil();
        $portfolio = $gradeutil->get_portfolio_with_evaluation($this->_customdata['courseid']);

        if ($portfolio) {
            $this->fill_form_with_grade_fields($mform, $portfolio);
        }

        $this->add_action_buttons(true);
    }

    private function fill_form_with_grade_fields($mform, $portfolio) {
        $groupgrade = $this->get_group_grade($portfolio, $this->groupid);

        if ($portfolio->grade > 0) {
            $mform->addElement('text', 'grade', get_string('grade', 'mod_portfoliogroup'));
            $mform->addHelpButton('grade', 'grade', 'mod_portfoliogroup');
            $mform->addRule('grade', get_string('onlynumbers', 'mod_portfoliogroup'), 'numeric', null, 'client');
            $mform->addRule('grade', get_string('required'), 'required', null, 'client');
            $mform->setType('grade', PARAM_RAW);

            if ($groupgrade) {
                $mform->setDefault('grade', $groupgrade);
            }
        }

        if ($portfolio->grade < 0) {
            $grademenu = array(-1 => get_string("nograde")) + make_grades_menu($portfolio->grade);

            $mform->addElement('select', 'grade', get_string('gradenoun') . ':', $grademenu);
            $mform->setType('grade', PARAM_INT);
            $mform->addRule('grade', get_string('required'), 'required', null, 'client');

            if ($groupgrade) {
                $mform->setDefault('grade', $groupgrade);
            }
        }
    }

    private function get_group_grade($portfolio, $groupid) {
        $gradeutil = new gradeutil();
        $groupgrade = $gradeutil->get_group_grade($portfolio, $groupid);

        return $this->process_grade($portfolio->grade, $groupgrade);
    }

    private function process_grade($portfoliograde, $grade = null) {
        // Grade in decimals.
        if ($grade && $portfoliograde > 0) {
            return number_format($grade, 1, '.', '');
        }

        // Grade in scale.
        if ($grade && $portfoliograde < 0) {
            return (int) $grade;
        }

        return false;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['grade'])) {
            $errors['grade'] = get_string('required');
        }

        return $errors;
    }
}
