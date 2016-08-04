<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160803_191207_formbuilder2_AddFieldsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_fields table
        craft()->db->createCommand()->createTable('formbuilder2_fields', array(
            'fieldId'       => array('column' => 'integer', 'required' => false),
            'fieldLayoutId' => array('column' => 'integer', 'required' => false),
            'template'      => array(),
        ), null, true);

        // Add foreign keys to craft_fields
        craft()->db->createCommand()->addForeignKey('formbuilder2_fields', 'fieldId', 'fields', 'id', 'CASCADE', null);
        craft()->db->createCommand()->addForeignKey('formbuilder2_fields', 'fieldLayoutId', 'fieldlayouts', 'id', 'CASCADE', null);
	}
}
