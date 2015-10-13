<?php
namespace Craft;

class FormBuilder2_FormModel extends BaseModel
{
  /**
   * Name to string
   *
   */
  function __toString()
  {
    return Craft::t($this->name);
  }

  /**
   * Define Attributes
   *
   */
  protected function defineAttributes()
  {
    return array(
      'id'                                  => AttributeType::Number,
      'name'                                => array(AttributeType::Name, 'required' => true),
      'handle'                              => array(AttributeType::Handle, 'required' => true),
      'notifySubmission'                    => AttributeType::Bool,
      'notifyEmail'                         => AttributeType::String,
      'notifyTemplatePath'                  => AttributeType::String,
      'emailSubject'                        => AttributeType::Name,
      'redirectUrl'                         => AttributeType::String,
      'successMessage'                      => AttributeType::String,
      'errorMessage'                        => AttributeType::String,
      'spamTimeMethod'                      => AttributeType::Bool,
      'spamTimeMethodTime'                  => AttributeType::Number,
      'spamHoneypotMethod'                  => AttributeType::Bool,
      'spamHoneypotMethodString'            => AttributeType::String,
      'spamHoneypotMethodMessage'           => AttributeType::String,
      'fileUploadSourceUrl'                 => AttributeType::String,
      'fieldLayoutId'                       => AttributeType::Number,
      'ajaxSubmit'                          => AttributeType::Bool
    );
  }

  /**
   * Behaviors
   *
   */
  public function behaviors()
  {
    return array(
      'fieldLayout' => new FieldLayoutBehavior('FormBuilder_Entry'),
    );
  }

}