<?php
namespace Craft;

class FormBuilder2_ConfigurationController extends BaseController
{

  protected $allowAnonymous = true;

  /**
   * Get Plugin Settings for Configuration Page
   *
   */
  public function actionConfigurationIndex()
  {
    $formItems = craft()->formBuilder2_form->getAllForms();
    $plugin = craft()->plugins->getPlugin('FormBuilder2');
    $settings = $plugin->getSettings();
    
    // Get Logo Asset
    if ($settings['emailNotificationLogo']) {
      $criteria           = craft()->elements->getCriteria(ElementType::Asset);
      $criteria->id       = $settings['emailNotificationLogo'];
      $criteria->limit    = 1;
      $elements           = $criteria->find();
    } else {
      $elements = [];
    }

    $variables['title']     = 'FormBuilder2';
    $variables['settings']  = $settings;
    $variables['plugin']    = $plugin;
    $variables['elements']  = $elements;
    
    $this->renderTemplate('formbuilder2/configuration', $variables);
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

}
