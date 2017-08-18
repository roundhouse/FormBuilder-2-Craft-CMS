<?php
namespace Craft;

class FormBuilder2_TemplateModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
    	'id'       => AttributeType::Number,
    	'name'     => AttributeType::Name,
        'handle'   => array(AttributeType::Handle, 'required' => true),
    	'type'     => array(AttributeType::String, 'required' => true),
        'content'  => AttributeType::Mixed,
        'styles'   => AttributeType::Mixed,
        'settings' => AttributeType::Mixed
    ));
  }
}