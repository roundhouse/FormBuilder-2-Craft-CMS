<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170714_202735_formbuilder2_AddSortOrderToForms extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->addColumnAfter('formbuilder2_forms', 'sortOrder', array(
            ColumnType::SmallInt, 
            'default' => 0
        ), 'fieldLayoutId');

		return true;
	}
}
