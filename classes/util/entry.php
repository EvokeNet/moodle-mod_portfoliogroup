<?php

namespace mod_portfoliogroup\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Entry utility class helper
 *
 * @package     mod_portfoliogroup
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class entry {
    private $portfoliocontexts = [];

    public function get_entry_context($portfolioid) {
        if (isset($this->portfoliocontexts[$portfolioid])) {
            return $this->portfoliocontexts[$portfolioid];
        }

        $coursemodule = get_coursemodule_from_instance('portfoliogroup', $portfolioid);

        $this->portfoliocontexts[$portfolioid] = \context_module::instance($coursemodule->id);

        return $this->portfoliocontexts[$portfolioid];
    }

    public function group_has_entry_in_portfolio_instance($portfolioid, $groupid) {
        global $DB;

        $count = $DB->count_records('portfoliogroup_entries', ['portfolioid' => $portfolioid, 'groupid' => $groupid]);

        if (!$count) {
            return false;
        }

        return true;
    }

    public function get_group_course_entries($courseid, $groupid) {
        global $DB, $USER;

        $sql = 'SELECT e.*, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.firstname, u.lastname
                FROM {portfoliogroup_entries} e
                INNER JOIN {user} u ON u.id = e.userid
                WHERE courseid = :courseid AND groupid = :groupid';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid, 'groupid' => $groupid]);

        if (!$records) {
            return false;
        }

        $data = [];
        $i = 1;
        foreach ($records as $record) {
            $context = $this->get_entry_context($record->portfolioid);

            $attachments = $this->get_attachments($record->id, $context);

            $images = $this->get_images($attachments);
            $files = $this->get_files($attachments);

            $entry = [
                'id' => $record->id,
                'title' => $record->title,
                'content' => format_text($record->content, $record->contentformat),
                'timecreated' => userdate($record->timecreated),
                'hasimages' => !empty($images),
                'images' => $images,
                'singleimage' => !empty($images) && count($images) === 1,
                'hasfiles' => !empty($files),
                'files' => $files,
                'position' => ($i % 2 == 0) ? 'right' : 'left',
                'postedby' => fullname($record),
                'itsmine' => $USER->id === $record->userid,
                'cmid' => $context->instanceid
            ];

            $data[] = array_merge($entry, $this->get_entry_reactions($record->id), $this->get_entry_comments($record->id));

            $i++;
        }

        return $data;
    }

    public function get_images($files = null) {
        if (!$files) {
            return false;
        }

        $files = array_filter($files, function($file) {
            return $file['isimage'] === true;
        });

        $files = array_values($files);

        $files[0]['active'] = true;

        return $files;
    }

    public function get_files($files = null) {
        if (!$files) {
            return false;
        }

        $files = array_filter($files, function($file) {
            return $file['isimage'] === false;
        });

        return array_values($files);
    }

    public function get_attachments($entryid, $context) {
        $files = $this->get_entry_attachments($entryid, $context->id);

        if (!$files) {
            return false;
        }

        $entryfiles = [];
        foreach ($files as $file) {
            $path = [
                '',
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $entryid . $file->get_filepath() . $file->get_filename()
            ];

            $fileurl = \moodle_url::make_file_url('/pluginfile.php', implode('/', $path), true);

            if (!$file->is_valid_image() || $file->get_filepath() == '/thumb/') {
                $entryfiles[] = [
                    'filename' => $file->get_filename(),
                    'isimage' => $file->is_valid_image(),
                    'fileurl' => $fileurl->out()
                ];
            }
        }

        return $entryfiles;
    }

    public function get_entry_reactions($entryid) {
        $reactionutil = new reaction();

        $totalreactions = $reactionutil->get_total_reactions($entryid, reaction::LIKE);
        $userreacted = $reactionutil->user_reacted($entryid, reaction::LIKE);

        return [
            'totalreactions' => $totalreactions,
            'userreacted' => $userreacted
        ];
    }

    public function get_entry_comments($entryid) {
        global $DB, $USER;


        $sql = 'SELECT c.id as commentid, c.text, c.timecreated as ctimecreated, c.timemodified as ctimemodified, u.id as userid, u.*
            FROM {portfoliogroup_comments} c
            INNER JOIN {user} u ON u.id = c.userid
            WHERE c.entryid = :entryid';

        $comments = $DB->get_records_sql($sql, ['entryid' => $entryid]);

        if (!$comments) {
            return [
                'comments' => false,
                'totalcomments' => 0
            ];
        }

        $userutil = new user();

        $commentsdata = [];
        foreach ($comments as $comment) {
            $userpicture = $userutil->get_user_image_or_avatar($comment);

            $commentsdata[] = [
                'commentid' => $comment->commentid,
                'text' => $comment->text,
                'commentuserpicture' => $userpicture,
                'commentuserfullname' => fullname($comment),
                'isowner' => $USER->id == $comment->userid,
                'edited' => $comment->ctimecreated != $comment->ctimemodified,
                'humantimecreated' => userdate($comment->ctimecreated)
            ];
        }

        return [
            'comments' => $commentsdata,
            'totalcomments' => count($commentsdata)
        ];
    }

    public function get_total_course_entries($courseid, $groupid) {
        global $DB;

        $sql = 'SELECT count(*)
                FROM {portfoliogroup_entries}
                WHERE courseid = :courseid AND groupid = :groupid';

        return $DB->count_records_sql($sql, ['groupid' => $groupid, 'courseid' => $courseid]);
    }

    public function get_last_course_entry($courseid, $groupid) {
        global $DB;

        $sql = 'SELECT id, title, timecreated
                FROM {portfoliogroup_entries}
                WHERE courseid = :courseid AND groupid = :groupid
                ORDER BY id DESC LIMIT 1';

        $record = $DB->get_record_sql($sql, ['courseid' => $courseid, 'groupid' => $groupid]);

        if (!$record) {
            return false;
        }

        $record->humantimecreated = userdate($record->timecreated);

        return $record;
    }

    public function delete_entry($entryid) {
        global $DB, $USER;

        $entry = $DB->get_record('portfoliogroup_entries', ['id' => $entryid, 'userid' => $USER->id], '*', MUST_EXIST);

        $DB->delete_records('portfoliogroup_comments', ['entryid' => $entry->id]);

        $DB->delete_records('portfoliogroup_reactions', ['entryid' => $entry->id]);

        $DB->delete_records('portfoliogroup_entries', ['id' => $entry->id]);

        $coursemodule = get_coursemodule_from_instance('portfoliogroup', $entry->portfolioid, $entry->courseid);

        $context = \context_module::instance($coursemodule->id);

        if ($files = $this->get_entry_attachments($entryid, $context->id)) {
            foreach ($files as $file) {
                $file->delete();
            }
        }
    }

    public function get_entry_attachments($entryid, $contextid, $filearea = 'attachments') {
        $fs = get_file_storage();

        $files = $fs->get_area_files($contextid,
            'mod_portfoliogroup',
            $filearea,
            $entryid,
            'timemodified',
            false);

        if (!$files) {
            return false;
        }

        return $files;
    }

    public function create_entry_thumbs($entry) {
        $context = $this->get_entry_context($entry->portfolioid);

        if (!$files = $this->get_entry_attachments($entry->id, $context->id)) {
            return;
        }

        $fs = get_file_storage();

        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                $filerecord = [
                    'userid' => $entry->userid,
                    'filename' => $file->get_filename(),
                    'contextid' => $file->get_contextid(),
                    'component' => $file->get_component(),
                    'filearea' => $file->get_filearea(),
                    'itemid' => $entry->id,
                    'filepath' => '/thumb/'
                ];

                $fileinfo = $file->get_imageinfo();

                $width = $fileinfo['width'];
                if ($width > 600) {
                    $width = 600;
                }

                $fs->convert_image($filerecord, $file, $width);
            }
        }
    }
}
