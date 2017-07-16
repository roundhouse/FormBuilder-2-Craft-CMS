<?php
namespace Craft;

class FormBuilder2_TemplateModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
    	'id'                  => AttributeType::Number,
    	'name'                => AttributeType::Name,
    	'handle'              => array(AttributeType::Handle, 'required' => true),
      'layoutId'            => AttributeType::Number,
      'bodyText'            => AttributeType::String,
      'footerText'          => AttributeType::String,
      'altText'             => AttributeType::String,
      'templateContent'     => AttributeType::Mixed,
      'templateStyles'      => AttributeType::Mixed,
      'templateSettings'    => AttributeType::Mixed,
    ));
  }
}