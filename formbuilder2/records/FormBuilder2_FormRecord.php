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
      'name'            => array(AttributeType::Name, 'required' => true),
      'handle'          => array(AttributeType::Handle, 'required' => true),
      'fieldLayoutId'   => AttributeType::Number,
      'sortOrder'       => AttributeType::SortOrder,
      'options'         => AttributeType::Mixed,
      'spam'            => AttributeType::Mixed,
      'messages'        => AttributeType::Mixed,
      'notify'          => AttributeType::Mixed,
      'settings'        => AttributeType::Mixed
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