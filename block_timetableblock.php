<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Timetable dashboard block.
 *
 * @package   block_timetableblock
 * @copyright 2026 Sameh Naim <sameh@timetable.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Timetable block backed by local_timetable.
 */
class block_timetableblock extends block_base {
    public function init(): void {
        $this->title = get_string('pluginname', 'block_timetableblock');
    }

    public function applicable_formats(): array {
        return [
            'my' => true,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'user-profile' => false,
        ];
    }

    public function has_config(): bool {
        return false;
    }

    public function get_content(): stdClass {
        global $OUTPUT, $PAGE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $context = context_system::instance();
        if (!has_capability('local/timetable:view', $context)) {
            return $this->content;
        }

        try {
            $userrecord = is_object($USER) ? $USER : core_user::get_user($USER);
            if (!$userrecord || empty($userrecord->id)) {
                return $this->content;
            }

            $service = new \local_timetable\local\timetable_service();
            $payload = $service->get_user_timetable($userrecord, false);
            $payload['powered_by'] = get_string('poweredby', 'local_timetable');
            $payload['powered_by_url'] = get_string('poweredbyurl', 'local_timetable');

            $refreshurl = new moodle_url('/local/timetable/index.php', [
                'refresh' => 1,
                'sesskey' => sesskey(),
            ]);

            foreach ($payload['timetables'] as $index => $timetable) {
                $payload['timetables'][$index]['refresh_url'] = $refreshurl->out(false);
                $payload['timetables'][$index]['refresh_title'] = get_string('refreshshort', 'local_timetable');
                $payload['timetables'][$index]['pdf_url'] = (new moodle_url('/local/timetable/export.php', [
                    'timetableid' => $timetable['id'],
                ]))->out(false);
                $payload['timetables'][$index]['pdf_title'] = get_string('exportpdf_full', 'local_timetable');
            }

            $renderer = $PAGE->get_renderer('local_timetable');
            $this->content->text = $renderer->render_user_timetables($payload);
        } catch (Throwable $e) {
            $this->content->text = \html_writer::div(
                get_string('blockemptyfallback', 'block_timetableblock'),
                'alert alert-info'
            );
        }

        return $this->content;
    }
}
