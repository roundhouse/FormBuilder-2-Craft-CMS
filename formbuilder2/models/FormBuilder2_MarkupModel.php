<?php
namespace Craft;

class FormBuilder2_MarkupModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'key'      => AttributeType::String,
			'body'     => AttributeType::String,
			'htmlBody' => AttributeType::String,
		);
	}
}
