<?php
namespace Craft;

class FormBuilder2Controller extends BaseController
{
 
 	protected $allowAnonymous = true;

	/**
	 * Load Dashboard
	 *
	 */
	public function actionDashboard()
	{
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugins = craft()->plugins->getPlugin('FormBuilder2');

    return $this->renderTemplate('formbuilder2/dashboard', array(
      'settings'  => $settings,
      'plugin'  => $plugins,
      'title'  => 'FormBuilder2'
    ));
	}

	/**
	 * Get Plugin Settings for Configuration Page
	 *
	 */
	public function actionConfigurationIndex()
	{
	  $plugin = craft()->plugins->getPlugin('FormBuilder2');
	  $settings = $plugin->getSettings();
	  
	  $variables['title']     = 'FormBuilder2';
	  $variables['settings']  = $settings;
	  $variables['plugin']    = $plugin;
	  
	  $this->renderTemplate('formbuilder2/tools/configuration', $variables);
	}

	/**
	 * Saves a plugin's settings.
	 *
	 */
	public function actionSavePluginSettings()
	{
	  $this->requirePostRequest();
	  $pluginClass = craft()->request->getRequiredPost('pluginClass');
	  $settings = craft()->request->getPost();

	  $plugin = craft()->plugins->getPlugin($pluginClass);
	  if (!$plugin)
	  {
	    throw new Exception(Craft::t('No plugin exists with the class “{class}”', array('class' => $pluginClass)));
	  }

	  if (craft()->plugins->savePluginSettings($plugin, $settings))
	  {
	    craft()->userSession->setNotice(Craft::t('Plugin settings saved.'));

	    $this->redirectToPostedUrl();
	  }

	  craft()->userSession->setError(Craft::t('Couldn’t save plugin settings.'));

	  // Send the plugin back to the template
	  craft()->urlManager->setRouteVariables(array(
	    'settings' => $settings
	  ));
	}

	/**
	 * Export & Import Index
	 *
	 */
	public function actionBackupRestoreIndex()
	{
    $plugin = craft()->plugins->getPlugin('FormBuilder2');
    $settings = $plugin->getSettings();
    
    $variables['title']     = 'FormBuilder2';
    $variables['settings']  = $settings;
    $variables['plugin']    = $plugin;
    
    $this->renderTemplate('formbuilder2/tools/backup-restore', $variables);
	}

	/**
	 * Export All Forms
	 *
	 */
	public function actionBackupAllForms()
	{
		$this->requirePostRequest();
		$response = craft()->formBuilder2->backupAllForms();
		if (!$response) {
			craft()->templates->includeJs('var message = "You do not have any forms to backup!"; var notifications = new Craft.CP; notifications.displayNotification("error", message);');
		}
	}

	/**
	 * Restore Forms
	 *
	 */
	public function actionRestoreForms()
	{
		$this->requirePostRequest();
		$restoreFile = craft()->request->getPost('restoreForms');
		$filePath = \CUploadedFile::getInstanceByName('restoreForms');
		$sqlFileContents = StringHelper::arrayToString(IOHelper::getFileContents($filePath->getTempName(), true), '');

		$result = craft()->db->createCommand()->setText($sqlFileContents)->queryAll();

	}

}
