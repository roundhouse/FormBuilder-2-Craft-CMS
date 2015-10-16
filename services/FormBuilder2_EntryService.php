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

}
