<?php
namespace Craft;

class FormBuilder2_TemplateModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
    	'id'               => AttributeType::Number,
    	'name'             => AttributeType::Name,
    	'handle'           => array(AttributeType::Handle, 'required' => true),
      'templateFile'     => AttributeType::Mixed,
      'templateContent'  => AttributeType::Mixed,
      'templateStyles'   => AttributeType::Mixed,
    	'templateSettings' => AttributeType::Mixed,
    ));
  }
}