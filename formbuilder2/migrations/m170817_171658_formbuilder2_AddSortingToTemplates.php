<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170817_171658_formbuilder2_AddSortingToTemplates extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->addColumnAfter('formbuilder2_templates', 'sortOrder', array(
            ColumnType::SmallInt, 
            'default' => 0
        ), 'handle');

		return true;
	}
}
