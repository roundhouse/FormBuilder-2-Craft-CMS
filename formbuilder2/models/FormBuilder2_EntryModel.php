<?php
namespace Craft;

class FormBuilder2_EntryModel extends BaseElementModel
{

    // Properties
    // =========================================================================

    protected $elementType = 'FormBuilder2';

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc BaseElementModel::getFieldLayout()
     *
     * @return FieldLayoutModel|null
     */
    public function getFieldLayout()
    {
        return $this->getForm()->getFieldLayout();
    }

    /**
     * Returns the content title for this entry
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getContent()->title;
    }

    /**
     * Returns the form model associated with this entry
     *
     * @return SproutForms_FormModel
     */
    public function getForm()
    {
        if (!isset($this->form)) {
            $this->form = fb()->forms->getFormById($this->formId);
        }
        return $this->form;
    }

    public function date()
    {
        return DateTimeHelper::currentTimeStamp();
    }

    /**
     * View Submission Link in Elements Table
     *
     */
    public function entryUrl()
    {
        $entry = craft()->formBuilder2_entry->getSubmissionById($this->id);
        $url = UrlHelper::getUrl('formbuilder2/entries/' .$this->id. '/edit');
        $link = '<a href="'.$url.'" class="view-submission">'.Craft::t('View Submission').'</a>';

        $this->__set('submission', $link);
        
        return $this;
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'              => AttributeType::Number,
            'form'            => AttributeType::Mixed,
            'formId'          => AttributeType::Number,
            'title'           => AttributeType::String,
            'files'           => AttributeType::String,
            'submission'      => AttributeType::String,
            'ipAddress'       => AttributeType::String,
            'userAgent'       => AttributeType::Mixed
        ));
    }

  // public function getTitle()
  // {
  //   return $this->getContent()->title;
  // }

  

  /**
   * Define if editable
   *
   */
  // public function isEditable()
  // {
  //   return true;
  // }

  /**
   * Get Control Panel Edit Url
   *
   */
  // public function getCpEditUrl()
  // {
  //   return UrlHelper::getCpUrl('formbuilder2/entries/'.$this->id.'/edit');
  // }

  /**
   * Normalize Files For Elements Table
   *
   */
  // public function normalizeFilesForElementsTable()
  // {
  //   $entry = craft()->formBuilder2_entry->getSubmissionById($this->id);
  //   $files = count($entry->files);

  //   if ($files == 0) {
  //     $files = Craft::t('No Uploads');
  //   } elseif ($files == 1) {
  //     $files = '<span class="upload-count">'.$files.'</span> '.Craft::t('File Uploaded');
  //   } else {
  //     $files = '<span class="upload-count">'.$files.'</span> '.Craft::t('Files Uploaded');
  //   }

  //   $this->__set('files', $files);
  //   return $this;
  // }

  

  /**
   * Normalize Submission For Elements Table
   *
   */
  // public function normalizeDataForElementsTable()
  // {
  //   $data = json_decode($this->submission, true);

  //   // Pop off the first (4) items from the data array
  //   $data = array_slice($data, 0, 4);

  //   $newData = '<ul>';

  //   foreach ($data as $key => $value) { 

  //     $fieldHandle = craft()->fields->getFieldByHandle($key);

  //     $capitalize = ucfirst($key);
  //     $removeUnderscore = str_replace('_', ' ', $key);
  //     $valueArray = is_array($value);

  //     if ($valueArray == '1') {
  //       $newData .= '<li class="left icon" style="margin-right:10px;"><strong>' . $fieldHandle . '</strong>: ';
  //       foreach ($value as $item) {
  //         if ($item != '') {
  //           $newData .= $item;
  //           if (next($value)==true) $newData .= ', ';
  //         }
  //       }
  //     } else {
  //       if ($value != ''){
  //         $newData .= '<li class="left icon" style="margin-right:10px;"><strong>' . $fieldHandle . '</strong>: ' . $value . '</li>';
  //       }
  //     }
  //   }

  //   $newData .= '</ul>';
  //   $this->__set('submission', $newData);
  //   return $this;
  // }

    // public function getContentTable()
    // {
    //     return craft()->content->contentTable;
    // }

    // public function getFieldContext()
    // {
    //     return craft()->content->fieldContext;
    // }

    // public function getContentTable()
    // {
    //     return fb()->forms->getContentTableName($this->getForm());
    // }
}