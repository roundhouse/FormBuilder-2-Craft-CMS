<?php
namespace Craft;

class FormBuilder2Variable
{

    private $form;

    /**
    * Load Required Scripts
    *
    */
    public function includeScripts($form)
    {
        // Ajax Submit Script
        if ($form->formSettings["ajaxSubmit"] == "1") {
            $namespaced = '.formbuilder2#'.$form->handle;
            craft()->templates->includeJsFile(UrlHelper::getResourceUrl('formbuilder2/js/ajaxsubmit.js'));
            craft()->templates->includeJs('new FormBuilder2("'.$namespaced.'").init()');
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
            if (!isset($form->formSettings["disableDatepickerScripts"]) || $form->formSettings["disableDatepickerScripts"] != "1") {
                craft()->templates->includeJsFile(UrlHelper::getResourceUrl('/lib/jquery-ui.min.js'));
                craft()->templates->includeJsFile(UrlHelper::getResourceUrl('lib/jquery.timepicker/jquery.timepicker.min.js'));
                craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/datetimepicker.css'));
            }
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
        return fb()->forms->getFormById($formId);
    }

    /**
	* Get Form By Id
	*
	*/
	public function getFormHtmlById($formId)
	{
		$form = fb()->forms->getFormById($formId);
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
    $form = fb()->forms->getFormByHandle($formHandle);
    $this->form = $form; 
    return $form;
  }

  /**
   * Get Total Number of Forms
   *
   */
  public function totalForms()
  {
    $count = fb()->forms->getTotalForms();
    return $count;
  }

  /**
   * Get All Forms
   *
   */
  public function getAllForms()
  {
    $forms = fb()->forms->getAllForms();
    return $forms;
  }



  /**
   * Get Total Number of Submissions
   *
   */
  public function totalEntries()
  {
    $count = fb()->entries->getTotalEntries();
    return $count;
  }

  /**
   * Get Total Number of Submissions Per Form
   *
   */
  public function getAllEntriesFromFormID($formId)
  {
    return fb()->entries->getAllEntriesFromFormID($formId);
  }

  /**
   * Get Entry By Id
   *
   */
  public function getFormEntryById($entryId)
  {
    $entryModel = fb()->entries->getFormEntryById($entryId);
    $entry['form'] = array(
      'id'    =>  $entryModel->attributes['id'],
      'title' =>  $entryModel->attributes['title']
    );

    $submission = array();
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
  public function getInputHtml($field, $value = array()) 
  {
    $theField       = $field->getField();
    $fieldType      = $theField->getFieldType();
    $template       = fb()->fields->getFieldTemplate($field);
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
    if (isset($attributes['settings']['value'])){
        $value = $attributes['settings']['value'];
    } else {
        $value = (array_key_exists($theField->handle, $value)) ? $value[$theField->handle] : null;
    }

	  $variables = array(
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
        'sources'           => $varSources,
        'form'              => null
	  );

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
        $variables['form'] = $this->form;
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
      case "Table":
        if ($template) {
          $html = craft()->templates->render($template->template, $variables);
        } else {
            $this->_setTemplate(null, 'plugin');
            $html = craft()->templates->render('formbuilder2/templates/inputs/table', $variables);
            $this->_setTemplate($originaPath, 'site');
        }
      break;
    }

    return $html;
  }

  
  public function getTermsInputs($form)
  {
    $originaPath    = craft()->templates->getTemplatesPath();
    $terms          = $form->extra;

    $this->_setTemplate(null, 'plugin');
    $html = craft()->templates->render('formbuilder2/templates/inputs/terms', $terms);
    $this->_setTemplate($originaPath, 'site');
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
    $labels = fb()->fields->getJsonLabels();
    return json_encode($labels);
  }

  public function getFields()
  {
    $fields = fb()->fields->getFields();
    return json_encode($fields);
  }


  public function getAllMessages()
  {
    $messages = array();

    $messages[0] = array(
      'key' => 'templateBodyCopy',
      'heading' => 'Body Copy',
      'body' => "Hey {{user.friendlyName}},\n\n" .
    "Thanks for creating an account with {{siteName}}! To activate your account, click the following link:\n\n" .
    "{{submission}}\n\n" .
    "If you were not expecting this email, just ignore it.",
    );

    $messages[2] = array(
      'key' => 'templateFooterCopy',
      'heading' => 'Footer Copy',
      'body' => "Hey {{user.friendlyName}},\n\n" .
    "Thanks for creating an account with {{siteName}}! To activate your account, click the following link:\n\n" .
    "{{submission}}\n\n" .
    "If you were not expecting this email, just ignore it.",
    );

    return $messages;
  }

  public function getLayout($key)
  {
    $layout = fb()->layouts->getLayoutById($key);
    return $layout;
  }

  public function getTemplates()
  {
    $templates = fb()->templates->getAllTemplates();
    return $templates;
  }

  public function getBlockTypes()
  {
    $blockTypes = fb()->templates->getBlockTypes();
    return $blockTypes;
  }

}
