<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160813_010312_formbuilder2_AddLayoutsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_formbuilder2_layouts table
		craft()->db->createCommand()->createTable('formbuilder2_layouts', array(
			'name'         => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'handle'       => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'description'  => array(),
			'type'         => array(),
			'icon'         => array('column' => 'text'),
			'templateName' => array(),
			'templateOriginalName' => array(),
			'templatePath' => array('required' => true),
			'screenshot'   => array('column' => 'text'),
		), null, true);

		// Add indexes to craft_formbuilder2_layouts
		craft()->db->createCommand()->createIndex('formbuilder2_layouts', 'id', true);
		craft()->db->createCommand()->createIndex('formbuilder2_layouts', 'handle', true);

	}
}
