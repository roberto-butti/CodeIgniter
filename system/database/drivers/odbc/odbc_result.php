<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * NOTICE OF LICENSE
 * 
 * Licensed under the Open Software License version 3.0
 * 
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ODBC Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_odbc_result extends CI_DB_result {

	public $num_rows;

	/**
	 * Number of rows in the result set
	 *
	 * @return	int
	 */
	public function num_rows()
	{
		if (is_int($this->num_rows))
		{
			return $this->num_rows;
		}

		// Work-around for ODBC subdrivers that don't support num_rows()
		if (($this->num_rows = @odbc_num_rows($this->result_id)) === -1)
		{
			$this->num_rows = count($this->result_array());
		}

		return $this->num_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * @return	int
	 */
	public function num_fields()
	{
		return @odbc_num_fields($this->result_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Generates an array of column names
	 *
	 * @return	array
	 */
	public function list_fields()
	{
		$field_names = array();
		$num_fields = $this->num_fields();

		if ($num_fields > 0)
		{
			for ($i = 1; $i <= $num_fields; $i++)
			{
				$field_names[] = odbc_field_name($this->result_id, $i);
			}
		}

		return $field_names;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Generates an array of objects containing field meta-data
	 *
	 * @return	array
	 */
	public function field_data()
	{
		$retval = array();
		for ($i = 0, $odbc_index = 1, $c = $this->num_fields(); $i < $c; $i++, $odbc_index++)
		{
			$retval[$i]			= new stdClass();
			$retval[$i]->name		= odbc_field_name($this->result_id, $odbc_index);
			$retval[$i]->type		= odbc_field_type($this->result_id, $odbc_index);
			$retval[$i]->max_length		= odbc_field_len($this->result_id, $odbc_index);
			$retval[$i]->primary_key	= 0;
			$retval[$i]->default		= '';
		}

		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Free the result
	 *
	 * @return	null
	 */
	function free_result()
	{
		if (is_resource($this->result_id))
		{
			odbc_free_result($this->result_id);
			$this->result_id = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Data Seek
	 *
	 * Moves the internal pointer to the desired offset.  We call
	 * this internally before fetching results to make sure the
	 * result set starts at zero
	 *
	 * @access	private
	 * @return	array
	 */
	function _data_seek($n = 0)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Returns the result set as an array
	 *
	 * @access	private
	 * @return	array
	 */
	function _fetch_assoc()
	{
		if (function_exists('odbc_fetch_object'))
		{
			return odbc_fetch_array($this->result_id);
		}
		else
		{
			return $this->_odbc_fetch_array($this->result_id);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Returns the result set as an object
	 *
	 * @access	private
	 * @return	object
	 */
	function _fetch_object()
	{
		if (function_exists('odbc_fetch_object'))
		{
			return odbc_fetch_object($this->result_id);
		}
		else
		{
			return $this->_odbc_fetch_object($this->result_id);
		}
	}


	/**
	 * Result - object
	 *
	 * subsititutes the odbc_fetch_object function when
	 * not available (odbc_fetch_object requires unixODBC)
	 *
	 * @access	private
	 * @return	object
	 */
	function _odbc_fetch_object(& $odbc_result) {
		$rs = array();
		$rs_obj = FALSE;
		if (odbc_fetch_into($odbc_result, $rs)) {
			foreach ($rs as $k=>$v) {
				$field_name= odbc_field_name($odbc_result, $k+1);
				$rs_obj->$field_name = $v;
			}
		}
		return $rs_obj;
	}


	/**
	 * Result - array
	 *
	 * subsititutes the odbc_fetch_array function when
	 * not available (odbc_fetch_array requires unixODBC)
	 *
	 * @access	private
	 * @return	array
	 */
	function _odbc_fetch_array(& $odbc_result) {
		$rs = array();
		$rs_assoc = FALSE;
		if (odbc_fetch_into($odbc_result, $rs)) {
			$rs_assoc=array();
			foreach ($rs as $k=>$v) {
				$field_name= odbc_field_name($odbc_result, $k+1);
				$rs_assoc[$field_name] = $v;
			}
		}
		return $rs_assoc;
	}

}


/* End of file odbc_result.php */
/* Location: ./system/database/drivers/odbc/odbc_result.php */
