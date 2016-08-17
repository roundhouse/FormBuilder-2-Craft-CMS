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
   * Get Entry By Id
   * 
   */
  public function getFormEntryById($entryId)
  {
    $entryModel = craft()->formBuilder2_entry->getFormEntryById($entryId);
    $entry['form'] = [
      'id'    =>  $entryModel->attributes['id'],
      'title' =>  $entryModel->attributes['title']
    ];

    $submission = [];
    foreach (json_decode($entryModel->attributes['submission']) as $key => $object) {
      $submission[$key] = $object;
    }
    
    $entry['data'] = $submission;
    $entry['files'] = json_decode($entryModel->attributes['files']);

    return $entry;
  }


  /**
   * Get Input HTML for FieldTypes
   * 
   */
  public function getInputHtml($field, $value = []) 
  {
    $theField       = $field->getField();
    $fieldType      = $theField->getFieldType();
    $template       = craft()->formBuilder2_field->getFieldTemplate($field->fieldId);
    $originaPath    = craft()->templates->getTemplatesPath();

    $theField->required = $field->required; 

	$attributes 		= $theField->attributes;
	  
	$fieldSettings = '';
	if ($fieldType) {
	  	$fieldSettings	= $fieldType->getSettings();
    }

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
    if ($sproutFieldsPlugin && $sproutFieldsPlugin->isInstalled && $sproutFieldsPlugin->isEnabled) {
        $sproutFields = true;
    }

    switch ($theField->type) {
	  	// Sprout Fields
	  	case "SproutFields_Email":
	  		if ($sproutFields) {
                $this->_setTemplate(null, 'plugin');
                $html = craft()->templates->render('formbuilder2/templates/inputs/email', $variables);
                $this->_setTemplate($originaPath, 'site');
            }
        break;
        case "SproutFields_Phone":
            if ($sproutFields) {
                $this->_setTemplate(null, 'plugin');
                $html = craft()->templates->render('formbuilder2/templates/inputs/phone', $variables);
                $this->_setTemplate($originaPath, 'site');
            }
        break;
        case "SproutFields_Link":
            if ($sproutFields) {
                $this->_setTemplate(null, 'plugin');
                $html = craft()->templates->render('formbuilder2/templates/inputs/link', $variables);
                $this->_setTemplate($originaPath, 'site');
            }
      break;
      case "SproutFields_Hidden":
        if ($sproutFields) {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('sproutfields/templates/_integrations/sproutforms/fields/hidden/input', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
        break;
      case "PlainText":
        if ($attributes['settings']['multiline']) {
          if ($template) {
            $html = craft()->templates->render($template->template, $variables);
          } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/textarea', $variables);
            $this->_setTemplate($originaPath, 'site');
          }
        } else {
          if ($template) {
            $html = craft()->templates->render($template->template, $variables);
          } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/text', $variables);
            $this->_setTemplate($originaPath, 'site');
          }
        }
      break;
      case "Checkboxes":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/checkbox', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "RadioButtons":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/radio', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Entries":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/entries', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Dropdown":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/select', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "MultiSelect":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/multiselect', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "RichText":
        $variables['requiredJs'] = 'redactor';
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/richtext', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Lightswitch":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/lightswitch', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Color":
        $variables['value'] = '#000000';
        $variables['requiredJs'] = 'colorpicker';
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/color', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Date":
        $variables['requiredJs'] = 'dateandtime';
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/datetime', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Number":
        $variables['value'] = craft()->numberFormatter->formatDecimal($attributes['settings']['decimals'], false);
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/number', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
      case "Assets":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/file', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
    }

    return $html;
  }

  // Template Setters
  private function _setTemplate($path, $type = 'site')
  {
    if ($type == 'site') {
        craft()->templates->setTemplatesPath($path);
    }

    if ($type == 'plugin') {
        craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
    }
  }

  // Custom Field Templates
  public function getJsonLabels()
  {
    $labels = craft()->formBuilder2_field->getJsonLabels();
    return json_encode($labels);
  }

  public function getFields()
  {
    $fields = craft()->formBuilder2_field->getFields();
    return json_encode($fields);
  }


  public function getAllMessages()
  {
    $messages = [];

    $messages[0] = [
      'key' => 'templateBodyCopy',
      'heading' => 'Body Copy',
      'body' => "Hey {{user.friendlyName}},\n\n" .
    "Thanks for creating an account with {{siteName}}! To activate your account, click the following link:\n\n" .
    "{{submission}}\n\n" .
    "If you were not expecting this email, just ignore it.",
    ];

    $messages[2] = [
      'key' => 'templateFooterCopy',
      'heading' => 'Footer Copy',
      'body' => "Hey {{user.friendlyName}},\n\n" .
    "Thanks for creating an account with {{siteName}}! To activate your account, click the following link:\n\n" .
    "{{submission}}\n\n" .
    "If you were not expecting this email, just ignore it.",
    ];

    return $messages;
  }

  public function getLayout($key)
  {
    $layout = craft()->formBuilder2_layout->getLayoutById($key);
    return $layout;
  }

  public function getTemplates()
  {
    $templates = craft()->formBuilder2_template->getAllTemplates();
    return $templates;
  }

  public function getBlockTypes()
  {
    $blockTypes = craft()->formBuilder2_template->getBlockTypes();
    return $blockTypes;
  }

}
