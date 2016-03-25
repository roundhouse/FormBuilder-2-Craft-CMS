<?php

/*
Plugin Name: FormBuilder 2
Plugin Url: https://github.com/roundhouse/FormBuilder-2
Author: Vadim Goncharov (https://github.com/owldesign)
Author URI: http://roundhouseagency.com
Description: FormBuilder 2 is a Craft CMS plugin that lets you create forms for your front-end.
Version: 0.1.0
*/

namespace Craft;

class FormBuilder2Plugin extends BasePlugin
{

  public function init()
  {
    if (craft()->request->isCpRequest()) {
      craft()->templates->hook('formBuilder2.prepCpTemplate', array($this, 'prepCpTemplate'));
    }
  }

  public function getReleaseFeedUrl()
  {
    return 'https://github.com/roundhouse/FormBuilder-2-Craft-CMS/blob/master/releases.json';
  }

	public function getName()
	{
		return 'FormBuilder 2';
	}

	public function getVersion()
	{
		return '0.1.0';
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
