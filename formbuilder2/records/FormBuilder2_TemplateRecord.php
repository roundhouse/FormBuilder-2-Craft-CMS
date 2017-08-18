<?php
namespace Craft;

class FormBuilder2_TemplateRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'formbuilder2_templates';
    }

    protected function defineAttributes()
    {
        return array(
            'name'                  => array(AttributeType::Name, 'required' => true),
            'handle'                => array(AttributeType::Handle, 'required' => true),
            'type'                  => array(AttributeType::String, 'required' => true),
            'sortOrder'             => AttributeType::SortOrder,
            'content'               => AttributeType::Mixed,
            'styles'                => AttributeType::Mixed,
            'settings'              => AttributeType::Mixed
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
