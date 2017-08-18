<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170814_172454_formbuilder2_RenameFormTableColumns extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->renameColumn('formbuilder2_forms', 'formSettings', 'options');
        $this->renameColumn('formbuilder2_forms', 'spamProtectionSettings', 'spam');
        $this->renameColumn('formbuilder2_forms', 'messageSettings', 'messages');
        $this->renameColumn('formbuilder2_forms', 'notificationSettings', 'notify');
        $this->renameColumn('formbuilder2_forms', 'extra', 'settings');
		return true;
	}
}
