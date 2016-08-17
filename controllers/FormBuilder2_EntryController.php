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

    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();

    $variables['settings']    = $settings;
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

            $uploadedFiles = UploadedFile::getInstancesByName($field->handle);
            $allowedKinds = [];
            if ($field->settings['restrictFiles']) {
              $allowedKinds = $field->settings['allowedKinds'];
            }

            foreach ($uploadedFiles as $file) {
              $fileKind = IOHelper::getFileKind(IOHelper::getExtension($file->getName()));
              if (in_array($fileKind, $allowedKinds)) {
                $files[] = array(
                  'folderId' => $field->settings['singleUploadLocationSource'][0],
                  'sourceId' => $field->settings['singleUploadLocationSource'][0],
                  'filename' => $file->getName(),
                  'location' => $file->getTempName(),
                  'type'     => $file->getType(),
                  'kind'     => $fileKind
                );
              } else {
                $submissionErrorMessage[] = Craft::t('File type is not allowed!');
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
    $validation = craft()->formBuilder2_entry->validateEntry($form, $submissionData, $files);

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

      // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      // FILE UPLOADS
      // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $fileIds = [];
      $fileCollection = [];
      $tempPath = [];
      if ($files) {
        foreach ($files as $key => $file) {
          $tempPath = AssetsHelper::getTempFilePath($file['filename']);
          move_uploaded_file($file['location'], $tempPath);
          $response = craft()->assets->insertFileByLocalPath($tempPath, $file['filename'], $file['folderId'], AssetConflictResolution::KeepBoth);
          $fileIds[] = $response->getDataItem('fileId');
          $fileCollection[] = [
            'tempPath' => $tempPath,
            'filename' => $file['filename'],
            'type'     => $file['type']
          ];
        }
        $submissionEntry->files = $fileIds;
      }

      $submissionResponseId = craft()->formBuilder2_entry->processSubmissionEntry($submissionEntry);

      if ($submissionResponseId) {
        // Notify Admin of Submission
        if (isset($notificationSettings['notifySubmission'])) {
          if ($notificationSettings['notifySubmission'] == '1') {
            $this->notifyAdminOfSubmission($submissionResponseId, $fileCollection, $form);
          }
        }

        // Notify Submitter of Submission
        if (isset($notificationSettings['notifySubmitter'])) {
          if ($notificationSettings['notifySubmitter'] == '1') {
            $this->notifySubmitterOfSubmission($submissionResponseId, $form);
          }
        }

        foreach ($fileCollection as $file) {
          IOHelper::deleteFile($file['tempPath'], true);
        }

        // Successful Submission Messages
        if ($ajax) {
          $this->returnJson([
            'success' => true,
            'customSuccessMessage' => $customSuccessMessage
          ]);
        } else {
          craft()->userSession->setFlash('success', $customSuccessMessage);
          $cookie = new HttpCookie('formBuilder2SubmissionId', $submissionEntry->attributes['id']);
          craft()->request->getCookies()->add($cookie->name, $cookie);
          $this->redirectToPostedUrl();
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
    $formSettings           = $attributes['formSettings'];
    $notificationSettings   = $attributes['notificationSettings'];

    $variables['form']                  = $form;
    $variables['files']                 = $files;
    $variables['formSettings']          = $formSettings;
    $variables['emailSettings']         = $notificationSettings['emailSettings'];
    $variables['notificationSettings']  = $notificationSettings;
    $variables['templateSettings']      = $notificationSettings['emailTemplate'];
    $variables['sendSubmission']        = $notificationSettings['emailSettings']['sendSubmitterSubmissionData'];
    $emailField                         = $notificationSettings['submitterEmail'];
    $variables['data']                  = $postData;

    if ($notificationSettings['emailTemplate'] && $notificationSettings['emailTemplate'] != '') {
      $template = craft()->formBuilder2_template->getTemplateByHandle($notificationSettings['emailTemplate']);
      $variables['template'] = $template;
    }

    $oldPath = craft()->templates->getTemplatesPath();
    craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
    $message  = craft()->templates->render('formbuilder2/templates/email/layouts/html', $variables);
    craft()->templates->setTemplatesPath($oldPath);

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
  protected function notifyAdminOfSubmission($submissionResponseId, $fileCollection, $form)
  {  
    $submission       = craft()->formBuilder2_entry->getSubmissionById($submissionResponseId);
    $files            = '';
    $postUploads      = $submission->files;
    $postData         = $submission->submission;
    $postData         = $this->filterSubmissionKeys($postData);

    // Uploaded Files
    if ($postUploads) {
      foreach ($postUploads as $key => $id) {
        $criteria         = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->id     = $id;
        $criteria->limit  = 1;
        $files          = $criteria->find();
      }
    }

    $attributes             = $form->getAttributes();
    $formSettings           = $attributes['formSettings'];
    $notificationSettings   = $attributes['notificationSettings'];

    $variables['form']                  = $form;
    $variables['files']                 = $files;
    $variables['formSettings']          = $formSettings;
    $variables['emailSettings']         = $notificationSettings['emailSettings'];
    $variables['notificationSettings']  = $notificationSettings;
    $variables['templateSettings']      = $notificationSettings['emailTemplate'];
    $variables['sendSubmission']        = $notificationSettings['emailSettings']['sendSubmissionData'];
    $variables['data'] = $postData;
    
    if ($notificationSettings['emailTemplate'] && $notificationSettings['emailTemplate'] != '') {
      $template = craft()->formBuilder2_template->getTemplateByHandle($notificationSettings['emailTemplate']);
      $variables['template'] = $template;
    }

    $customSubject = '';
    if (isset($notificationSettings['customSubject'])) {
      if ($notificationSettings['customSubject'] == '1') {
        $customSubjectField = $notificationSettings['customSubjectLine'];
        $customSubject = $postData[$customSubjectField];
      }
    }

    $oldPath = craft()->templates->getTemplatesPath();
    craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
    $message  = craft()->templates->render('formbuilder2/templates/email/layouts/html', $variables);
    craft()->templates->setTemplatesPath($oldPath);

    // Custom Emails
    $customEmail = '';
    if ($notificationSettings['customEmailField']) {
      $customEmail = $postData[$notificationSettings['customEmailField']];
    }

    if (craft()->formBuilder2_entry->sendEmailNotification($form, $fileCollection, $postData, $customEmail, $customSubject, $message, true, null)) {
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
      'redirect',
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
