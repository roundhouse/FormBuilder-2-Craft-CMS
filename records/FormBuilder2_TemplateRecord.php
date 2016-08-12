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
            'templateLayout'        => AttributeType::Mixed,
            'templateBodyCopy'      => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateFooterCopy'    => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateContent'       => AttributeType::Mixed,
            'templateStyles'        => AttributeType::Mixed,
            'templateSettings'      => AttributeType::Mixed,
            'templateAltCopy'       => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateAltCopy2'      => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateAltCopy3'      => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateAltCopy4'      => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
            'templateAltCopy5'      => array(AttributeType::String, 'required' => true, 'column' => ColumnType::Text),
        );
    }
}
