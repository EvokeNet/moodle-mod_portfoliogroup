<?php

/**
 * Portfolio builder services definition
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_portfoliogroup_enrolledusers' => [
        'classname' => 'mod_portfoliogroup\external\course',
        'classpath' => 'mod/portfoliogroup/classes/external/course.php',
        'methodname' => 'enrolledusers',
        'description' => 'Get the list of enrolled users in a course',
        'type' => 'read',
        'ajax' => true
    ],
    'mod_portfoliogroup_createentry' => [
        'classname' => 'mod_portfoliogroup\external\entry',
        'classpath' => 'mod/portfoliogroup/classes/external/entry.php',
        'methodname' => 'create',
        'description' => 'Creates a new skill',
        'type' => 'write',
        'ajax' => true
    ],
    'mod_portfoliogroup_togglereaction' => [
        'classname' => 'mod_portfoliogroup\external\reaction',
        'classpath' => 'mod/portfoliogroup/classes/external/reaction.php',
        'methodname' => 'toggle',
        'description' => 'Toggle a user reaction',
        'type' => 'write',
        'ajax' => true
    ],
    'mod_portfoliogroup_addcomment' => [
        'classname' => 'mod_portfoliogroup\external\comment',
        'classpath' => 'mod/portfoliogroup/classes/external/comment.php',
        'methodname' => 'add',
        'description' => 'Add a new comment',
        'type' => 'write',
        'ajax' => true
    ],
    'mod_portfoliogroup_editcomment' => [
        'classname' => 'mod_portfoliogroup\external\comment',
        'classpath' => 'mod/portfoliogroup/classes/external/comment.php',
        'methodname' => 'edit',
        'description' => 'Edit a new comment',
        'type' => 'write',
        'ajax' => true
    ],
    'mod_portfoliogroup_loadportfolios' => [
        'classname' => 'mod_portfoliogroup\external\portfolio',
        'classpath' => 'mod/portfoliogroup/classes/external/portfolio.php',
        'methodname' => 'load',
        'description' => 'Load users portfolios',
        'type' => 'read',
        'ajax' => true
    ],
    'mod_portfoliogroup_gradeportfolio' => [
        'classname' => 'mod_portfoliogroup\external\grade',
        'classpath' => 'mod/portfoliogroup/classes/external/grade.php',
        'methodname' => 'grade',
        'description' => 'Grade a user portfolio',
        'type' => 'write',
        'ajax' => true
    ],
];
