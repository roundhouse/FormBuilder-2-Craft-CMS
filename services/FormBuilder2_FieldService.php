<?php
namespace Craft;

class FormBuilder2_FieldService extends BaseApplicationComponent
{
	
	public function getFieldTemplate($fieldId)
	{
		$template = FormBuilder2_FieldRecord::model()->findByAttributes([
			'fieldId' => $fieldId
		]);

		return $template;
	}

	public function getAllLabels()
	{
		$labelRecords = FormBuilder2_FieldRecord::model()->findAll();
		$labels = FormBuilder2_FieldModel::populateModels($labelRecords);

		return $labels;
	}

	public function getLabels($fieldLayoutId)
	{
		$result = FormBuilder2_FieldRecord::model()->findAll(
			'fieldLayoutId='. $fieldLayoutId,
			array(
				'id' => null,
				'fieldId' => null,
				'fieldLayoutId' => null,
				'tempalte' => null
			)
		);

		return FormBuilder2_FieldModel::populateModels($result);
	}

	public function getJsonLabels()
	{
	  $labels = $this->getAllLabels();
	  $output = array();

	  foreach($labels as $label)
	  {
	    $output[$label->id] = array(
	      'id' => (int) $label->id,
	      'fieldId' => (int) $label->fieldId,
	      'fieldLayoutId' => (int) $label->fieldLayoutId,
	      'template' => Craft::t($label->template)
	    );
	  }

	  return $output;
	}

	public function saveLabel(FormBuilder2_FieldModel $label)
	{
		$isExisting = false;
		$record = null;

		if(is_int($label->id))
		{
			$record = FormBuilder2_FieldRecord::model()->findById($label->id);

			if($record)
			{
				$isExisting = true;
			}
			else
			{
				throw new Exception(Craft::t('No label exists with the ID “{id}”.', array('id' => $label->id)));
			}
		}
		else
		{
			$record = FormBuilder2_FieldRecord::model()->findByAttributes(array(
				'fieldId' => $label->fieldId,
				'fieldLayoutId' => $label->fieldLayoutId,
			));

			if($record)
			{
				$isExisting = true;
			}
			else
			{
				$record = new FormBuilder2_FieldRecord();
			}
		}

		$field = craft()->fields->getFieldById($label->fieldId);
		$layout = craft()->fields->getLayoutById($label->fieldLayoutId);

		if(!$field)
		{
			throw new Exception(Craft::t('No field exists with the ID “{id}”.', array('id' => $label->fieldId)));
		}

		if(!$layout)
		{
			throw new Exception(Craft::t('No field layout exists with the ID “{id}”.', array('id' => $label->fieldLayoutId)));
		}

		$record->fieldId = $label->fieldId;
		$record->fieldLayoutId = $label->fieldLayoutId;
		$record->template = $label->template;

		$record->validate();
		$label->addErrors($record->getErrors());

		$success = !$label->hasErrors();

		if($success)
		{
			$event = new Event($this, array(
				'label'        => $label,
				'isNewCustomField' => !$isExisting,
			));

			$this->onBeforeSaveLabel($event);

			if($event->performAction)
			{
				$transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();

				try
				{
					$record->save(false);
					$label->id = $record->id;

					if($transaction)
					{
						$transaction->commit();
					}
				}
				catch(\Exception $e)
				{
					if($transaction)
					{
						$transaction->rollback();
					}

					throw $e;
				}

				$this->onSaveLabel(new Event($this, array(
					'label'        => $label,
					'isNewCustomField' => !$isExisting,
				)));
			}
		}

		return $success;
	}

	public function onBeforeSaveLabel(Event $event)
	{
		$this->raiseEvent('onBeforeSaveLabel', $event);
	}

	public function onSaveLabel(Event $event)
	{
		$this->raiseEvent('onSaveLabel', $event);
	}

	public function getFields()
	{
	  $fields = craft()->fields->getAllFields();
	  $output = array();

	  foreach($fields as $field)
	  {
	    $output[(int) $field->id] = array(
	      'id' => (int) $field->id,
	      'handle' => $field->handle,
	      'name' => $field->name,
	      'instructions' => $field->instructions
	    );
	  }

	  return $output;
	}
}
