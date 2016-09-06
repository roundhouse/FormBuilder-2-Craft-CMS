<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160906_181418_formbuilder2_UpdateTemplatesColumnType extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$this->alterColumn('formbuilder2_templates', 'bodyText', ColumnType::Text);
		$this->alterColumn('formbuilder2_templates', 'footerText', ColumnType::Text);
		$this->alterColumn('formbuilder2_templates', 'altText', ColumnType::Text);

		return true;
	}
}
