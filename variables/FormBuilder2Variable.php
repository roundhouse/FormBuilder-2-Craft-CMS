<?php
namespace Craft;

class FormBuilder2Variable
{

  /**
   * Load Required Scripts
   * 
   */
  public function includeScripts($form)
  { 
    // Ajax Submit Script
    if ($form->formSettings["ajaxSubmit"] == "1") {
      craft()->templates->includeJsFile(UrlHelper::getResourceUrl('formbuilder2/js/ajaxsubmit.js'));
    }
    
    $fieldLayout = $form->fieldLayout->getFieldLayout();
    $fields = $fieldLayout->getFields();
    foreach ($fields as $key => $value) {
      $field = $value->getField();
      if ($field->type == 'Color') {
        // Colorpicker
        craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/colorpicker.css'));
        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('formbuilder2/js/libs/colorpicker.js'));
      } elseif ($field->type == 'Date') {
        // Date & Time Picker
        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('/lib/jquery-ui.min.js'));
        craft()->templates->includeJsFile(UrlHelper::getResourceUrl('lib/jquery.timepicker/jquery.timepicker.min.js'));
        craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/datetimepicker.css'));
      } elseif ($field->type == 'RichText') {
        // WYSIWYG Editor
        craft()->templates->includeCssResource('/lib/redactor/redactor.css');
        craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
      } elseif ($field->type == 'Lightswitch') {
        // Lightswitch
        craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/lightswitch.css'));
      }
    }
    return;
  }

  /**
   * Get Form By Id
   * 
   */
  public function getFormById($formId)
  {
    return craft()->formBuilder2_form->getFormById($formId);
  }

  /**
	 * Get Form By Id
	 * 
	 */
	public function getFormHtmlById($formId)
	{
		$form = craft()->formBuilder2_form->getFormById($formId);
		$oldPath = craft()->templates->getTemplatesPath();

    $variables['formId'] = $form;

    craft()->templates->setTemplatesPath(craft()->path->getPluginsPath().'formbuilder2/templates');
    $html = craft()->templates->render('/forms/frontend', $variables);
    craft()->templates->setTemplatesPath($oldPath);

    return $html;
  }
  
  /**
   * Get Form By Handle
   * 
   */
  public function getFormByHandle($formHandle)
  {
    return craft()->formBuilder2_form->getFormByHandle($formHandle);
  }

  /**
   * Get Total Number of Forms
   * 
   */
  public function totalForms()
  {
    $count = craft()->formBuilder2_form->getTotalForms();
    return $count;
  }

  /**
   * Get All Forms
   * 
   */
  public function getAllForms()
  {
    $forms = craft()->formBuilder2_form->getAllForms();
    return $forms;
  }

  

  /**
   * Get Total Number of Submissions
   * 
   */
  public function totalEntries()
  {
    $count = craft()->formBuilder2_entry->getTotalEntries();
    return $count;
  }

  /**
   * Get Total Number of Submissions Per Form
   * 
   */
  public function getAllEntriesFromFormID($formId)
  {
    return craft()->formBuilder2_entry->getAllEntriesFromFormID($formId);
  }
  
  /**
   * Get Input HTML for FieldTypes
   * 
   */
  public function getInputHtml($field, $value = []) 
  {

    $theField = craft()->fields->getFieldById($field->fieldId);
    $fieldType = $theField->getFieldType();

    $requiredField = $field->required; 
    $theField->required = $requiredField; 

	$attributes 		= $theField->attributes;
	$pluginSettings 	= craft()->plugins->getPlugin('FormBuilder2')->getSettings(); // DEPRICATE
	  
	$fieldSettings = '';
	if ($fieldType) {
	  	$fieldSettings	= $fieldType->getSettings();
	}

	$oldPath = craft()->templates->getTemplatesPath();
	craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

    $templatePath = craft()->path->getPluginsPath() . 'plugins/formbuilder2/templates/inputs/';
    $customTemplatePath = craft()->path->getPluginsPath() . 'formbuilder2/templates/custom/inputs/';
    $extension = '.twig';

    if (isset($attributes['settings']['placeholder'])) { $varPlaceholder = $attributes['settings']['placeholder']; } else { $varPlaceholder = null; }
    if (isset($attributes['settings']['options'])) { $varOptions = $attributes['settings']['options']; } else { $varOptions = null; }
    if (isset($attributes['settings']['values'])) { $varValues = $attributes['settings']['values']; } else { $varValues = null; }
    if (isset($attributes['settings']['default'])) { $varOn = $attributes['settings']['default']; } else { $varOn = null; }
    if (isset($attributes['settings']['checked'])) { $varChecked = $attributes['settings']['checked']; } else { $varChecked = null; }
    if (isset($attributes['settings']['minuteIncrement'])) { $varMinuteIncrement = $attributes['settings']['minuteIncrement']; } else { $varMinuteIncrement = null; }
    if (isset($attributes['settings']['showTime'])) { $varShowTime = $attributes['settings']['showTime']; } else { $varShowTime = null; }
    if (isset($attributes['settings']['showDate'])) { $varShowDate = $attributes['settings']['showDate']; } else { $varShowDate = null; }
    if (isset($attributes['settings']['min'])) { $varMin = $attributes['settings']['min']; } else { $varMin = null; }
    if (isset($attributes['settings']['max'])) { $varMax = $attributes['settings']['max']; } else { $varMax = null; }
    if (isset($attributes['settings']['limit'])) { $varLimit = $attributes['settings']['limit']; } else { $varLimit = null; }
    if (isset($attributes['settings']['selectionLabel'])) { $varSelectionLabel = $attributes['settings']['selectionLabel']; } else { $varSelectionLabel = null; }
    
    // Unless "All" sections has been chosen loop through the array and 
    // remove the 'section:' string from all source ID's and. Then convert all
    // the strings to intigers so we are left with an array of section ids
    if (isset($attributes['settings']['sources'])) { 
    	$varSources = $attributes['settings']['sources'];
    	if ($varSources != '*') {
    		$varSources = array_map( function($var) { return is_numeric($var) ? (int)$var : $var; }, str_replace("section:", "", $varSources) );
    	}
  	} else { 
  		$varSources = null; 
  	}

    // Check if there was a value
    $value = (array_key_exists($theField->handle, $value)) ? $value[$theField->handle] : null;

	  $variables = [
	  	'field'             => $attributes,
	  	'type'  			=> $attributes['type'],
	  	'name'  			=> $attributes['handle'],
	  	'label'  			=> $attributes['name'],
	  	'handle'  			=> $attributes['handle'],
	  	'instructions'  	=> $attributes['instructions'],
	  	'placeholder'  		=> $varPlaceholder,
	  	'options'  			=> $varOptions,
	  	'value'  			=> $value,
	  	'values'  			=> $varValues,
	  	'on'		  		=> $varOn,
	  	'checked'		  	=> $varChecked,
	  	'minuteIncrement'   => $varMinuteIncrement,
	  	'showTime'			=> $varShowTime,
	  	'showDate'			=> $varShowDate,
	  	'min'  				=> $varMin,
	  	'max'  				=> $varMax,
	  	'requiredJs'		=> null,
	  	'required'	  		=> $theField->required,
	  	'settings'	  		=> $fieldSettings,
        'sources'           => $varSources
	  ];

    $html = '';

    // Check for Sprout Fields Plugin
    $sproutFieldsPlugin = craft()->plugins->getPlugin('sproutfields', false);
    $sproutFields = false;
    if ($sproutFieldsPlugin->isInstalled && $sproutFieldsPlugin->isEnabled) {
        $sproutFields = true;
    }

    switch ($theField->type) {
	  	// Sprout Fields
	  	case "SproutFields_Email":
	  		if ($sproutFields) {
	  			$html = craft()->templates->render('formbuilder2/templates/inputs/email', $variables);
	  		}
	  	break;
	  	case "SproutFields_Phone":
	  		if ($sproutFields) {
	  			$html = craft()->templates->render('formbuilder2/templates/inputs/phone', $variables);
	  		}
	  	break;
	  	case "SproutFields_Link":
	  		if ($sproutFields) {
	  			$html = craft()->templates->render('formbuilder2/templates/inputs/link', $variables);
	  		}
      break;
      case "SproutFields_Hidden":
        if ($sproutFields) {
          $html = craft()->templates->render('sproutfields/templates/_integrations/sproutforms/fields/hidden/input', $variables);
        }
	  	break;
      case "PlainText":
        if ($attributes['settings']['multiline']) {
          if (IOHelper::fileExists($customTemplatePath . 'textarea' . $extension)) {
            $html = craft()->templates->render('formbuilder2/templates/custom/inputs/textarea', $variables);
          } else {
            $html = craft()->templates->render('formbuilder2/templates/inputs/textarea', $variables);
          }
        } else {
          if (IOHelper::fileExists($customTemplatePath . 'text' . $extension)) {
            $html = craft()->templates->render('formbuilder2/templates/custom/inputs/text', $variables);
          } else {
            $html = craft()->templates->render('formbuilder2/templates/inputs/text', $variables);
          }
        }
      break;
      case "Checkboxes":
        if (IOHelper::fileExists($customTemplatePath . 'checkbox' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/checkbox', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/checkbox', $variables);
        }
      break;
      case "RadioButtons":
        if (IOHelper::fileExists($customTemplatePath . 'radio' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/radio', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/radio', $variables);
        }
      break;
      case "Entries":
        if (IOHelper::fileExists($customTemplatePath . 'entries' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/entries', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/entries', $variables);
        }
      break;
      case "Dropdown":
        if (IOHelper::fileExists($customTemplatePath . 'select' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/select', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/select', $variables);
        }
      break;
      case "MultiSelect":
        if (IOHelper::fileExists($customTemplatePath . 'select' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/multiselect', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/multiselect', $variables);
        }
      break;
      case "RichText":
        $variables['requiredJs'] = 'redactor';
        if (IOHelper::fileExists($customTemplatePath . 'richtext' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/richtext', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/richtext', $variables);
        }
      break;
      case "Lightswitch":
        if (IOHelper::fileExists($customTemplatePath . 'lightswitch' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/lightswitch', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/lightswitch', $variables);
        }
      break;
      case "Color":
        $variables['value'] = '#000000';
        $variables['requiredJs'] = 'colorpicker';
        if (IOHelper::fileExists($customTemplatePath . 'color' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/color', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/color', $variables);
        }
      break;
      case "Date":
        $variables['requiredJs'] = 'dateandtime';
        if (IOHelper::fileExists($customTemplatePath . 'datetime' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/datetime', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/datetime', $variables);
        }
      break;
      case "Number":
        $variables['value'] = craft()->numberFormatter->formatDecimal($attributes['settings']['decimals'], false);
        if (IOHelper::fileExists($customTemplatePath . 'number' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/number', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/number', $variables);
        }
      break;
      case "Assets":
      if (IOHelper::fileExists($customTemplatePath . 'file' . $extension)) {
          $html = craft()->templates->render('formbuilder2/templates/custom/inputs/file', $variables);
        } else {
          $html = craft()->templates->render('formbuilder2/templates/inputs/file', $variables);
        }
      break;
    }

    craft()->templates->setTemplatesPath($oldPath);
    return $html;
  }

}
