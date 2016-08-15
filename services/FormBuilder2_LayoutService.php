<?php
namespace Craft;

class FormBuilder2_LayoutService extends BaseApplicationComponent
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



	public function getLayoutById($layoutId)
	{
	  $layoutRecord = FormBuilder2_LayoutRecord::model()->findById($layoutId);

	  if ($layoutRecord) {
	    return FormBuilder2_LayoutModel::populateModel($layoutRecord);
	  }
	}



	public function getAllLayouts()
	{
	  $layoutRecords = FormBuilder2_LayoutRecord::model()->findAll();
	  $layouts = FormBuilder2_LayoutModel::populateModels($layoutRecords);

	  return $layouts;
	}



	public function saveLayoutMarkup(FormBuilder2_MarkupModel $markup)
	{
		$layout = $this->getLayoutById($markup->key);

		if(!$layout) {
			throw new Exception(Craft::t('No layout exists with the ID “{key}”', array('key' => $markup->key)));
		} else {
			$layoutRecord = FormBuilder2_LayoutRecord::model()->findById($markup->key);

			$layoutRecord->setAttribute('content', $markup->body);
			$layoutRecord->save();
		}

		return true;
	}



	public function saveLayout(FormBuilder2_LayoutModel $layout)
 	{
 		if ($layout->id) {
 			$layoutRecord = FormBuilder2_LayoutRecord::model()->findById($layout->id);

 			if (!$layoutRecord) {
 		    	throw new Exception(Craft::t('No layout exists with the ID “{id}”', array('id' => $layout->id)));
 			}

 			$oldLayout = FormBuilder2_LayoutModel::populateModel($layoutRecord);
 			$isNewLayout = false;
 		} else {
 			$layoutRecord = new FormBuilder2_LayoutRecord();
 			$isNewLayout = true;
 		}

 		$layoutRecord->name 					= $layout->name;
 		$layoutRecord->handle 					= $layout->handle;
 		$layoutRecord->description 				= $layout->description;
 		$layoutRecord->icon 					= $layout->icon;
 		$layoutRecord->templateName 			= $layout->templateName;
 		$layoutRecord->templateOriginalName		= $layout->templateOriginalName;
 		$layoutRecord->templatePath 			= $layout->templatePath;


 		$layoutRecord->validate();
 		$layout->addErrors($layoutRecord->getErrors());

 		if (!$layout->hasErrors()) {
 		  $transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();
 		  
 		  try {
 		    $layoutRecord->save(false);

 		    if (!$layout->id) { 
 		    	$layout->id = $layoutRecord->id;
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
}
