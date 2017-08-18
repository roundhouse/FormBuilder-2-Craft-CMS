<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170817_192019_formbuilder2_AddTypeToTemplates extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->addColumnAfter('formbuilder2_templates', 'type', array(ColumnType::Varchar, 'required' => true), 'handle');
		return true;
	}
}
