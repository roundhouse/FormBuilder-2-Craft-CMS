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
				// Create transaction only if this isn't apart of an already occurring transaction
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

	/**
	 * An event dispatcher for the moment before saving a label.
	 *
	 * @param Event $event
	 * @throws \CException
	 */
	public function onBeforeSaveLabel(Event $event)
	{
		$this->raiseEvent('onBeforeSaveLabel', $event);
	}

	/**
	 * An event dispatcher for the moment after saving a label.
	 *
	 * @param Event $event
	 * @throws \CException
	 */
	public function onSaveLabel(Event $event)
	{
		$this->raiseEvent('onSaveLabel', $event);
	}


}
