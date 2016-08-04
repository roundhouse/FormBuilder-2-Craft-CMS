<?php

/*
Plugin Name: FormBuilder 2
Plugin Url: https://github.com/roundhouse/FormBuilder-2
Author: Vadim Goncharov (https://github.com/owldesign)
Author URI: http://roundhouseagency.com
Description: FormBuilder 2 is a Craft CMS plugin that lets you create forms for your front-end.
Version: 2.0.4
*/

namespace Craft;

class FormBuilder2Plugin extends BasePlugin
{

  public function init()
  {
    if (craft()->request->isCpRequest()) {
      craft()->templates->hook('formBuilder2.prepCpTemplate', array($this, 'prepCpTemplate'));
    }

    // Add this to the fieldlayout template
    if(craft()->request->isCpRequest() && !craft()->request->isAjaxRequest()) {
      craft()->templates->includeJs('CustomField.labels=' . json_encode($this->_getLabels()));
      craft()->templates->includeJs('CustomField.fields=' . json_encode($this->_getFields()));
    }

    craft()->on('fields.saveFieldLayout', function(Event $e)
    {
      $layout = $e->params['layout'];
      $customfield = craft()->request->getPost('customfield');

      if($customfield)
      {
        $transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();
        try
        {
          foreach($customfield as $fieldId => $labelInfo)
          {
            $label = new FormBuilder2_FieldModel();
            $label->fieldId = $fieldId;
            $label->fieldLayoutId = $layout->id;

            if(array_key_exists('template', $labelInfo))
            {
              $label->template = $labelInfo['template'];
            }

            craft()->formBuilder2_field->saveLabel($label);
          }

          if($transaction)
          {
            $transaction->commit();
          }
        }
        catch(\Exception $e)
        {
          if($transaction)
          {
            $transaction->rollback();
          }

          throw $e;
        }

        // Make sure these labels don't get saved more than once
        unset($_POST['customfield']);
      }
    });
  }

  private function _getFields()
  {
    $fields = craft()->fields->getAllFields();
    $output = array();

    foreach($fields as $field)
    {
      $output[(int) $field->id] = array(
        'id' => (int) $field->id,
        'handle' => $field->handle,
        'name' => $field->name,
        'instructions' => $field->instructions
      );
    }

    return $output;
  }

  private function _getLabels()
  {
    $labels = craft()->formBuilder2_field->getAllLabels();
    $output = array();

    foreach($labels as $label)
    {
      $output[$label->id] = array(
        'id' => (int) $label->id,
        'fieldId' => (int) $label->fieldId,
        'fieldLayoutId' => (int) $label->fieldLayoutId,
        'template' => Craft::t($label->template)
      );
    }

    return $output;
  }

  private function _getLayouts()
  {
    $formBuilder = craft()->formBuilder2_form->getAllForms();
    // $assetSources = craft()->assetSources->getAllSources();
    // $categoryGroups = craft()->categories->getAllGroups();
    // $globalSets = craft()->globals->getAllSets();
    // $entryTypes = EntryTypeModel::populateModels(EntryTypeRecord::model()->ordered()->findAll());
    // $tagGroups = craft()->tags->getAllTagGroups();
    //$userFields = FieldLayoutModel::populateModel(FieldLayoutRecord::model()->findByAttributes('type', ElementType::User));

    $sections = craft()->sections->getAllSections();
    $singleSections = array();

    foreach($sections as $section)
    {
      $entryType = $section->getEntryTypes()[0];
      $singleSections[$section->id] = (int) $entryType->fieldLayoutId;
    }

    return array(
      'formBuilder' => $this->_mapLayouts($formBuilder),
      // 'assetSource' => $this->_mapLayouts($assetSources),
      // 'categoryGroup' => $this->_mapLayouts($categoryGroups),
      // 'globalSet' => $this->_mapLayouts($globalSets),
      // 'entryType' => $this->_mapLayouts($entryTypes),
      // 'tagGroup' => $this->_mapLayouts($tagGroups),
      //'userFields' => $userFields->id,
      // 'singleSection' => $singleSections,
    );
  }

  private function _mapLayouts($list)
  {
    $output = array();

    foreach($list as $item)
    {
      $output[(int) $item->id] = (int) $item->fieldLayoutId;
    }

    return $output;
  }

  public function getReleaseFeedUrl()
  {
    return 'https://raw.githubusercontent.com/roundhouse/FormBuilder-2-Craft-CMS/master/releases.json';
  }

	public function getName()
	{
		return 'FormBuilder 2';
	}

	public function getVersion()
	{
		return '2.0.4';
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

  public function prepCpTemplate(&$context)
  {
    $context['subnav'] = array();
    $context['subnav']['dashboard'] = array('label' => Craft::t('Dashboard'), 'url' => 'formbuilder2/dashboard');
    $context['subnav']['forms'] = array('label' => Craft::t('Forms'), 'url' => 'formbuilder2/forms');
    $context['subnav']['entries'] = array('label' => Craft::t('Entries'), 'url' => 'formbuilder2/entries');
    $context['subnav']['configuration'] = array('icon' => 'settings', 'label' => Craft::t('Configuration'), 'url' => 'formbuilder2/tools/configuration');
  }

  public function addTwigExtension()  
  {
    Craft::import('plugins.formbuilder2.twigextensions.FormBuilder2TwigExtension');
    return new FormBuilder2TwigExtension();
  }

  public function registerCpRoutes()
  {
    return array(
      'formbuilder2'                                  => array('action' => 'formBuilder2/dashboard'),
      'formbuilder2/dashboard'                        => array('action' => 'formBuilder2/dashboard'),
      'formbuilder2/tools/configuration'              => array('action' => 'formBuilder2/configurationIndex'),
      'formbuilder2/tools/backup-restore'             => array('action' => 'formBuilder2/backupRestoreIndex'),
      'formbuilder2/tools/export'                     => array('action' => 'formBuilder2/exportIndex'),
      'formbuilder2/forms'                            => array('action' => 'formBuilder2_Form/formsIndex'),
      'formbuilder2/forms/new'                        => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/forms/(?P<formId>\d+)'            => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/forms/(?P<formId>\d+)/edit'       => array('action' => 'formBuilder2_Form/editForm'),
      'formbuilder2/entries'                          => array('action' => 'formBuilder2_Entry/entriesIndex'),
      'formbuilder2/entries/(?P<entryId>\d+)/edit'    => array('action' => 'formBuilder2_Entry/viewEntry')
    );
  }

}
