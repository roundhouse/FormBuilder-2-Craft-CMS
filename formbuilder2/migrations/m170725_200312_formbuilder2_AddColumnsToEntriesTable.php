<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170725_200312_formbuilder2_AddColumnsToEntriesTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->addColumnAfter('formbuilder2_entries', 'userAgent', array(ColumnType::Text, 'required' => false), 'submission');
        $this->addColumnAfter('formbuilder2_entries', 'ipAddress', array(ColumnType::Varchar, 'required' => false), 'userAgent');

		return true;
	}
}
