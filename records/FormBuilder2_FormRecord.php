<?php
namespace Craft;

class FormBuilder2_FormRecord extends BaseRecord
{
  //======================================================================
  // Get Table Name
  //======================================================================
  public function getTableName()
  {
    return 'formbuilder2_forms';
  }

  //======================================================================
  // Define Attributes
  //======================================================================
  protected function defineAttributes()
  {
    return array(
      'name'                                => array(AttributeType::Name, 'required' => true),
      'handle'                              => array(AttributeType::Handle, 'required' => true),
      'emailSubject'                        => AttributeType::Name,
      // 'ajaxSubmit'                          => AttributeType::Bool,
      // 'successPageRedirect'                 => AttributeType::Bool,
      // 'redirectUrl'                         => AttributeType::String,
      // 'useReCaptcha'                        => AttributeType::Bool,
      // 'hasFileUploads'                      => AttributeType::Bool,
      // 'uploadSource'                        => AttributeType::String,
      'successMessage'                      => AttributeType::String,
      'errorMessage'                        => AttributeType::String,
      // 'toEmail'                             => AttributeType::Name,
      // 'notifyFormAdmin'                     => AttributeType::Bool,
      // 'notificationTemplatePath'            => AttributeType::String,
      // 'notifyRegistrant'                    => AttributeType::Bool,
      // 'notificationFieldHandleName'         => AttributeType::String,
      // 'notificationTemplatePathRegistrant'  => AttributeType::String,
      // 'fieldLayoutId'                       => AttributeType::Number
    );
  }

  //======================================================================
  // Define Relationships
  //======================================================================
  public function defineRelations()
  {
    return array(
      'fieldLayout'   => array(static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::SET_NULL),
      'entries'       => array(static::HAS_MANY, 'FormBuilder2_EntryRecord', 'entrieId'),
    );
  }

  //======================================================================
  // Define Indexes
  //======================================================================
  public function defineIndexes()
  {
    return array(
      array('columns' => array('id'), 'unique' => true),
      array('columns' => array('name'), 'unique' => true),
      array('columns' => array('handle'), 'unique' => true),
    );
  }

  //======================================================================
  // Scopes
  //======================================================================
  public function scopes()
  {
    return array(
      'ordered' => array('order' => 'id'),
    );
  }
}