<?php
namespace Craft;

class FormBuilder2_FieldRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'formbuilder2_fields';
    }

    public function defineRelations()
    {
        return array(
            'field'       => array(static::BELONGS_TO, 'FieldRecord',       'onDelete' => static::CASCADE),
            'fieldLayout' => array(static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::CASCADE),
        );
    }

    protected function defineAttributes()
    {
        return array(
            'template' => AttributeType::String
        );
    }
}
