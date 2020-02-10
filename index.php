<?php

// This module for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This module for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Send out push communications to the mobile app.
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('tool_pushcommunications_main', '', null, '', []);
// admin_externalpage_setup does access validation checks for us

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/pushcommunications/index.php'));
$PAGE->set_title(get_string('pageheader', 'tool_pushcommunications'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('tool_pushcommunications');

echo $output->header();
echo $output->heading($pagetitle);

// handle the submission of the push communication send form
$form = new \tool_pushcommunications\local\composepushcommunication_form(null, null, 'post');
if ($data = $form->get_data()) {
	require_once(__DIR__ . '/classes/local/pushcommunication_sender.php');
	$sender = new \tool_pushcommunications\local\pushcommunication_sender();
	$sender->test_send_message($data);
}

// create renderable
$renderable = new \tool_pushcommunications\output\index_page();

echo $output->render($renderable);
echo $output->footer();
