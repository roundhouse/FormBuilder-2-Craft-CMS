<?php
namespace Craft;

class FormBuilder2_EntryController extends BaseController
{

    // Properties
    // =========================================================================

    protected $allowAnonymous = array('actionSaveEntry');
    public $form;

    // Public Methods
    // =========================================================================

    /**
    * Entries Index
    *
    */
    public function actionEntriesIndex()
    {
        $formItems = fb()->forms->getAllForms();
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
        $entry  = fb()->entries->getEntryById($variables['entryId']);
        $form   = fb()->forms->getFormById($entry->formId);
        $tabs   = $entry->getFieldLayout()->getTabs();

        if (empty($entry)) { 
            throw new HttpException(404);
        }  

        $variables['entry']         = $entry;
        $variables['form']          = $form;
        $variables['fieldTabs']     = $tabs;
        $variables['title']         = 'Edit Entry';

        // $files = array();
        // $fileIds = array();

        // if ($entry->files) {
        //   $files = array();
        //   foreach ($entry->files as $key => $value) {
        //     $files[] = craft()->assets->getFileById($value);
        //     $fileIds[] = $value;
        //   }
        // }

        // $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
        // // Craft::dd($entry->getAttributes());
        // $variables['settings']    = $settings;
        // $variables['entry']       = $entry;
        // $variables['form']        = fb()->forms->getFormById($entry->formId);
        // $variables['files']       = $files;
        // $variables['fileIds']     = $fileIds;
        // $variables['submission']  = $entry->submission;
        // $variables['navigation']  = $this->navigation();

        $this->renderTemplate('formbuilder2/entries/_view', $variables);
    }

    /**
     * Processes Submissions
     */
    public function actionSaveEntry()
    {
        $this->requirePostRequest();

        $formId = craft()->request->getRequiredPost('formId');
        $this->form = fb()->forms->getFormById($formId);

        // $notify = $this->form->notify;

        $entry = $this->_getEntryModel();

        $this->_populateEntryModel($entry);

        $saveEntry = isset($this->form->settings['database']['enabled']) && $this->form->settings['database']['enabled'] == 'true' ? true : false;

        if ($saveEntry) {
            
            if (fb()->entries->saveEntry($entry)) {
                if (craft()->request->isAjaxRequest()) {
                    $this->returnJson(array(
                        'success' => true
                    ));
                } else {
                    craft()->userSession->setNotice(Craft::t('Entry Saved.'));
                    $this->redirectToPostedUrl($entry);
                }
            } else {
                if (craft()->request->isAjaxRequest()) {
                    $this->returnJson(array(
                        'success' => false,
                        'errors' => $entry->getErrors()
                    ));
                } else {
                    craft()->userSession->setError(Craft::t('Couldn’t save entry.'));
                    craft()->urlManager->setRouteVariables(array(
                        'entry' => $entry
                    ));
                }
            }

        } else {
            // Don't save entry by do process notificaitons if any enabled

            // fb()->notification->sendNotification($this->form, $entry, $_POST);
        }

        // // On Entry Prepare Event
        // Craft::import('plugins.formbuilder2.events.FormBuilder2_OnPrepareEntryModelEvent');
        // $onPrepareEvent = new FormBuilder2_OnPrepareEntryModelEvent($this, array(
        //     'entry' => $entry
        // ));
        // fb()->onPrepareEntryModelEvent($onPrepareEvent);

        // $entry->formId = $this->form->id;


        // Fire Before Save Event
        // Craft::import('plugins.formBuilder2.events.FormBuilder2_OnBeforeSaveEntryEvent');
        // $onBeforeSaveEntry = new FormBuilder2_OnBeforeSaveEntryEvent(
        //     $this, array(
        //         'entry' => $entry
        //     )
        // );
        // fb()->onBeforeSaveEntry($onBeforeSaveEntry);

        // $saved = true;

        // if ($onBeforeSaveEntry->performAction) {

        //     $saved = fb()->entries->saveEntry($entry);

        // } else {

        //     fb()->entries->callOnSaveEntryEvent($entry);

        // }

        // if ($saved) {
        //     if (isset($notify['admin']['enabled']) && $notify['admin']['enabled'] == '1') {
        //         fb()->notification->sendNotification($this->form, $entry, $_POST);
        //     }

        //     if (craft()->request->isAjaxRequest()) {
        //         $this->returnJson(array(
        //             'success' => true
        //         ));
        //     } else {
        //         craft()->userSession->setNotice(Craft::t('Entry Submitted.'));
        //         $this->redirectToPostedUrl($entry);
        //     }
        // } else {
        //     if (craft()->request->isAjaxRequest()) {
        //         $this->returnJson(array(
        //             'errors' => $entry->getErrors(),
        //         ));
        //     } else {
        //         craft()->userSession->setError(Craft::t('Unable to save submission.'));
        //         fb()->forms->currentEntry[$this->form->handle] = $entry;
        //         craft()->urlManager->setRouteVariables(array(
        //             $this->form->handle => $entry
        //         ));
        //     }
        // }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates an EntryModel.
     *
     * @throws Exception
     * @return EntryModel
     */
    private function _getEntryModel()
    {
        $entry = new FormBuilder2_EntryModel();

        return $entry;
    }

    /**
     * Populates an FormBuilder2_EntryModel with post data.
     *
     * @param FormBuilder2_EntryModel $entry
     *
     * @return null
     */
    private function _populateEntryModel(FormBuilder2_EntryModel $entry)
    {
        $entry->formId    = $this->form->id;
        $entry->ipAddress = craft()->request->getUserHostAddress();
        $entry->userAgent = craft()->request->getUserAgent();

        $title = isset($this->form->settings['database']['titleFormat']) && $this->form->settings['database']['titleFormat'] != '' ? $this->form->settings['database']['titleFormat'] : 'Submission - '.DateTimeHelper::currentTimeStamp();
        $_POST['fields']['date'] = DateTimeHelper::currentTimeStamp();

        $entry->getContent()->title = craft()->templates->renderObjectTemplate($title, $_POST['fields']);
        $fieldsLocation = craft()->request->getParam('fieldsLocation', 'fields');
        $entry->setContentFromPost($fieldsLocation);
    }


    public function actionRemoveAssetsFromSubmission()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $entryId = craft()->request->getRequiredPost('entryId');

        if (fb()->entries->removeFilesFromSubmission($entryId)) {
            $this->returnJson(array(
                'success' => true,
                'message' => 'Submission Updated',
            ));
        } else {
            $this->returnJson(array(
                'success' => false,
                'message' => 'Submission Updated Failed',
            ));
        }

    }

    public function actionDownloadFiles()
    {
        $filePath = craft()->request->query['filePath'];
        craft()->request->sendFile(IOHelper::getFileName($filePath), IOHelper::getFileContents($filePath), array('forceDownload' => true));
    }

    public function actionDownloadAllFiles()
    {
        $this->requireAjaxRequest();

        // FormBuilder2Plugin::log("Files: ".$files, LogLevel::Error, true);

        if (ini_get('allow_url_fopen')) {
            $fileIds = craft()->request->getRequiredPost('ids');
            $formId = craft()->request->getRequiredPost('formId');
            $files = array();
            $filePath = '';

            foreach ($fileIds as $id) {
                $files[] = craft()->assets->getFileById($id);
            }
            $zipname = craft()->path->getTempPath().'SubmissionFiles-'.$formId.'.zip';
            $zip = new \ZipArchive();
            $zip->open($zipname, \ZipArchive::CREATE);
            foreach ($files as $file) {
                $zip->addFromString($file->filename, file_get_contents($file->url));
            }
            $filePath = $zip->filename;
            $zip->close();

            // header('Content-type: application/zip');
            // header('Content-disposition: attachment; filename='.$zipname);
            // header('Content-Length: ' . filesize($zipname));
            // readfile($zipname);

            if ($filePath == $zipname) {
                // craft()->request->sendFile(IOHelper::getFileName($filePath), IOHelper::getFileContents($filePath), array('forceDownload' => true));
                $this->returnJson(array(
                    'success' => true,
                    'message' => 'Download Complete.',
                    'filePath' => $filePath
                ));
            }
        } else {
            $this->returnJson(array(
                'success' => false,
                'message' => 'Cannot download all files, `allow_url_fopen` must be enabled.'
            ));
        }


    }

  /**
   * Delete Submission.
   *
   */
  public function actionDeleteSubmissionAjax()
  {
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    $entryId = craft()->request->getRequiredPost('id');

    if (craft()->elements->deleteElementById($entryId)) {
        $this->returnJson(array('success' => true));
        craft()->userSession->setNotice(Craft::t('Submission deleted.'));
    } else {
        $this->returnJson(array('success' => false));
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
        craft()->userSession->setNotice(Craft::t('Submission deleted.'));
        $this->redirectToPostedUrl();
    } else {
        craft()->userSession->setError(Craft::t('Couldn’t delete entry.'));
    }
  }

  /**
   * Notify Admin of Submission
   *
   */
  protected function notifySubmitterOfSubmission($submissionResponseId, $form)
  {
    $submission       = fb()->entries->getSubmissionById($submissionResponseId);
    $files            = array();
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

    if ($notificationSettings['emailTemplateSubmitter'] && $notificationSettings['emailTemplateSubmitter'] != '') {
      $template = fb()->templates->getTemplateByHandle($notificationSettings['emailTemplateSubmitter']);
      $variables['template'] = $template;
    }

    $oldPath = craft()->templates->getTemplatesPath();
    craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
    $message  = craft()->templates->render('formbuilder2/templates/email/layouts/html', $variables);
    craft()->templates->setTemplatesPath($oldPath);

    // Email
    $toEmail = $postData[$emailField];

    if (fb()->entries->sendEmailNotificationToSubmitter($form, $message, true, $toEmail)) {
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
    $submission       = fb()->entries->getSubmissionById($submissionResponseId);
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
    $variables['templateSettings']      = isset($notificationSettings['emailTemplate']) ? $notificationSettings['emailTemplate'] : null;
    $variables['sendSubmission']        = $notificationSettings['emailSettings']['sendSubmissionData'];
    $variables['data'] = $postData;

    if ($notificationSettings['emailTemplate'] && $notificationSettings['emailTemplate'] != '') {
      $template = fb()->templates->getTemplateByHandle($notificationSettings['emailTemplate']);
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

    if (fb()->entries->sendEmailNotification($form, $fileCollection, $postData, $customEmail, $customSubject, $message, true, null)) {
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
    $navigationSections = array(
      array(
        'heading' => Craft::t('Menu'),
        'nav'     => array(
          array(
            'label' => Craft::t('Dashboard'),
            'icon'  => 'tachometer',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2'),
          ),
          array(
            'label' => Craft::t('Forms'),
            'icon'  => 'list-alt',
            'extra' => fb()->forms->getTotalForms(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms'),
          ),
          array(
            'label' => Craft::t('Entries'),
            'icon'  => 'file-text-o',
            'extra' => fb()->entries->getTotalEntries(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/entries'),
          ),
        )
      ),
      array(
        'heading' => Craft::t('Quick Links'),
        'nav'     => array(
          array(
            'label' => Craft::t('Create New Form'),
            'icon'  => 'pencil-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ),
        )
      ),
      array(
        'heading' => Craft::t('Tools'),
        'nav'     => array(
          array(
            'label' => Craft::t('Export'),
            'icon'  => 'share-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/export'),
          ),
          array(
            'label' => Craft::t('Configuration'),
            'icon'  => 'sliders',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/configuration'),
          ),
        )
      ),
    );
    return $navigationSections;
  }

}
