<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160809_202610_formbuilder2_AddTemplatesTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_formbuilder2_templates table
		craft()->db->createCommand()->createTable('formbuilder2_templates', array(
			'name'             => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'handle'           => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'templateFile'     => array('column' => 'text'),
			'templateContent'  => array('column' => 'text'),
			'templateStyles'   => array('column' => 'text'),
			'templateSettings' => array('column' => 'text'),
		), null, true);
	}
}
