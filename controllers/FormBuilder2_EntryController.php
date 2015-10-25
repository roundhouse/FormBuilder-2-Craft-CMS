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
      'settings'  => $settings,
      'title'     => 'FormBuilder2'
    ));
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

    $this->renderTemplate('formbuilder2/entries/_view', $variables);
  }

  /**
   * Submit Entry
   *
   */
  public function actionSubmitEntry()
  {
    $form = craft()->formBuilder2_entry->getFormByHandle(craft()->request->getPost('formHandle'));
    
    // Set Up Form Submission
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
    $files                      = '';
    $errorMessage               = [];

    // Prepare submissionEntry for processing
    $submissionEntry = new FormBuilder2_EntryModel();
    // Using Ajax
    if ($useAjax) {
      $this->requireAjaxRequest();
    } else {
      $this->requirePostRequest();
    }

    // Custom Redirect
    if ($customRedirect) {
      $redirectUrl = $form->customRedirectUrl;
    }

    // Spam Protection | Timed Method
    if ($spamTimeSubmissions) {
      $formSubmissionTime = (int)craft()->request->getPost('spamTimeMethod');
      $submissionDuration = time() - $formSubmissionTime;
      $allowedTime = (int)$form->spamTimeMethodTime;
      if ($submissionDuration < $allowedTime) {
        $spamMethodOne = false;
        $errorMessage[] = Craft::t('You submitted too fast, you are robot!');
        // $form->addError('spamTimeSubmissions', Craft::t('You submitted too fast, you are robot!'));
      } else {
        $spamMethodOne = true;
      }
    } else {
      $spamMethodOne = true;
    }

    // Spam Protection | Honeypot Method
    if ($spamHoneypotSubmissions) {
      $honeypotField = craft()->request->getPost('email-address-new');
      if ($honeypotField != '') {
        $spamMethodTwo = false;
        $errorMessage[] = Craft::t('You tried the honey, you are robot bear!');
      } else {
        $spamMethodTwo = true;
      }
    } else {
      $spamMethodTwo = true;
    }

    // Validate Required Fields
    $validateRequired = craft()->formBuilder2_entry->validateEntry($form, $submissionData);
    
    // File Uploads
    if ($hasFileUploads) {
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


    $submissionEntry->formId        = $form->id;
    $submissionEntry->title         = $form->name;
    $submissionEntry->files         = $files;
    $submissionEntry->submission    = $submissionData;

    // Process Errors
    if ($errorMessage) {
      craft()->urlManager->setRouteVariables(array(
        'errors' => $errorMessage
      ));
    }

    // Process Submission Entry
    if (!$errorMessage && $spamMethodOne && $spamMethodTwo && $validateRequired && craft()->formBuilder2_entry->processSubmissionEntry($submissionEntry)) {

      // Notify Admin of Submission
      if ($notifyAdminOfSubmission) {
        $this->notifyAdminOfSubmission($submissionEntry, $form);
      }

      // Messages
      if ($useAjax) {
        $this->returnJson(
          ['success' => true, 'message' => $form->successMessage, 'form' => $form]
        );
      } else {
        craft()->userSession->setFlash('success', $form->successMessage);
        if ($customRedirect) {
          $this->redirect($redirectUrl);
        } else {
          $this->redirectToPostedUrl();
        }
      }

    } else {
      if (!$saveSubmissionsToDatabase && !$notifyAdminOfSubmission) {
        craft()->userSession->setFlash('error', Craft::t('Update form settings to save to database or notify form admin. If form submits nothing will happen.'));
      }
      if ($useAjax) {
        $this->returnJson(
          ['error' => true, 'message' => $form->errorMessage, 'form' => $form]
        );
      } else {
        craft()->userSession->setFlash('error', $form->errorMessage);
      }
    }
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
  protected function notifyAdminOfSubmission($submission, $form)
  {  
    // $data         = new \stdClass($data);
    $postUploads  = $submission->files;
    $postData     = $submission->submission;
    $postData     = $this->filterSubmissionKeys($postData);
    
    craft()->path->setTemplatesPath(craft()->path->getPluginsPath());
    $templatePath = craft()->path->getPluginsPath() . 'plugins/formbuilder2/templates/email/';
    $customTemplatePath = craft()->path->getPluginsPath() . 'formbuilder2/templates/custom/email/';
    $extension = '.twig';

    // Get Plugin
    $plugin = craft()->plugins->getPlugin('FormBuilder2');
    $settings = $plugin->getSettings();

    // Get Logo
    if ($settings['emailNotificationLogo']) {
      $criteria         = craft()->elements->getCriteria(ElementType::Asset);
      $criteria->id     = $settings['emailNotificationLogo'];
      $criteria->limit  = 1;
      $customLogo       = $criteria->find();
    } else {
      $customLogo = '';
    }

    $variables = array(
      'submission'  => $postData,
      'files'       => $postUploads,
      'form'        => $form,
      'entry'       => $submission,
      'settings'    => $settings,
      'customLogo'  => $customLogo
    );

    if (IOHelper::fileExists($customTemplatePath . 'default' . $extension)) {
      $message  = craft()->templates->render('formbuilder2/templates/custom/email/default', $variables);
    } else {
      $message  = craft()->templates->render('formbuilder2/templates/email/default', $variables);
    }

    if (craft()->formBuilder2_entry->sendEmailNotification($form, $postUploads, $message, true, null)) {
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
  

}
