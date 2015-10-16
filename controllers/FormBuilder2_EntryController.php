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
    // $this->requireAjaxRequest();

    // Set Up Form Submission
    $form = craft()->formBuilder2_entry->getFormByHandle(craft()->request->getPost('formHandle'));
    $submission = craft()->request->getPost();
    $submissionData = $this->filterSubmissionKeys($submission);

    // Defaults
    $customRedirect = false;
    $useAjax = false;
    $spamTimeSubmissions = false;
    $spamHoneypotSubmissions = false;
    $notifyAdminOfSubmission = false;
    $files = false;

    // Set Up Entry Model
    $submissionEntry = new FormBuilder2_EntryModel();

    if (is_array($_FILES)) {
      echo 'yes files';
    } 
    var_dump($submissionData);
  }

  /**
   * Filter Out Unused Post Submission
   *
   */
  protected function filterSubmissionKeys($submission)
  {
    $filterKeys = array(
      'action',
      'formRedirect',
      'formHandle'
    );
    if (is_array($submission)) {
      foreach ($submission as $k => $v) {
        if (in_array($k, $filterKeys)) {
          unset($submission[$k]);
        }
      }
    }
    return $submission;
  }
  

}
