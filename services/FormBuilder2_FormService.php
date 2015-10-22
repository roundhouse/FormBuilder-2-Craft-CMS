<?php
namespace Craft;


class FormBuilder2_FormService extends BaseApplicationComponent
{
  
  private $_formsById;
  private $_allFormIds;
  private $_fetchedAllForms = false;

  /**
   * Get All Form ID's
   *
   */
  public function getAllFormIds()
  {
    if (!isset($this->_allFormIds)) {
      if ($this->_fetchedAllForms) {
        $this->_allFormIds = array_keys($this->_formsById);
      } else {
        $this->_allFormIds = craft()->db->createCommand()
          ->select('id')
          ->from('formbuilder2_forms')
          ->queryColumn();
      }
    }
    return $this->_allFormIds;
  }

  /**
   * Get All Form
   *
   */
  public function getAllForms($indexBy = null)
  {
    if (!$this->_fetchedAllForms) {
      $formRecords = FormBuilder2_FormRecord::model()->ordered()->findAll();
      $this->_formsById = FormBuilder2_FormModel::populateModels($formRecords, 'id');
      $this->_fetchedAllForms = true;
    }

    if ($indexBy == 'id') {
      return $this->_formsById;
    } else if (!$indexBy) {
      return array_values($this->_formsById);
    } else {
      $forms = array();
      foreach ($this->_formsById as $form) {
        $forms[$form->$indexBy] = $form;
      }
      return $forms;
    }
  }

  /**
   * Get Form By Handle
   *
   */
  public function getFormByHandle($formHandle)
  {
    $formRecord = FormBuilder2_FormRecord::model()->findByAttributes(array(
      'handle' => $formHandle
    ));

    if ($formRecord) {
      return FormBuilder2_FormModel::populateModel($formRecord);
    }
  }

  /**
   * Get Form by ID
   *
   */
  public function getFormById($formId)
  {
    if (!isset($this->_formsById) || !array_key_exists($formId, $this->_formsById)) {
      $formRecord = FormBuilder2_FormRecord::model()->findById($formId);

      if ($formRecord) {
        $this->_formsById[$formId] = FormBuilder2_FormModel::populateModel($formRecord);
      } else {
        $this->_formsById[$formId] = null;
      }
    }
    return $this->_formsById[$formId];
  }

  /**
   * Get Total Forms Count
   *
   */
  public function getTotalForms()
  {
    return count($this->getAllFormIds());
  }

  /**
   * Save New Form
   *
   */
  public function saveForm(FormBuilder2_FormModel $form)
  {
    if ($form->id) {
      $formRecord = FormBuilder2_FormRecord::model()->findById($form->id);

      if (!$formRecord) {
        throw new Exception(Craft::t('No form exists with the ID “{id}”', array('id' => $form->id)));
      }

      $oldForm = FormBuilder2_FormModel::populateModel($formRecord);
      $isNewForm = false;
    } else {
      $formRecord = new FormBuilder2_FormRecord();
      $isNewForm = true;
    }

    $formRecord->name                         = $form->name;
    $formRecord->handle                       = $form->handle;
    $formRecord->saveSubmissionsToDatabase    = $form->saveSubmissionsToDatabase;
    $formRecord->customRedirect               = $form->customRedirect;
    $formRecord->customRedirectUrl            = $form->customRedirectUrl;
    $formRecord->hasFileUploads               = $form->hasFileUploads;
    $formRecord->ajaxSubmit                   = $form->ajaxSubmit;
    $formRecord->spamTimeMethod               = $form->spamTimeMethod;
    $formRecord->spamTimeMethodTime           = $form->spamTimeMethodTime;
    $formRecord->spamHoneypotMethod           = $form->spamHoneypotMethod;
    $formRecord->spamHoneypotMethodMessage    = $form->spamHoneypotMethodMessage;
    $formRecord->successMessage               = $form->successMessage;
    $formRecord->errorMessage                 = $form->errorMessage;
    $formRecord->notifySubmission             = $form->notifySubmission;
    $formRecord->notifyEmail                  = $form->notifyEmail;
    $formRecord->emailSubject                 = $form->emailSubject;
    $formRecord->fieldLayoutId                = $form->fieldLayoutId;


    if ($form->customRedirect && $form->customRedirectUrl == '') {
      $form->addError('customRedirectUrl', Craft::t('Please enter Redirect URL.'));
    }

    if ($form->spamTimeMethod && $form->spamTimeMethodTime == '') {
      $form->addError('spamTimeMethodTime', Craft::t('Please enter time.'));
    }

    if ($form->spamHoneypotMethod && $form->spamHoneypotMethodMessage == '') {
      $form->addError('spamHoneypotMethodMessage', Craft::t('Please enter message for screen readers.'));
    }

    if ($form->notifySubmission && $form->notifyEmail == '') {
      $form->addError('notifyEmail', Craft::t('Please enter notification email.'));
    }

    if ($form->notifySubmission && $form->emailSubject == '') {
      $form->addError('emailSubject', Craft::t('Please enter notification email subject.'));
    }
    
    $formRecord->validate();
    $form->addErrors($formRecord->getErrors());

    if (!$form->hasErrors()) {
      $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
      try {
        if (!$isNewForm && $oldForm->fieldLayoutId) {
          craft()->fields->deleteLayoutById($oldForm->fieldLayoutId);
        }

        $fieldLayout = $form->getFieldLayout();
        craft()->fields->saveLayout($fieldLayout);

        $form->fieldLayoutId = $fieldLayout->id;
        $formRecord->fieldLayoutId = $fieldLayout->id;

        $formRecord->save();

        if (!$form->id) { $form->id = $formRecord->id; }

        $this->_formsById[$form->id] = $form;

        if ($transaction !== null) { $transaction->commit(); }
      } catch (\Exception $e) {
        if ($transaction !== null) { $transaction->rollback(); }
        throw $e;
      }
      return true;
    } else { 
      return false; 
    }
  }


  /**
   * Delete Form
   *
   */
  public function deleteFormById($formId)
  { 
    if (!$formId) { return false; }

    $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
    try {
      $fieldLayoutId = craft()->db->createCommand()
        ->select('fieldLayoutId')
        ->from('formbuilder2_forms')
        ->where(array('id' => $formId))
        ->queryScalar();

      if ($fieldLayoutId) {
        craft()->fields->deleteLayoutById($fieldLayoutId);
      }

      $entryIds = craft()->db->createCommand()
        ->select('id')
        ->from('formbuilder2_entries')
        ->where(array('formId' => $formId))
        ->queryColumn();

      craft()->elements->deleteElementById($entryIds);
      $affectedRows = craft()->db->createCommand()->delete('formbuilder2_forms', array('id' => $formId));

      if ($transaction !== null) { $transaction->commit(); }
      return (bool) $affectedRows;
    } catch (\Exception $e) {
      if ($transaction !== null) { $transaction->rollback(); }
      throw $e;
    }
  }

}
