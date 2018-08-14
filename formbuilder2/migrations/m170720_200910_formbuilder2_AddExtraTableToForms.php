<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170720_200910_formbuilder2_AddExtraTableToForms extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->addColumnAfter('formbuilder2_forms', 'extra', array(
            ColumnType::Text
        ), 'notificationSettings');

        return true;
	}
}
