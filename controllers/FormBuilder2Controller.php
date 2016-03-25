<?php
namespace Craft;

class FormBuilder2Controller extends BaseController
{
 
 	protected $allowAnonymous = true;


  public function actionGetFormFields()
  {
    $formId = craft()->request->getPost('formId');
    $form = craft()->formBuilder2_form->getFormById($formId);


    if ($formId) {
      $field = [];
      $fields = $form->fieldLayout->getFieldLayout()->getFields();
      foreach ($fields as $key => $value) {
        $field[$key] = $value->getField();
      }

      $this->returnJson($field);
    }
  }

	/**
	 * Load Dashboard
	 *
	 */
	public function actionDashboard()
	{
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugin = craft()->plugins->getPlugin('FormBuilder2');

    $variables['title']       = 'FormBuilder2';
    $variables['settings']    = $settings;
    $variables['plugin']      = $plugin;
    $variables['navigation']  = $this->navigation();

    return $this->renderTemplate('formbuilder2/dashboard', $variables);
	}

	/**
	 * Export Page
	 *
	 */
	public function actionExportIndex()
	{
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugin = craft()->plugins->getPlugin('FormBuilder2');

    $variables['title']       = 'FormBuilder2';
    $variables['settings']    = $settings;
    $variables['plugin']      = $plugin;
    $variables['navigation']  = $this->navigation();

    return $this->renderTemplate('formbuilder2/tools/export', $variables);
	}

	/**
	 * Export All Forms
	 *
	 */
	public function actionExportAllEntries()
	{
		// TODO: Make EXPORTS WORK
		$this->requirePostRequest();

		$entries = FormBuilder2_EntryRecord::model()->findAll();

		$attributes = [];
		$submission = [];
		$files 			= [];

    foreach ($entries as $key => $entry)  {
    	$entry = $entry->getAttributes();
    	
    	foreach ($entry['submission'] as $index => $value) {
    		$field = craft()->fields->getFieldByHandle($index);
    		$submission[$index] = $field->name . ':' . $value;
    	}

    	if ($entry['files']) {
    		foreach ($entry['files'] as $index => $value) {
    			$file = craft()->assets->getFileById($value);
    			$submission[$index] = 'File:' . $file->getUrl();
    		}
    	}
    	$attributes[$key]['id'] 				= $entry['id'];
    	$attributes[$key]['formId'] 		= $entry['formId'];
    	$attributes[$key]['title'] 			= $entry['title'];
    	$attributes[$key]['submission'] = StringHelper::arrayToString($submission, ',');
    }

    $date = uniqid(gmdate('Y-m-d-'));
    $filename = 'formbuilder2_entries_' . $date . '.csv';
  	
		header('Content-Type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		$output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Form ID', 'Form Name', 'Submission'));
    foreach ($attributes as $line) {
      fputcsv($output, $line, ',');
    }
    exit();

		// craft()->request->sendFile('filename.csv', $output, array('forceDownload' => true));
	}

	/**
	 * Get Plugin Settings for Configuration Page
	 *
	 */
	public function actionConfigurationIndex()
	{
	  $plugin = craft()->plugins->getPlugin('FormBuilder2');
	  $settings = $plugin->getSettings();
	  
	  $variables['title']       = 'FormBuilder2';
	  $variables['settings']    = $settings;
    $variables['plugin']      = $plugin;
	  $variables['navigation']  = $this->navigation();
	  
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
    
    $variables['title']       = 'FormBuilder2';
    $variables['settings']    = $settings;
    $variables['plugin']      = $plugin;
    $variables['navigation']  = $this->navigation();
    
    $this->renderTemplate('formbuilder2/tools/backup-restore', $variables);
	}

	/**
	 * Export All Forms
	 *
	 */
	public function actionBackupAllForms()
	{
		// TODO: look at this for saving files http://craftcms.stackexchange.com/questions/2179/how-can-i-force-a-download-of-entry-data-to-an-excel-file
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

  /**
   * Sidebar Navigation
   *
   */
	public function navigation()
  {
    $navigationSections = [
      [
        'heading' => Craft::t('Menu'),
        'nav'     => [
          [
            'label' => Craft::t('Dashboard'),
            'icon'  => 'tachometer',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2'),
          ],
          [
            'label' => Craft::t('Forms'),
            'icon'  => 'list-alt',
            'extra' => craft()->formBuilder2_form->getTotalForms(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms'),
          ],
          [
            'label' => Craft::t('Entries'),
            'icon'  => 'file-text-o',
            'extra' => craft()->formBuilder2_entry->getTotalEntries(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/entries'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Quick Links'),
        'nav'     => [
          [
            'label' => Craft::t('Create New Form'),
            'icon'  => 'pencil-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Tools'),
        'nav'     => [
          [
            'label' => Craft::t('Export'),
            'icon'  => 'share-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/export'),
          ],
          [
            'label' => Craft::t('Configuration'),
            'icon'  => 'sliders',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/configuration'),
          ],
        ]
      ],
    ];
    return $navigationSections;
  }

}
