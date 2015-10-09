<?php

/*
Plugin Name: FormBuilder 2
Plugin Url: http://github.com/roundhouse/formbuilder2
Author: Vadim Goncharov (https://github.com/owldesign)
Author URI: http://roundhouseagency.com
Description: Form builder for craft cms. Lets you build multiple forms with custom fields. Dynamically display the forms in your templates. Upon submission the forms are saved and stored in the database as well as notification sent to the form's owner.
Version: 0.0.1
*/

namespace Craft;

class FormBuilder2Plugin extends BasePlugin
{

	public function getName()
	{
		return 'FormBuilder 2';
	}

	public function getVersion()
	{
		return '0.0.1';
	}

	public function getDeveloper()
	{
		return 'Roundhouse Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://github.com/roundhouse';
	}

  protected function defineSettings()
  {
    return array(
      'inputTemplatePath'   => array(AttributeType::String, 'default' => 'templates/forms/input', 'required' => true)
    );
  }

  public function getSettingsHtml()
  {
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();

    return craft()->templates->render('formbuilder2/configuration', array(
      'settings' => $settings
    ));

 }

	public function hasCpSection()
  {
    return true;
  }

  public function registerCpRoutes()
  {
    return array(
      'formbuilder2'                  => array('action' => 'formBuilder2/dashboard'),
      'formbuilder2/configuration'    => array('action' => 'formBuilder2_Configuration/configuration'),
      'formbuilder2/entries'          => array('action' => 'formBuilder2_Entry/allEntries'),
      'formbuilder2/forms'            => array('action' => 'formBuilder2_Form/allForms'),
      'formbuilder2/form/create'      => array('action' => 'formBuilder2_Form/editForm'),
      // 'formbuilder2/(?P<recipeId>\d+)' => 'cocktailrecipes/_edit',
    );
  }

}
