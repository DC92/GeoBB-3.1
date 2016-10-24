<?php
/**
*
* Spacial objects extension for the phpBB Forum Software package.
*
* @copyright (c) 2016 Dominique Cavailhez
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace Dominique92\GeoBB\migrations;

/**
 * Migration stage 1: Schema changes
 */
class m1_schema extends \phpbb\db\migration\migration
{
	/**
	 * Check if this migration is effectively installed
	 *
	 * @return bool True if this migration is installed, False if this migration is not installed
	 * @access public
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'geom');
	}

	/**
	 * Add the specific columns to the posts table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		// Save local config parameters (to be able to clone an existing PHPBB forum)
		$result = $this->db->sql_query('SELECT * FROM '.CONFIG_TABLE);
		while ($row = $this->db->sql_fetchrow($result))
			if (in_array(
				$row['config_name'],
				[
					'server_name',
					'cookie_domain',
					'cookie_name',
					'avatar_salt',
					'plupload_salt',
					'questionnaire_unique_id',
				]))
				$config_upd [] =
					'UPDATE '.CONFIG_TABLE.
					' SET config_value = "'.$row['config_value'].'"'.
					' WHERE config_name = "'.$row['config_name'].'";';
		file_put_contents ('config_base.sql', implode(PHP_EOL, $config_upd));

		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'posts'	=> array(
					'geom' => array('TEXT', null),
					'geo_altitude' => array('VCHAR:12', null),
					'geo_massif' => array('VCHAR:50', null),
				),
			),
		);
	}

	/**
	 * Drop the specific columns from the posts table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'posts'	=> array(
					'geom',
					'geo_altitude',
					'geo_massif',
				),
			),
		);
	}
}
