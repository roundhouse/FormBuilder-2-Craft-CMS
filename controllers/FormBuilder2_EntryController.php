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
    // Set Up Form Submission
    $form = craft()->formBuilder2_entry->getFormByHandle(craft()->request->getPost('formHandle'));
    $formFields = $form->fieldLayout->getFieldLayout()->getFields();
    $submission = craft()->request->getPost();
    $submissionData = $this->filterSubmissionKeys($submission);

    // Defaults
    $saveSubmissionsToDatabase  = $form->saveSubmissionsToDatabase;
    $customRedirect             = $form->customRedirect;
    $useAjax                    = $form->ajaxSubmit;
    $spamTimeSubmissions        = $form->spamTimeMethod;
    $spamHoneypotSubmissions    = $form->spamHoneypotMethod;
    $notifyAdminOfSubmission    = $form->notifySubmission;
    $hasFileUploads             = $form->hasFileUploads;

    // Using Ajax
    if ($useAjax) {
      $this->requireAjaxRequest();
    } else {
      $this->requirePostRequest();
    }

    // Set Up Entry Model
    $submissionEntry = new FormBuilder2_EntryModel();

    // Custom Redirect
    if ($customRedirect) {
      $redirectUrl = $form->customRedirectUrl;
    }

    
    // Form File Uplodas
    if ($form->hasFileUploads) {
      $fileName = [];
      $fileTmpName = [];
      $fileSize = [];
      $fileKind = [];
      $uniqueFileName = [];
      foreach ($_FILES as $key => $value) {
        $fileName[] = $value['name'];
        $fileTmpName[] = $value['tmp_name'];
        $fileSize[] = $value['size'];
        $fileKind[] = IOHelper::getFileKind(IOHelper::getExtension($value['name']));
        $submissionData[$key] = uniqid() . '-' . $value['name'];
      }
    }


    // Validate Fields
    $validated = craft()->formBuilder2_entry->validateEntry($form, $submissionData);
    if (!empty($validated)) {
      foreach ($validated as $key => $value) {
        craft()->userSession->setFlash('error', $value);
      }
      craft()->urlManager->setRouteVariables(array(
        'errors' => $validated
      ));
    }

    $submissionEntry->formId     = $form->id;
    $submissionEntry->title      = $form->name;
    $submissionEntry->data       = $submissionData;

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
