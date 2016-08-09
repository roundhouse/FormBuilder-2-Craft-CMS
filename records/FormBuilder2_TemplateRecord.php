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
            'name'              => array(AttributeType::Name, 'required' => true),
            'handle'            => array(AttributeType::Handle, 'required' => true),
            'templateFile'      => AttributeType::Mixed,
            'templateContent'   => AttributeType::Mixed,
            'templateStyles'    => AttributeType::Mixed,
            'templateSettings'  => AttributeType::Mixed,
        );
    }
}
