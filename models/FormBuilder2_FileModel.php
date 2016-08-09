<?php
namespace Craft;

class FormBuilder2_FileModel extends BaseElementModel
{
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
      'fileName'          => AttributeType::Name,
      'fileOriginalName'  => AttributeType::String,
      'fileNameCleaned'   => AttributeType::String,
    	'fileExtension'     => AttributeType::String,
      'filePath'          => AttributeType::String,
    	'fileContents'      => AttributeType::Mixed
    ));
  }
}