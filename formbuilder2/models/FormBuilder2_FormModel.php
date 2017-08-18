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
      'id'                      => AttributeType::Number,
      'name'                    => array(AttributeType::Name, 'required' => true),
      'handle'                  => array(AttributeType::Handle, 'required' => true),
      'fieldLayoutId'           => AttributeType::Number,
      'options'                 => AttributeType::Mixed,
      'spam'                    => AttributeType::Mixed,
      'messages'                => AttributeType::Mixed,
      'notify'                  => AttributeType::Mixed,
      'settings'                => AttributeType::Mixed,
      'sortOrder'               => AttributeType::SortOrder,
      'dateUpdated'             => AttributeType::DateTime,
      // 'customRedirect'                      => AttributeType::Bool,
      // 'customRedirectUrl'                   => AttributeType::String,
      // 'hasFileUploads'                      => AttributeType::Bool,
      // 'ajaxSubmit'                          => AttributeType::Bool,
      // 'spamTimeMethod'                      => AttributeType::Bool,
      // 'spamTimeMethodTime'                  => AttributeType::Number,
      // 'spamHoneypotMethod'                  => AttributeType::Bool,
      // 'spamHoneypotMethodMessage'           => AttributeType::String,
      // 'successMessage'                      => array(AttributeType::String, 'required' => true),
      // 'errorMessage'                        => array(AttributeType::String, 'required' => true),
      // 'notifySubmission'                    => AttributeType::Bool,
      // 'notifyEmail'                         => AttributeType::String,
      // 'submitterEmail'                      => AttributeType::String,
      // 'submitterEmailSubject'               => AttributeType::String,
      // 'notifySubmitter'                     => AttributeType::Bool,
      // 'emailSubject'                        => AttributeType::Name,
      // 'sendSubmissionData'                  => AttributeType::Bool,
      // 'customSubject'                       => AttributeType::Bool,
      // 'customSubjectLine'                   => AttributeType::String,
      // 'emailTemplateStyle'                  => AttributeType::String,
      // 'emailBodyCopy'                       => AttributeType::String,
      // 'emailAdditionalFooterCopy'           => AttributeType::String,
      // 'emailCustomLogo'                     => AttributeType::Number,
      // 'emailBackgroundColor'                => AttributeType::String,
      // 'emailContainerWidth'                 => AttributeType::Number,
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