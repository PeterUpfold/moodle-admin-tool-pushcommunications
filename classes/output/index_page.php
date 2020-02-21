<?php
/*
Parent Progress View, a module for Moodle to allow the viewing of documents and pupil data by authorised parents.
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
 * Output renderable (handler to set up data) for index page mustache template.
 *
 * @package tool_pushcommunications
 * @author Test Valley School
 */

namespace tool_pushcommunications\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Output renderable (handler to set up data) for index page mustache template.
 *
 * @package tool_pushcommunications
 * @author Test Valley School
 */
class index_page implements renderable, templatable {

	/**
	 * @var string The result of any push communication action.
	 */
	protected $result = "";

	/**
	 * The class constructor should receive any information that needs to be passed to the template at rendertime.
	 */
	public function __construct($result) {
		$this->result = $result;
	}

	/**
	 * Export the data for use in the Mustache template.
	 */
	public function export_for_template(renderer_base $output) {
		global $CFG;
		$data = new stdClass();

		require_once("$CFG->dirroot/admin/user/lib.php");
		require_once("$CFG->dirroot/cohort/lib.php");


		// render the form and pass to Mustache
		require_once( dirname(__FILE__) . '/../local/composepushcommunication_form.php');
		$form_populate_data = \get_selection_data(new \user_filtering());
		$form_populate_data['cohorts'] = \cohort_get_all_cohorts(0, 100, '');
		$form = new \tool_pushcommunications\local\composepushcommunication_form(null, $form_populate_data, 'post');
		$data->composepushcommunication_form = $form->render();
		$data->result = $this->result;

		return $data;
	}

};
