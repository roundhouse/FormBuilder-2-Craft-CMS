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

    $variables['title']       = 'FormBuilder2';
    $variables['formItems']   = $formItems;
    $variables['settings']    = $settings;
    $variables['navigation']  = $this->navigation();

    return $this->renderTemplate('formbuilder2/entries/index', $variables);
  }

  /**
   * View/Edit Entry
   *
   */
  public function actionViewEntry(array $variables = array())
  {
    $entry = craft()->formBuilder2_entry->getSubmissionById($variables['entryId']);
    if (empty($entry)) { throw new HttpException(404); }

    $files = '';
    if ($entry->files) {
      $files = [];
      foreach ($entry->files as $key => $value) {
        $files[] = craft()->assets->getFileById($value);
      }
    }

    $variables['entry']       = $entry;
    $variables['title']       = 'FormBuilder2';
    $variables['form']        = craft()->formBuilder2_form->getFormById($entry->formId);
    $variables['files']       = $files;
    $variables['submission']  = $entry->submission;
    $variables['navigation']  = $this->navigation();

    $this->renderTemplate('formbuilder2/entries/_view', $variables);
  }

  /**
   * Submit Entry
   *
   */
  public function actionSubmitEntry()
  {
    $this->requirePostRequest();
    
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // VARIABLES
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $files                    = '';
    $ajax                     = false;
    $passedValidation         = true;
    $validationErrors         = [];
    $submissionErrorMessage   = [];
    $customSuccessMessage     = '';
    $customErrorMessage       = '';
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM 
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $form = craft()->formBuilder2_entry->getFormByHandle(craft()->request->getPost('formHandle'));
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM SUBMISSION
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $formFields = $form->fieldLayout->getFieldLayout()->getFields(); // Get all form fields
    $submission = craft()->request->getPost(); // Get all values from the submitted form
    $submissionData = $this->filterSubmissionKeys($submission); // Fillter out unused submission data
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM ATTRIBUTES
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $attributes                   = $form->getAttributes();
    $formSettings                 = $attributes['formSettings'];
    $spamProtectionSettings       = $attributes['spamProtectionSettings'];
    $messageSettings              = $attributes['messageSettings'];
    $notificationSettings         = $attributes['notificationSettings'];
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM SETTINGS ||| (1) Custom Redirect, (2) File Uploads, (3) Ajax Submissions
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // (1) Custom Redirect
    if ($formSettings['formRedirect']['customRedirect'] != '') {
      $redirectUrl = $formSettings['formRedirect']['customRedirectUrl'];
    }

    // (2) File Uploads
    if ($formSettings['hasFileUploads'] == '1') {
      foreach ($formFields as $key => $value) {
        $field = $value->getField();
        switch ($field->type) {
          case 'Assets':
            foreach ($_FILES as $key => $value) {
              if (!$value['tmp_name'] == '') {
                $fileModel = new AssetFileModel();
                $folderId = $field->settings['singleUploadLocationSource'][0];
                $sourceId = $field->settings['singleUploadLocationSource'][0];
                $fileModel->originalName  = $value['tmp_name'];
                $fileModel->sourceId      = $sourceId;
                $fileModel->folderId      = $folderId;
                $fileModel->filename      = IOHelper::getFileName($value['name']);
                $fileModel->kind          = IOHelper::getFileKind(IOHelper::getExtension($value['name']));
                $fileModel->size          = filesize($value['tmp_name']);
                if ($value['tmp_name']) {
                  $fileModel->dateModified  = IOHelper::getLastTimeModified($value['tmp_name']);
                }
                if ($fileModel->kind == 'image') {
                  list ($width, $height) = ImageHelper::getImageSize($value['tmp_name']);
                  $fileModel->width = $width;
                  $fileModel->height = $height;
                }
                $files[$key]     = $fileModel;
              }
            }
          break;
        }
      }
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM CUSTOM MESSAGES ||| (1) Success Message (2) Error Message
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // (1) Success Message
    $customSuccessMessage = $messageSettings['successMessage'] ? $messageSettings['successMessage'] : Craft::t('Submission was successful.');
    // (2) Error Message
    $customErrorMessage = $messageSettings['errorMessage'] ? $messageSettings['errorMessage'] : Craft::t('There was a problem with your submission.');
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // (3) Ajax Submissions
    if ($formSettings['ajaxSubmit'] == '1') {
      $this->requireAjaxRequest();
      $ajax = true;
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FORM SPAM PROTECTION ||| (1) Timed Method (2) Honeypot Method
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // (1) Timed Method
    if ($spamProtectionSettings['spamTimeMethod'] == '1') {
      $formSubmissionTime = (int)craft()->request->getPost('spamTimeMethod');
      $submissionDuration = time() - $formSubmissionTime;
      $allowedTime = (int)$spamProtectionSettings['spamTimeMethodTime'];
      if ($submissionDuration < $allowedTime) {
        if ($ajax) {
          $this->returnJson([
            'validationErrors' => [Craft::t('You submitted too fast, you are robot!')],
            'customErrorMessage' => $customErrorMessage
          ]);
        } else {
          $spamTimedMethod = false;
          $submissionErrorMessage[] = Craft::t('You submitted too fast, you are robot!');
        }
      } else {
        $spamTimedMethod = true;
      }
    } else {
      $spamTimedMethod = true;
    }

    // (2) Honeypot Method
    if ($spamProtectionSettings['spamHoneypotMethod'] == '1') {
      $honeypotField = craft()->request->getPost('email-address-new');
      if ($honeypotField != '') {
        if ($ajax) {
          $this->returnJson([
            'validationErrors' => [Craft::t('You tried the honey, you are robot bear!')],
            'customErrorMessage' => $customErrorMessage
          ]);
        } else {
          $spamHoneypotMethod = false;
          $submissionErrorMessage[] = Craft::t('You tried the honey, you are robot bear!');
        }
      } else {
        $spamHoneypotMethod = true;
      }
    } else {
      $spamHoneypotMethod = true;
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // NEW FORM MODEL
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $submissionEntry                  = new FormBuilder2_EntryModel();
    $submissionEntry->formId          = $form->id;
    $submissionEntry->title           = $form->name;
    $submissionEntry->files           = $files;
    $submissionEntry->submission      = $submissionData;
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // FAILED SUBMISSION REDIRECT W/MESSAGES (Spam Protection)
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    if ($submissionErrorMessage) {
      craft()->userSession->setFlash('error', $customErrorMessage);
      craft()->urlManager->setRouteVariables(array(
        'errors' => $submissionErrorMessage
      ));
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // VALIDATE SUBMISSION DATA
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $validation = craft()->formBuilder2_entry->validateEntry($form, $submissionData);

    // if ($validation != '') {
    if (!empty($validation)) {
      if ($ajax) {
        $this->returnJson([
          'passedValidation' => false,
          'validationErrors' => $validation,
          'customErrorMessage' => $customErrorMessage
        ]);
      } else {
        craft()->userSession->setFlash('error', $customErrorMessage);
        $passedValidation = false;
        return craft()->urlManager->setRouteVariables([
          'value' => $submissionData, // Pass filled in data back to form
          'errors' => $validation // Pass validation errors back to form
        ]);
      }
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // PROCESS SUBMISSION ENTRY
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    if (!$submissionErrorMessage && $passedValidation && $spamTimedMethod && $spamHoneypotMethod) {

      $submissionResponseId = craft()->formBuilder2_entry->processSubmissionEntry($submissionEntry);

      if ($submissionResponseId) {
        // Notify Admin of Submission
        if ($notificationSettings['notifySubmission'] == '1') {
          $this->notifyAdminOfSubmission($submissionResponseId, $form);
        }

        // Notify Submitter of Submission
        if ($notificationSettings['notifySubmitter'] == '1') {
          $this->notifySubmitterOfSubmission($submissionResponseId, $form);
        }
        
        // Successful Submission Messages
        if ($ajax) {
          $this->returnJson([
            'success' => true,
            'customSuccessMessage' => $customSuccessMessage
          ]);
        } else {
          craft()->userSession->setFlash('success', $customSuccessMessage);
          if ($formSettings['formRedirect']['customRedirect'] != '') {
            $this->redirect($redirectUrl);
          } else {
            $this->redirectToPostedUrl();
          }
        }
      } else {
        // Submission Error Messages
        if ($ajax) {
          $this->returnJson([
            'error' => true,
            'customErrorMessage' => $customErrorMessage
          ]);
        } else {
          craft()->userSession->setFlash('error', $customErrorMessage);
        return craft()->urlManager->setRouteVariables([
          'value' => $submissionData, // Pass filled in data back to form
          'errors' => $validation // Pass validation errors back to form
        ]);
        }
      }
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      
  }

  /**
   * Delete Submission
   *
   */
  public function actionDeleteSubmission()
  {
    $this->requirePostRequest();
    $entryId = craft()->request->getRequiredPost('entryId');

    if (craft()->elements->deleteElementById($entryId)) {
      craft()->userSession->setNotice(Craft::t('Entry deleted.'));
      $this->redirectToPostedUrl();
      craft()->userSession->setError(Craft::t('Couldn’t delete entry.'));
    }
  }

  /**
   * Notify Admin of Submission
   *
   */
  protected function notifySubmitterOfSubmission($submissionResponseId, $form)
  { 
    $submission       = craft()->formBuilder2_entry->getSubmissionById($submissionResponseId);
    $files            = [];
    $postUploads      = $submission->files;
    $postData         = $submission->submission;
    $postData         = $this->filterSubmissionKeys($postData);

    $attributes             = $form->getAttributes();
    $notificationSettings   = $attributes['notificationSettings'];
    $emailField             = $notificationSettings['submitterEmail'];

    // Template Variables
    $variables['form']      = $form;
    $variables['data']      = $postData;

    // Template
    craft()->path->setTemplatesPath(craft()->path->getPluginsPath());
    $message  = craft()->templates->render('formbuilder2/templates/email/text-submitter', $variables);

    // Email
    $toEmail = $postData[$emailField];

    if (craft()->formBuilder2_entry->sendEmailNotificationToSubmitter($form, $message, true, $toEmail)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Notify Admin of Submission
   *
   */
  protected function notifyAdminOfSubmission($submissionResponseId, $form)
  {  
    $submission       = craft()->formBuilder2_entry->getSubmissionById($submissionResponseId);
    $files            = [];
    $postUploads      = $submission->files;
    $postData         = $submission->submission;
    $postData         = $this->filterSubmissionKeys($postData);

    craft()->path->setTemplatesPath(craft()->path->getPluginsPath());
    $templatePath = craft()->path->getPluginsPath() . 'plugins/formbuilder2/templates/email/';
    $customTemplatePath = craft()->path->getPluginsPath() . 'formbuilder2/templates/custom/email/';
    $extension = '.twig';

    // Uploaded Files
    if ($postUploads) {
      foreach ($postUploads as $key => $id) {
        $criteria         = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->id     = $id;
        $criteria->limit  = 1;
        $files[]          = $criteria->find();
      }
    }

    $attributes             = $form->getAttributes();
    $formSettings           = $attributes['formSettings'];
    $notificationSettings   = $attributes['notificationSettings'];
    $templateSettings       = $notificationSettings['templateSettings'];

    // Get Logo
    if ($notificationSettings['templateSettings']['emailCustomLogo'] != '') {
      $criteria         = craft()->elements->getCriteria(ElementType::Asset);
      $criteria->id     = $notificationSettings['templateSettings']['emailCustomLogo'];
      $criteria->limit  = 1;
      $customLogo       = $criteria->find();
    } else {
      $customLogo = '';
    }

    $variables['form']                  = $form;
    $variables['files']                 = $files;
    $variables['formSettings']          = $formSettings;
    $variables['emailSettings']         = $notificationSettings['emailSettings'];
    $variables['templateSettings']      = $notificationSettings['templateSettings'];
    if ($notificationSettings['templateSettings']['emailCustomLogo'] != '') {
      $variables['customLogo']          = $customLogo;
    }

    if ($notificationSettings['emailSettings']['sendSubmissionData'] == '1') {
      $variables['data']                = $postData;
    }

    $customSubject = '';
    if ($notificationSettings['customSubject'] == '1') {
      $customSubjectField = $notificationSettings['customSubjectLine'];
      $customSubject = $postData[$customSubjectField];
    }

    if ($templateSettings['emailTemplateStyle'] == 'html') {
      if (IOHelper::fileExists($customTemplatePath . 'html' . $extension)) {
        $message  = craft()->templates->render('formbuilder2/templates/custom/email/html', $variables);
      } else {
        $message  = craft()->templates->render('formbuilder2/templates/email/html', $variables);
      }
    } else {
      if (IOHelper::fileExists($customTemplatePath . 'text' . $extension)) {
        $message  = craft()->templates->render('formbuilder2/templates/custom/email/text', $variables);
      } else {
        $message  = craft()->templates->render('formbuilder2/templates/email/text', $variables);
      }
    }

    if (craft()->formBuilder2_entry->sendEmailNotification($form, $postUploads, $customSubject, $message, true, null)) {
      return true;
    } else {
      return false;
    }
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
      'formHandle',
      'spamTimeMethod',
      'email-address-new',
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
  
  /**
   * Sidebar Navigation
   *
   */
  public function navigation()
  {
    $navigationSections = [
      [
        'heading' => Craft::t('Menu'),
        'nav'     => [
          [
            'label' => Craft::t('Dashboard'),
            'icon'  => 'tachometer',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2'),
          ],
          [
            'label' => Craft::t('Forms'),
            'icon'  => 'list-alt',
            'extra' => craft()->formBuilder2_form->getTotalForms(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms'),
          ],
          [
            'label' => Craft::t('Entries'),
            'icon'  => 'file-text-o',
            'extra' => craft()->formBuilder2_entry->getTotalEntries(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/entries'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Quick Links'),
        'nav'     => [
          [
            'label' => Craft::t('Create New Form'),
            'icon'  => 'pencil-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Tools'),
        'nav'     => [
          [
            'label' => Craft::t('Export'),
            'icon'  => 'share-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/export'),
          ],
          [
            'label' => Craft::t('Configuration'),
            'icon'  => 'sliders',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/configuration'),
          ],
        ]
      ],
    ];
    return $navigationSections;
  }

}
