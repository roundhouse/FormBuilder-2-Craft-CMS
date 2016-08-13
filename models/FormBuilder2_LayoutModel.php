<?php
namespace Craft;

class FormBuilder2_LayoutModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
    	'id'            => AttributeType::Number,
    	'name'          => array(AttributeType::Name, 'required' => true),
    	'handle'        => array(AttributeType::Handle, 'required' => true),
      'description'   => AttributeType::String,
      'type'          => AttributeType::String,
      'content'       => AttributeType::Mixed,
      'icon'          => AttributeType::Mixed,
      'screenshot'    => AttributeType::Mixed
    ));
  }
}