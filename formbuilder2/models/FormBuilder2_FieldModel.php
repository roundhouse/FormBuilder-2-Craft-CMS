<?php
namespace Craft;

class FormBuilder2_FieldModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
      'fieldId'       => AttributeType::Number,
      'fieldLayoutId' => AttributeType::Number,
      'template'      => AttributeType::String,
    ));
  }
}