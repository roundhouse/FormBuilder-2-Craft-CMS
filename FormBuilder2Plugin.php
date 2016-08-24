<?php

/*
Plugin Name: FormBuilder 2
Plugin Url: https://github.com/roundhouse/FormBuilder-2
Author: Vadim Goncharov (https://github.com/owldesign)
Author URI: http://roundhouseagency.com
Description: FormBuilder 2 is a Craft CMS plugin that lets you create forms for your front-end.
Version: 2.0.7
*/

namespace Craft;

class FormBuilder2Plugin extends BasePlugin
{

  public function init()
  {

    // Getting date for releases.json
    // Craft::dd(DateTimeHelper::toIso8601(DateTimeHelper::currentTimeStamp()));

    if (craft()->request->isCpRequest()) {
      craft()->templates->hook('formBuilder2.prepCpTemplate', array($this, 'prepCpTemplate'));
    }

    craft()->on('fields.saveFieldLayout', function(Event $e) {
      $layout = $e->params['layout'];
      $customfield = craft()->request->getPost('customfield');

      if($customfield) {
        $transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();
        try {
          foreach($customfield as $fieldId => $labelInfo) {
            $label = new FormBuilder2_FieldModel();
            $label->fieldId = $fieldId;
            $label->fieldLayoutId = $layout->id;

            if(array_key_exists('template', $labelInfo)) {
              $label->template = $labelInfo['template'];
            }

            craft()->formBuilder2_field->saveLabel($label);
          }

          if($transaction) {
            $transaction->commit();
          }
        } catch(\Exception $e) {
          if($transaction) {
            $transaction->rollback();
          }

          throw $e;
        }
        unset($_POST['customfield']);
      }
    });
  }

  public function getReleaseFeedUrl()
  {
    return 'https://raw.githubusercontent.com/roundhouse/FormBuilder-2-Craft-CMS/master/releases.json';
  }

	public function getName()
	{
    $settings = $this->getSettings();
    if ($settings->pluginName) {
      return $settings->pluginName;
    }
		return 'FormBuilder 2';
	}

	public function getVersion()
	{
		return '2.0.7';
	}

	public function getDeveloper()
	{
		return 'Roundhouse Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://github.com/roundhouse';
	}

  public function getDocumentationUrl()
  {
    return 'https://github.com/roundhouse/FormBuilder-2-Craft-CMS';
  }
  
  public function getDescription()
  {
    return Craft::t('Simply Forms Manager');;
  }

	public function hasCpSection()
  {
    return true;
  }

  public function getSettingsUrl()
  {
    return 'formbuilder2/tools/configuration';
  }

  /**
   * Plugin settings.
   *
   * @return array
   */
  protected function defineSettings()
  {
    return array(
      'pluginName'   => array(AttributeType::String),
      'canDoActions' => array(AttributeType::Bool, 'default' => false)
    );
  }

  public function prepCpTemplate(&$context)
  {
    $pluginSettings = $this->getSettings();
    $context['subnav'] = array();
    $context['subnav']['dashboard'] = array('label' => Craft::t('Dashboard'), 'url' => 'formbuilder2/dashboard');
    if (craft()->userSession->isAdmin() || $pluginSettings->canDoActions) {
        $context['subnav']['forms'] = array('label' => Craft::t('Forms'), 'url' => 'formbuilder2/forms');
    }
    $context['subnav']['entries'] = array('label' => Craft::t('Entries'), 'url' => 'formbuilder2/entries');
    if (craft()->userSession->isAdmin() || $pluginSettings->canDoActions) {
        $context['subnav']['templates'] = array('icon' => 'settings', 'label' => Craft::t('Templates'), 'url' => 'formbuilder2/templates');
        $context['subnav']['configuration'] = array('icon' => 'settings', 'label' => Craft::t('Configuration'), 'url' => 'formbuilder2/tools/configuration');
    }
  }

  public function addTwigExtension()  
  {
    Craft::import('plugins.formbuilder2.twigextensions.FormBuilder2TwigExtension');
    return new FormBuilder2TwigExtension();
  }

  public function registerCpRoutes()
  {
    return array(
      'formbuilder2'                                      => array('action' => 'formBuilder2_Dashboard/dashboard'),
      'formbuilder2/dashboard'                            => array('action' => 'formBuilder2_Dashboard/dashboard'),
      'formbuilder2/tools/configuration'                  => array('action' => 'formBuilder2/configurationIndex'),
      'formbuilder2/tools/backup-restore'                 => array('action' => 'formBuilder2/backupRestoreIndex'),
      'formbuilder2/tools/export'                         => array('action' => 'formBuilder2/exportIndex'),
      'formbuilder2/forms'                                => array('action' => 'formBuilder2_Form/formsIndex'),
      'formbuilder2/forms/new'                            => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/forms/(?P<formId>\d+)'                => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/forms/(?P<formId>\d+)/edit'           => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/entries'                              => array('action' => 'formBuilder2_Entry/entriesIndex'),
      'formbuilder2/entries/(?P<entryId>\d+)/edit'        => array('action' => 'formBuilder2_Entry/viewEntry'),
      'formbuilder2/templates'                            => array('action' => 'formBuilder2_Template/index'),
      'formbuilder2/templates/new'                        => array('action' => 'formBuilder2_Template/editTemplate'),
      'formbuilder2/templates/(?P<templateId>\d+)'        => array('action' => 'formBuilder2_Template/editTemplate'),
      'formbuilder2/templates/(?P<templateId>\d+)/edit'   => array('action' => 'formBuilder2_Template/editTemplate'),
      'formbuilder2/templates/layouts'                            => array('action' => 'formBuilder2_Layout/index'),
      'formbuilder2/templates/layouts/new'                        => array('action' => 'formBuilder2_Layout/editLayout'),
      'formbuilder2/templates/layouts/(?P<layoutId>\d+)'          => array('action' => 'formBuilder2_Layout/editLayout'),
      'formbuilder2/templates/layouts/(?P<layoutId>\d+)/edit'     => array('action' => 'formBuilder2_Layout/editLayout'),
    );
  }

}
