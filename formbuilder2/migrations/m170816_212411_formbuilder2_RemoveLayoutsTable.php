<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170816_212411_formbuilder2_RemoveLayoutsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        FormBuilder2Plugin::log("DROPPING LAYOUTS>>>>>: ", LogLevel::Info, true);
        // Remove Layouts Table
        $this->dropTableIfExists('formbuilder2_layouts');
        FormBuilder2Plugin::log("END DROPPING LAYOUTS>>>>>: ", LogLevel::Info, true);

		return true;
	}
}
