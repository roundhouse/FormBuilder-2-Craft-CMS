<?php
namespace Craft;

class FormBuilder2_EntryController extends BaseController
{

  protected $allowAnonymous = true;


  /**
   * Entries Index
   *
   */
  public function actionEntriesIndex()
  { 
    $formItems = craft()->formBuilder2_form->getAllForms();
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugins = craft()->plugins->getPlugin('FormBuilder2');

    return $this->renderTemplate('formbuilder2/entries/index', array(
      'formItems'  => $formItems,
      'settings'  => $settings
    ));
  }

  /**
   * Submit Entry
   *
   */
  public function actionSubmitEntry()
  {
    $this->requirePostRequest();

    $post = craft()->request->getPost();
    $redirectUrl = craft()->request->getPost('formRedirect');

    var_dump($post);
    var_dump($redirectUrl);
  }
  

}
