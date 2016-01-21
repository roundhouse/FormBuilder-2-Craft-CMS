<?php
namespace Craft;

class FormBuilder2_EntryModel extends BaseElementModel
{

  protected $elementType = 'FormBuilder2';

  function __toString()
  {
    return $this->id;
  }

  /**
   * Define Attributes
   *
   */
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
      'id'              => AttributeType::Number,
      'formId'          => AttributeType::Number,
      'title'           => AttributeType::String,
      'files'           => AttributeType::String,
      'submission'      => AttributeType::String
    ));
  }

  /**
   * Define if editable
   *
   */
  public function isEditable()
  {
    return true;
  }

  /**
   * Get Control Panel Edit Url
   *
   */
  public function getCpEditUrl()
  {
    return UrlHelper::getCpUrl('formbuilder2/entries/'.$this->id.'/edit');
  }

  /**
   * Normalize Files For Elements Table
   *
   */
  public function normalizeFilesForElementsTable()
  {
    $entry = craft()->formBuilder2_entry->getSubmissionById($this->id);
    $files = count($entry->files);

    if ($files == 0) {
      $files = 'No Uploads';
    } elseif ($files == 1) {
      $files = '<span class="upload-count">'.$files.'</span> File Uploaded';
    } else {
      $files = '<span class="upload-count">'.$files.'</span> Files Uploaded';
    }

    $this->__set('files', $files);
    return $this;
  }

  /**
   * View Submission Link in Elements Table
   *
   */
  public function viewEntryLinkOnElementsTable()
  {
    $entry = craft()->formBuilder2_entry->getSubmissionById($this->id);
    // url('formbuilder2/forms/' ~ form.id ~ '/edit')
    $url = UrlHelper::getUrl('formbuilder2/entries/' .$this->id. '/edit');
    $link = '<a href="'.$url.'" class="view-submission">'.Craft::t('View Submission').'</a>';

    $this->__set('submission', $link);
    return $this;
  }

  /**
   * Normalize Submission For Elements Table
   *
   */
  public function normalizeDataForElementsTable()
  {
    $data = json_decode($this->submission, true);

    // Pop off the first (4) items from the data array
    $data = array_slice($data, 0, 4);

    $newData = '<ul>';

    foreach ($data as $key => $value) { 

      $fieldHandle = craft()->fields->getFieldByHandle($key);

      $capitalize = ucfirst($key);
      $removeUnderscore = str_replace('_', ' ', $key);
      $valueArray = is_array($value);

      if ($valueArray == '1') {
        $newData .= '<li class="left icon" style="margin-right:10px;"><strong>' . $fieldHandle . '</strong>: ';
        foreach ($value as $item) {
          if ($item != '') {
            $newData .= $item;
            if (next($value)==true) $newData .= ', ';
          }
        }
      } else {
        if ($value != ''){
          $newData .= '<li class="left icon" style="margin-right:10px;"><strong>' . $fieldHandle . '</strong>: ' . $value . '</li>';
        }
      }
    }

    $newData .= '</ul>';
    $this->__set('submission', $newData);
    return $this;
  }

}