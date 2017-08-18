<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170816_214828_formbuilder2_RenameTemplateTableColumns extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        FormBuilder2Plugin::log("DROPPING COLUMNS>>>>>: ", LogLevel::Info, true);
        $this->dropColumn('formbuilder2_templates', 'layoutId');
        $this->dropColumn('formbuilder2_templates', 'altText');
        $this->dropColumn('formbuilder2_templates', 'bodyText');
        $this->dropColumn('formbuilder2_templates', 'footerText');
        $this->renameColumn('formbuilder2_templates', 'templateContent', 'content');
        $this->renameColumn('formbuilder2_templates', 'templateStyles', 'styles');
        $this->renameColumn('formbuilder2_templates', 'templateSettings', 'settings');
        FormBuilder2Plugin::log("END DROPPING COLUMNS>>>>>: ", LogLevel::Info, true);

		return true;
	}
}
