<?php
namespace Craft;

class FormBuilder2_EntryRecord extends BaseRecord
{
  /**
   * Get Table Name
   *
   */
  public function getTableName()
  {
    return 'formbuilder2_entries';
  }

  /**
   * Define Attributes
   *
   */
  public function defineAttributes()
  {
    return array(
      'formId'      => AttributeType::Number,
      'title'       => AttributeType::String,
      'files'       => AttributeType::Mixed,
      'submission'  => AttributeType::Mixed,
    );
  }

  /**
   * Define Relationships
   *
   */
  public function defineRelations()
  {
    return array(
      'files'     => array(static::HAS_MANY, 'AssetFileRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
      'element'   => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
      'form'      => array(static::BELONGS_TO, 'FormBuilder2_FormRecord', 'required' => true, 'onDelete' => static::CASCADE),
    );
  }
}