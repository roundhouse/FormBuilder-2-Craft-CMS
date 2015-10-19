<?php
namespace Craft;


class FormBuilder2_EntryService extends BaseApplicationComponent
{
  
  private $_entriesById;
  private $_allEntryIds;
  private $_fetchedAllEntries = false;

  /**
   * Get All Entry ID's
   *
   */
  public function getAllEntryIds()
  {
    if (!isset($this->_allEntryIds)) {
      if ($this->_fetchedAllEntries) {
        $this->_allEntryIds = array_keys($this->_entriesById);
      } else {
        $this->_allEntryIds = craft()->db->createCommand()
          ->select('id')
          ->from('formbuilder2_entries')
          ->queryColumn();
      }
    }
    return $this->_allEntryIds;
  }

  /**
   * Get All Entries
   *
   */
  public function getAllEntries()
  {
    $entries = FormBuilder2_EntryRecord::model()->findAll();
    return $entries;
  }

  /**
   * Get Total Entries Count
   *
   */
  public function getTotalEntries()
  {
    return count($this->getAllEntryIds());
  }

  /**
   * Get Form By Handle
   *
   */
  public function getFormByHandle($handle)
  {
    $formRecord = FormBuilder2_FormRecord::model()->findByAttributes(array(
      'handle' => $handle,
    ));

    if (!$formRecord) { return false; }
    return FormBuilder2_FormModel::populateModel($formRecord);
  }

  /**
   * Validate values of a submitted form
   *
   */
  public function validateEntry($form, $submissionData){
    $fieldLayoutFields = $form->getFieldLayout()->getFields();
    
    $errorMessage = [];

    foreach ($fieldLayoutFields as $key => $fieldLayoutField) {
      $requiredField = $fieldLayoutField->attributes['required'];
      $fieldId = $fieldLayoutField->fieldId;
      $field = craft()->fields->getFieldById($fieldId);

      $userValue = (array_key_exists($field->handle, $submissionData)) ? $submissionData[$field->handle] : false;          

      if ($requiredField == 1) {
        $field->required = true;
      }
      
      $_processError = function($field, $message) {
        craft()->userSession->setFlash('error', $message);
        return $message;
      };

      switch ($field->type) {
        case "PlainText":
          if ($field->required) {
            $text = craft()->request->getPost($field->handle);
            if ($text == '') {
              $errorMessage[] = $field->name . ' cannot be blank.';
            }
          }
        break;
        case "RichField":
          if ($field->required) {
            $richField = craft()->request->getPost($field->handle);
            if ($richField == '') {
              $errorMessage[] = $field->name . ' cannot be blank.';
            }
          }
        break;
        case "Number":
          $number = craft()->request->getPost($field->handle);
          if ($field->required) {
            if (!ctype_digit($number)) {
              $errorMessage[] = $field->name . ' cannot be blank and needs to contain only numbers.';
            }
          } else {
            if (!ctype_digit($number) && (!empty($number))) {
              $errorMessage[] = $field->name . ' needs to contain only numbers.';
            }
          }
        break;
        case "MultiSelect":
          $multiselect = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($multiselect == '') {
              $errorMessage[] = $field->name . ' please select at least one.';
            }
          }
        break;
        case "RadioButtons":
          $radiobuttons = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($radiobuttons == '') {
              $errorMessage[] = $field->name . ' please select one.';
            }
          }
        break;
        case "Dropdown":
          $dropdown = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($dropdown == '') {
              $errorMessage[] = $field->name . ' please select one.';
            }
          }
        break;
        case "Checkboxes":
          $checkbox = craft()->request->getPost($field->handle);
          if ($field->required) {
            if (count($checkbox) == 1) {
              $errorMessage[] = $field->name . ' please select at least one.';
            }
          }
        break;
      }
    }
    return $errorMessage;
  }

}
