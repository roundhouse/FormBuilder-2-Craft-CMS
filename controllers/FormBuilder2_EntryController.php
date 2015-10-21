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
    $entry              = craft()->formBuilder2_entry->getFormEntryById($variables['entryId']);
    $variables['entry'] = $entry;

    if (empty($entry)) { throw new HttpException(404); }

    $variables['title']       = 'FormBuilder2';
    $variables['form']        = craft()->formBuilder2_form->getFormById($entry->formId);
    $variables['data']        = json_decode($entry->data, true);

    $this->renderTemplate('formbuilder2/entries/_view', $variables);
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

    // Custom Redirect
    if ($customRedirect) {
      $redirectUrl = $form->customRedirectUrl;
    }

    // Validate Required Fields
    $validateRequired = craft()->formBuilder2_entry->validateEntry($form, $submissionData);
    
    // Prepare submissionEntry for processing
    $submissionEntry = new FormBuilder2_EntryModel();

    // File Uploads
    if ($hasFileUploads) {
      foreach ($formFields as $key => $value) {
        $field = $value->getField();
        switch ($field->type) {
          case 'Assets':
            foreach ($_FILES as $key => $value) {
              // $fileName = $value['name'];
              // $fileTmpName = $value['tmp_name'];
              // $fileSize = $value['size'];
              // $fileKind = IOHelper::getFileKind(IOHelper::getExtension($value['name']));
              // $submissionData[$key] = uniqid() . '-' . $value['name'];
              // $submissionData[$key] = \CUploadedFile::getInstanceByName($key);
              $fileModel = new AssetFileModel();
              $folderId = $field->settings['singleUploadLocationSource'][0];
              $sourceId = $field->settings['singleUploadLocationSource'][0];
              $fileModel->sourceId = $sourceId;
              $fileModel->folderId = '1';
              $fileModel->filename = IOHelper::getFileName($value['name']);
              $fileModel->kind = IOHelper::getFileKind(IOHelper::getExtension($value['name']));
              // $fileModel->size = filesize($localFilePath);
              // $fileModel->dateModified = IOHelper::getLastTimeModified($localFilePath);

              $submissionData[$key] = $fileModel;
              // $submissionData[$key] = \CUploadedFile::getInstanceByName($key);
            }
          break;
        }
      }
    }
    
    $submissionEntry->formId  = $form->id;
    $submissionEntry->title   = $form->name;
    $submissionEntry->data    = $submissionData;

    // Process Submission Entry
    if ($validateRequired && craft()->formBuilder2_entry->processSubmissionEntry($submissionEntry)) {
      craft()->userSession->setFlash('success', $form->successMessage);

      // if ($hasFileUploads) {
      //   if (move_uploaded_file($file, $uploadDir . $uniqe_filename)) {
      //     IOHelper::deleteFile($file);

      //     $file = $uploadDir . $uniqe_filename;
      //     $fileModel = new AssetFileModel();

      //     $fileModel->sourceId = $form->uploadSource;
      //     $fileModel->folderId = $this->assetFolderId;

      //     $fileModel->filename = IOHelper::getFileName($uniqe_filename);
      //     $fileModel->originalName = IOHelper::getFileName($filename);
      //     $fileModel->kind = IOHelper::getFileKind(IOHelper::getExtension($uniqe_filename));
      //     $fileModel->size = filesize($file);
      //     $fileModel->dateModified = IOHelper::getLastTimeModified($file);

      //     if ($fileModel->kind == 'image') {
      //       list ($width, $height) = ImageHelper::getImageSize($file);
      //       $fileModel->width = $width;
      //       $fileModel->height = $height;
      //     }

      //     craft()->assets->storeFile($fileModel);

      //   } else {
      //     $fileupload = false;
      //   }
      // }






      // Custom Redirect
      if ($customRedirect) {
        $this->redirect($redirectUrl);
      }




    } else {
      if (!$saveSubmissionsToDatabase && !$notifyAdminOfSubmission) {
        craft()->userSession->setFlash('notice', Craft::t('Update form settings to save to database or notify form admin. If form submits nothing will happen.'));
      }
      craft()->userSession->setFlash('error', $form->errorMessage);
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
