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
 * Mobile output for the timetable block.
 *
 * @package   block_timetableblock
 * @copyright 2026 Sameh Naim <sameh@timetable.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_timetableblock\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Mobile content callbacks for the timetable block.
 */
class mobile {
    /**
     * Render the mobile timetable page.
     *
     * @param array $args
     * @return array
     */
    public static function view_timetable(array $args): array {
        global $OUTPUT, $USER;

        require_login();

        $context = \context_system::instance();
        require_capability('local/timetable:view', $context);

        $teacher = trim((string)($args['teacher'] ?? ''));
        $class = trim((string)($args['class'] ?? ''));
        $day = trim((string)($args['day'] ?? ''));

        $service = new \local_timetable\local\timetable_service();
        $payload = $service->get_user_timetable($USER, false, [
            'teacher' => $teacher,
            'class' => $class,
            'day' => $day,
        ]);

        $payload['mobile_title'] = get_string('mobileviewtitle', 'block_timetableblock');
        $payload['mobile_apply_label'] = get_string('mobileapplyfilters', 'block_timetableblock');
        $payload['mobile_clear_label'] = get_string('mobileclearfilters', 'block_timetableblock');
        $payload['mobile_noentries'] = get_string('mobilenoentries', 'block_timetableblock');
        $payload['selected_teacher'] = $teacher;
        $payload['selected_class'] = $class;
        $payload['selected_day'] = $day;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('block_timetableblock/mobileapp/timetable', $payload),
                ],
            ],
            'otherdata' => [
                'teacher' => $teacher,
                'class' => $class,
                'day' => $day,
            ],
        ];
    }
}
