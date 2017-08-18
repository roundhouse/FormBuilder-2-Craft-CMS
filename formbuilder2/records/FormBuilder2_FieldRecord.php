<?php
namespace Craft;

class FormBuilder2_FieldRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'formbuilder2_fields';
    }

    protected function defineAttributes()
    {
        return array(
            'template'          => AttributeType::String,
            'fieldId'           => array(AttributeType::Number, 'required' => false)
            'fieldLayoutId'     => array(AttributeType::Number, 'required' => false)
        );
    }
    
    public function defineRelations()
    {
        return array(
            'fieldId'       => array(static::BELONGS_TO, 'FieldRecord', 'onDelete' => static::CASCADE),
            'fieldLayout'   => array(static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::CASCADE),
        );
    }

}
