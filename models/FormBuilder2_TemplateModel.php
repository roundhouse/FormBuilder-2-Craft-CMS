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
      'templateLayout'      => AttributeType::Mixed,
      'templateBodyCopy'    => AttributeType::String,
      'templateFooterCopy'  => AttributeType::String,
      'templateContent'     => AttributeType::Mixed,
      'templateStyles'      => AttributeType::Mixed,
      'templateSettings'    => AttributeType::Mixed,
      'templateAltCopy'     => AttributeType::String,
      'templateAltCopy2'    => AttributeType::String,
      'templateAltCopy3'    => AttributeType::String,
      'templateAltCopy4'    => AttributeType::String,
      'templateAltCopy5'    => AttributeType::String,
    ));
  }
}