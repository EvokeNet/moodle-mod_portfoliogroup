<?php

namespace mod_portfoliogroup\event;

/**
 * The course_module_viewed event class.
 *
 * @package     mod_portfoliogroup
 * @category    event
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'portfoliogroup';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'portfoliogroup', 'restore' => 'portfoliogroup');
    }
}
