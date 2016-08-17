<?php
namespace Craft;

class FormBuilder2_TemplateService extends BaseApplicationComponent
{
	
	public function getFormTemplate($formId)
	{
		$template = FormBuilder2_TemplateRecord::model()->findByAttributes([
			'formId' => $formId
		]);

		return $template;
	}

	public function getTemplateFiles()
	{
		$folderEmpty = true;
		if (IOHelper::isFolderEmpty(craft()->path->getPluginsPath().'formbuilder2/templates/email/layouts')) {
			throw new HttpException(404, Craft::t('Looks like you don\'t have any templates in your email/layouts folder.'));
		} else {
			$folderEmpty = false;
		}

		$fileList = IOHelper::getFolderContents(craft()->path->getPluginsPath().'formbuilder2/templates/email/layouts');
		$files = [];
		$filesModel = [];

		if (!$folderEmpty) {
			foreach ($fileList as $key => $file) {
				$files[$key] = [
					'fileName' 			=> IOHelper::getFileName($file, false),
					'fileOriginalName' 	=> IOHelper::getFileName($file),
					'fileNameCleaned' 	=> IOHelper::cleanFilename(IOHelper::getFileName($file, false)),
					'fileExtension' 	=> IOHelper::getExtension($file),
					'filePath' 			=> $file,
					'fileContents'		=> IOHelper::getFileContents($file)
				];
				$filesModel[] = FormBuilder2_FileModel::populateModel($files[$key]);
			}
		}

		return $filesModel;
	}



	public function getTemplateById($templateId)
	{
	  $templateRecord = FormBuilder2_TemplateRecord::model()->findById($templateId);

	  if ($templateRecord) {
	    return FormBuilder2_TemplateModel::populateModel($templateRecord);
	  } else {
	  	throw new Exception(404, Craft::t('No template exists with the ID “{id}”', array('id' => $template->id)));
	  }
	}

	public function getTemplateByHandle($handle)
	{
	  $templateRecord = FormBuilder2_TemplateRecord::model()->findByAttributes(array(
	      'handle' => $handle,
	    ));

	  if ($templateRecord) {
	    return FormBuilder2_TemplateModel::populateModel($templateRecord);
	  } else {
	  	throw new Exception(404, Craft::t('No template exists with the handle “{handle}”', array('id' => $handle)));
	  }
	}

	/**
	 * Get template file by its name
	 * 		
	 * @param  string $templateName Template file name
	 * @return array               Returns template file information
	 */
	public function getTemplateByName($templateName)
	{
		$template = [];
		$path = craft()->path->getPluginsPath().'formbuilder2/templates/email/templates/'.$templateName;
		$file = IOHelper::getFile($path);

		$template= [
			'fileName' 			=> $file->getFileName(false),
			'fileOriginalName' 	=> $file->getFileName(),
			'fileNameCleaned' 	=> IOHelper::cleanFilename(IOHelper::getFileName($file->getRealPath(), false)),
			'fileExtension' 	=> $file->getExtension(),
			'filePath' 			=> $file->getRealPath(),
			'fileContents'		=> $file->getContents()
		];
		return $template;		
	}



	public function getAllTemplates()
	{
	  $templateRecords = FormBuilder2_TemplateRecord::model()->findAll();
	  $templates = FormBuilder2_TemplateModel::populateModels($templateRecords);

	  return $templates;
	}



	public function saveTemplate(FormBuilder2_TemplateModel $template)
 	{
 		if ($template->id) {
 			$templateRecord = FormBuilder2_TemplateRecord::model()->findById($template->id);

 			if (!$templateRecord) {
 		    	throw new Exception(Craft::t('No template exists with the ID “{id}”', array('id' => $template->id)));
 			}

 			$oldTemplate = FormBuilder2_TemplateModel::populateModel($templateRecord);
 			$isNewTemplate = false;
 		} else {
 			$templateRecord = new FormBuilder2_TemplateRecord();
 			$isNewTemplate = true;
 		}

 		$templateRecord->name 				= $template->name;
 		$templateRecord->handle 			= $template->handle;
 		$templateRecord->bodyText 			= $template->bodyText;
 		$templateRecord->footerText			= $template->footerText;
 		$templateRecord->templateContent 	= JsonHelper::encode($template->templateContent);
 		$templateRecord->templateStyles 	= JsonHelper::encode($template->templateStyles);
 		$templateRecord->templateSettings 	= JsonHelper::encode($template->templateSettings);

 		$templateRecord->validate();
 		$template->addErrors($templateRecord->getErrors());

 		if (!$template->hasErrors()) {
 		  $transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();
 		  
 		  try {
 		    $templateRecord->save(false);

 		    if (!$template->id) { 
 		    	$template->id = $templateRecord->id;
 		    }

 		    if ($transaction !== null) { 
 		    	$transaction->commit();
 		    }

 		  } catch (\Exception $e) {
 		    if ($transaction !== null) { 
 		    	$transaction->rollback();
 		    }
 		    throw $e;
 		  }
 		 	return true;
 		} else { 
 			return false; 
 		}
 	}

 	public function deleteTemplateById($templateId)
 	{ 
 	  if (!$templateId) { 
 	  	return false;
 	  }

 	  $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
 	  
 	  try {
 	    $record = FormBuilder2_TemplateRecord::model()->findById(array(
 	    	'id' => $templateId
 	    ));
 	    
 	    $affectedRows = craft()->db->createCommand()->delete('formbuilder2_templates', array('id' => $templateId));

 	    if ($transaction !== null) { 
 	    	$transaction->commit();
 	    }
 	    return (bool) $affectedRows;
 	  } catch (\Exception $e) {
 	    if ($transaction !== null) { 
 	    	$transaction->rollback();
 	    }
 	    throw $e;
 	  }
 	}

 	public function getBlockTypes()
 	{
 		$blockTypes = ['social', 'link'];
		$blockTypeCollection = [];

 		foreach ($blockTypes as $key => $block) {
 			$blockTypeModel = new MatrixBlockTypeModel();
 			$blockTypeModel->name = $block;
 			$blockTypeModel->handle = $block;

 			$blockTypeCollection[] = $blockTypeModel;
 		}	

 		return $blockTypeCollection;
 	}
}
