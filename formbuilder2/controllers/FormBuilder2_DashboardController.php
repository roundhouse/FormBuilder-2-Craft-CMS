<?php
namespace Craft;

class FormBuilder2_DashboardController extends BaseController
{
 
 	protected $allowAnonymous = true;


  public function actionDashboard()
  {

    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugin = craft()->plugins->getPlugin('FormBuilder2');

    $variables['title']       = 'FormBuilder2';
    $variables['settings']    = $settings;
    $variables['plugin']      = $plugin;

    return $this->renderTemplate('formbuilder2/dashboard', $variables);
  }


}
