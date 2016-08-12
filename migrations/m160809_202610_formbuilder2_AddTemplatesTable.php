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
			'name'               => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'handle'             => array('maxLength' => 255, 'column' => 'varchar', 'required' => true),
			'templateLayout'     => array('column' => 'text'),
			'templateBodyCopy'   => array('required' => true, 'column' => 'text'),
			'templateFooterCopy' => array('required' => true, 'column' => 'text'),
			'templateContent'    => array('column' => 'text'),
			'templateStyles'     => array('column' => 'text'),
			'templateSettings'   => array('column' => 'text'),
			'templateAltCopy'    => array('required' => true, 'column' => 'text'),
			'templateAltCopy2'   => array('required' => true, 'column' => 'text'),
			'templateAltCopy3'   => array('required' => true, 'column' => 'text'),
			'templateAltCopy4'   => array('required' => true, 'column' => 'text'),
			'templateAltCopy5'   => array('required' => true, 'column' => 'text'),
		), null, true);
	}
}
