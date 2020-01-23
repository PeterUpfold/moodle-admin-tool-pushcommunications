<?php
/*
Push Communications Tool for Moodle
    Copyright (C) 2016-20 Test Valley School.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License,
    or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * A class for a Moodle QuickForm for sending a push communication
 *
 * @package tool_pushcommunications
 * @copyright 2019-2020 Test Valley School {@link https://www.testvalley.hants.sch.uk/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_pushcommunications\local;

require_once("$CFG->libdir/formslib.php");

use moodleform;


class composepushcommunication_form extends moodleform {

	/**
	 * Definition of the form.
	 */
	public function definition() {
		global $CFG;

		$mform = $this->_form;
		$mform->addElement('textarea', 'communication_content', get_string('communication_content', 'tool_pushcommunications'), '');
		$mform->setType('communication_content', PARAM_NOTAGS);
		$mform->addRule(
			'communication_content',
			get_string('communication_content_maxlength', 'tool_pushcommunications'),
			'maxlength',
			2048,
			'client', /* also validated on server side */
			false,
			false
		);

		$this->add_action_buttons(/* $cancel */ false, get_string('send', 'tool_pushcommunications'));
	}

	/**
	 * Return a result of validation.
	 */
	public function validation($data, $files) {
		//TODO how do we do this??
		return array();
	}

};
