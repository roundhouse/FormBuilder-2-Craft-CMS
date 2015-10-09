<?php
namespace Craft;

class FormBuilder2_EntryController extends BaseController
{

  protected $allowAnonymous = true;


  /**
   * Get All Forms
   *
   */
  public function actionAllEntries()
  { 
    $variables['forms'] = craft()->formBuilder2_form->getAllForms();
    $variables['settings'] = craft()->plugins->getPlugin('FormBuilder2');
    return $this->renderTemplate('formbuilder2/entries/index', $variables);
  }

  

}
