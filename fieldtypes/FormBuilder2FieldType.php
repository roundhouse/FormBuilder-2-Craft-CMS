<?php
namespace Craft;

/**
 * Class FormBuilder2FieldType
 */
class FormBuilder2FieldType extends BaseFieldType implements IPreviewableFieldType
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IComponentType::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Forms');
	}

	/**
	 * @inheritDoc ISavableComponentType::getSettingsHtml()
	 *
	 * @return string|null
	 */
	// public function getSettingsHtml()
	// {
		// $formHandles = [];
		// $forms = craft()->formBuilder2_form->getAllForms();

		// foreach ($forms as $key => $value) {
		// 	$formHandles[$value->id] = $value->name;
		// }

		// return craft()->templates->render('formbuilder2/fieldtypes/forms/settings', array(
		// 	'forms' 		=> $formHandles,
		// 	'settings'  => $this->getSettings()
		// ));
		// return false;
	// }

	public function prepSettings($settings)
  {
  	$formHandles['options'] = [];
  	$forms = craft()->formBuilder2_form->getAllForms();

  	foreach ($forms as $key => $value) {
  		$formHandles['options'][$key]['label'] = $value->name;
  		$formHandles['options'][$key]['value'] = $value->id;
  	}

  	$settings = $formHandles;
    return $settings;
  }

	/**
	 * @inheritDoc IFieldType::defineContentAttribute()
	 *
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		return AttributeType::Number;
	}

	/**
	 * @inheritDoc IFieldType::getInputHtml()
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$field = craft()->fields->getFieldByHandle($name);
		$options = '';

		if ($field) {
			$options = isset($field->settings['options']) ? $field->settings['options'] : '';
		}

		return craft()->templates->render('formbuilder2/fieldtypes/forms/html', array(
			'name'    => $name,
			'value'   => $value,
			'options' => $options
		));
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseSavableComponentType::defineSettings()
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'formId'   => array(AttributeType::Number)
		);
	}
}
