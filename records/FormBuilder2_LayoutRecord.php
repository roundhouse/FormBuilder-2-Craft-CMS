<?php
namespace Craft;

class FormBuilder2_LayoutRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'formbuilder2_layouts';
    }

    protected function defineAttributes()
    {
        return array(
            'name'                  => array(AttributeType::Name, 'required' => true),
            'handle'                => array(AttributeType::Handle, 'required' => true),
            'description'           => AttributeType::String,
            'type'                  => AttributeType::String,
            'icon'                  => AttributeType::Mixed,
            'templateName'          => AttributeType::String,
            'templateOriginalName'  => AttributeType::String,
            'templatePath'          => array(AttributeType::String, 'required' => true),
            'screenshot'            => AttributeType::Mixed,
        );
    }

    public function defineIndexes()
    {
      return array(
        array('columns' => array('id'), 'unique' => true),
        array('columns' => array('handle'), 'unique' => true)
      );
    }

    public function scopes()
    {
      return array(
        'ordered' => array('order' => 'id')
      );
    }
}
