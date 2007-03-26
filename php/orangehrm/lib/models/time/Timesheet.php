<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 hSenid Software International Pvt. Ltd, http://www.hsenid.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */

require_once ROOT_PATH . '/lib/dao/DMLFunctions.php';
require_once ROOT_PATH . '/lib/dao/SQLQBuilder.php';


class Timesheet {

	/**
	 * Class constants
	 */
	const TIMESHEET_DB_TABLE_TIMESHEET = "hs_hr_timesheet";

	const TIMESHEET_DB_FIELD_TIMESHEET_ID = "timesheet_id";
	const TIMESHEET_DB_FIELD_EMPLOYEE_ID = "employee_id";
	const TIMESHEET_DB_FIELD_TIMESHEET_PERIOD_ID = "timesheet_period_id";
	const TIMESHEET_DB_FIELD_START_DATE = "start_date";
	const TIMESHEET_DB_FIELD_END_DATE = "end_date";
	const TIMESHEET_DB_FIELD_STATUS = "status";

	const TIMESHEET_STATUS_NOT_SUBMITTED=0;
	const TIMESHEET_STATUS_SUBMITTED=10;
	const TIMESHEET_STATUS_APPROVED=20;
	const TIMESHEET_STATUS_REJECTED=30;

	/**
	 * Class atributes
	 */
	private $timesheetId;
	private $employeeId;
	private $timesheetPeriodId;
	private $startDate;
	private $endDate;
	private $status;

	public function setTimesheetId($timesheetId) {
		$this->timesheetId=$timesheetId;
	}

	public function getTimesheetId() {
		return $this->timesheetId;
	}

	public function setEmployeeId($employeeId) {
		$this->employeeId=$employeeId;
	}

	public function getEmployeeId() {
		return $this->employeeId;
	}

	public function setTimesheetPeriodId($timesheetPeriodId) {
		$this->timesheetPeriodId=$timesheetPeriodId;
	}

	public function getTimesheetPeriodId() {
		return $this->timesheetPeriodId;
	}

	public function setStartDate($startDate) {
		$this->startDate=$startDate;
	}

	public function getStartDate() {
		return $this->startDate;
	}

	public function setEndDate($endDate) {
		$this->endDate=$endDate;
	}

	public function getEndDate() {
		return $this->endDate;
	}

	public function setStatus($status) {
		$this->status=$status;
	}

	public function getStatus() {
		return $this->status;
	}

	public function __construct() {
		//nothing to do
	}

	public function __distruct() {
		//nothing to do
	}

	/**
	 * Compute the new Timesheet id
	 */
	private function _getNewTimesheetId() {
		$sql_builder = new SQLQBuilder();

		$selectTable = self::TIMESHEET_DB_TABLE_TIMESHEET;
		$selectFields[0] = self::TIMESHEET_DB_FIELD_TIMESHEET_ID;
		$selectOrder = "DESC";
		$selectLimit = 1;
		$sortingField = self::TIMESHEET_DB_FIELD_TIMESHEET_ID;

		$query = $sql_builder->simpleSelect($selectTable, $selectFields, null, $sortingField, $selectOrder, $selectLimit);

		$dbConnection = new DMLFunctions();

		$result = $dbConnection -> executeQuery($query);

		$row = mysql_fetch_row($result);

		$this->setTimesheetId($row[0]+1);
	}

	/**
	 * Add a new timesheet
	 *
	 * Status will be overwritten
	 */
	public function addTimesheet() {
		$this->_getNewTimesheetId();
		$this->setStatus(self::TIMESHEET_STATUS_NOT_SUBMITTED);

		$sql_builder = new SQLQBuilder();

		$insertTable = self::TIMESHEET_DB_TABLE_TIMESHEET;

		$insertFields[0] = "`".self::TIMESHEET_DB_FIELD_TIMESHEET_ID."`";
		$insertFields[1] = "`".self::TIMESHEET_DB_FIELD_EMPLOYEE_ID."`";
		$insertFields[2] = "`".self::TIMESHEET_DB_FIELD_TIMESHEET_PERIOD_ID."`";
		$insertFields[3] = "`".self::TIMESHEET_DB_FIELD_START_DATE."`";
		$insertFields[4] = "`".self::TIMESHEET_DB_FIELD_END_DATE."`";
		$insertFields[5] = "`".self::TIMESHEET_DB_FIELD_STATUS."`";

		$insertValues[0] = $this->getTimesheetId();
		$insertValues[1] = $this->getEmployeeId();
		$insertValues[2] = $this->getTimesheetPeriodId();
		$insertValues[3] = "'".$this->getStartDate()."'";
		$insertValues[4] = "'".$this->getEndDate()."'";
		$insertValues[5] = $this->getStatus();

		$query = $sql_builder->simpleInsert($insertTable, $insertValues, $insertFields);

		$dbConnection = new DMLFunctions();

		$result = $dbConnection->executeQuery($query);

		if ($result && (mysql_affected_rows() > 0)) {
			return true;
		}
		return false;
	}

	/**
	 * Submit timesheet
	 */
	public function submitTimesheet($id) {
		$this->setTimesheetId($id);

		$timeSheet = $this->fetchTimesheets();

		if (!$timeSheet[0] || ($timeSheet[0]->getStatus() != self::TIMESHEET_STATUS_NOT_SUBMITTED)) {
			return false;
		}

		$timeSheet[0]->setStatus(self::TIMESHEET_STATUS_SUBMITTED);

		return $timeSheet[0]->_changeTimesheetStatus();
	}

	/**
	 * Approve timesheet
	 */
	public function approveTimesheet($id) {
		$this->setTimesheetId($id);

		$timeSheet = $this->fetchTimesheets();

		if ($timeSheet[0]->getStatus() != self::TIMESHEET_STATUS_SUBMITTED) {
			return false;
		}

		$timeSheet[0]->setStatus(self::TIMESHEET_STATUS_APPROVED);

		return $timeSheet[0]->_changeTimesheetStatus();
	}

	/**
	 * Cancel timesheet
	 */
	public function cancelTimesheet($id) {
		$this->setTimesheetId($id);

		$timeSheet = $this->fetchTimesheets();

		if ($timeSheet[0]->getStatus() != self::TIMESHEET_STATUS_SUBMITTED) {
			return false;
		}

		$timeSheet[0]->setStatus(self::TIMESHEET_STATUS_NOT_SUBMITTED);

		return $timeSheet[0]->_changeTimesheetStatus();
	}

	/**
	 * Reject timesheet
	 */
	public function rejectTimesheet($id) {
		$this->setTimesheetId($id);

		$timeSheet = $this->fetchTimesheets();

		if ($timeSheet[0]->getStatus() != self::TIMESHEET_STATUS_SUBMITTED) {
			return false;
		}

		$timeSheet[0]->setStatus(self::TIMESHEET_STATUS_REJECTED);

		return $timeSheet[0]->_changeTimesheetStatus();
	}

	private function _changeTimesheetStatus() {
		$sql_builder = new SQLQBuilder();

		$updateTable = self::TIMESHEET_DB_TABLE_TIMESHEET;

		$updateFields[0] = "`".self::TIMESHEET_DB_FIELD_STATUS."`";

		$updateValues[1] = $this->getStatus();

		$updateConditions[] = "`".self::TIMESHEET_DB_FIELD_TIMESHEET_ID."` = {$this->getTimesheetId()}";

		$query = $sql_builder->simpleUpdate($updateTable, $updateFields, $updateValues, $updateConditions);

		$dbConnection = new DMLFunctions();

		$result = $dbConnection -> executeQuery($query);

		if ($result) {
			return true;
		}

		return false;
	}

	/**
	 * Fetch timesheets
	 */
	public function fetchTimesheets() {
		$sql_builder = new SQLQBuilder();

		$selectTable = self::TIMESHEET_DB_TABLE_TIMESHEET." a ";

		$selectFields[0] = "a.`".self::TIMESHEET_DB_FIELD_TIMESHEET_ID."`";
		$selectFields[1] = "a.`".self::TIMESHEET_DB_FIELD_EMPLOYEE_ID."`";
		$selectFields[2] = "a.`".self::TIMESHEET_DB_FIELD_TIMESHEET_PERIOD_ID."`";
		$selectFields[3] = "a.`".self::TIMESHEET_DB_FIELD_START_DATE."`";
		$selectFields[4] = "a.`".self::TIMESHEET_DB_FIELD_END_DATE."`";
		$selectFields[5] = "a.`".self::TIMESHEET_DB_FIELD_STATUS."`";

		if ($this->getTimesheetId() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_TIMESHEET_ID."` = {$this->getTimesheetId()}";
		}
		if ($this->getEmployeeId() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_EMPLOYEE_ID."` = {$this->getEmployeeId()}";
		}
		if ($this->getTimesheetPeriodId() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_TIMESHEET_PERIOD_ID."` = {$this->getTimesheetPeriodId()}";
		}
		if ($this->getStartDate() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_START_DATE."` = '{$this->getStartDate()}'";
		}
		if ($this->getEndDate() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_END_DATE."` = '{$this->getEndDate()}'";
		}
		if ($this->getStatus() != null) {
			$selectConditions[] = "a.`".self::TIMESHEET_DB_FIELD_STATUS."` = '{$this->getStatus()}'";
		}

		$query = $sql_builder->simpleSelect($selectTable, $selectFields, $selectConditions);

		$dbConnection = new DMLFunctions();

		$result = $dbConnection -> executeQuery($query);

		$objArr = $this->_buildObjArr($result);

		return $objArr;
	}

	private function _buildObjArr($result) {
		$objArr = null;

		while ($row = mysql_fetch_assoc($result)) {
			$tmpTimeArr = new Timesheet();

			$tmpTimeArr->setTimesheetId($row[self::TIMESHEET_DB_FIELD_TIMESHEET_ID]);
			$tmpTimeArr->setEmployeeId($row[self::TIMESHEET_DB_FIELD_EMPLOYEE_ID]);
			$tmpTimeArr->setTimesheetPeriodId($row[self::TIMESHEET_DB_FIELD_TIMESHEET_PERIOD_ID]);
			$tmpTimeArr->setStartDate(date('Y-m-d', strtotime($row[self::TIMESHEET_DB_FIELD_START_DATE])));
			$tmpTimeArr->setEndDate(date('Y-m-d', strtotime($row[self::TIMESHEET_DB_FIELD_END_DATE])));
			$tmpTimeArr->setStatus($row[self::TIMESHEET_DB_FIELD_STATUS]);

			$objArr[] = $tmpTimeArr;
		}

		return $objArr;
	}
}
?>
