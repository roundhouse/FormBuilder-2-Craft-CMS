<?php
namespace Craft;

class FormBuilder2Variable
{

	/**
	 * Get Form By Handle
	 * 
	 */
	public function includeScripts($form)
  {
  	
  	
  	// Lightswitch
  	// TODO: load lightswitch only if form has lightswitch fields
  	craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/lightswitch.css'));

  	// Colorpicker
  	// TODO: load colorpicker only if form has colorpicker fields
  	craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/colorpicker.css'));
  	craft()->templates->includeJsFile(UrlHelper::getResourceUrl('formbuilder2/js/libs/colorpicker.js'));

  	// WYSIWYG Editor
  	// TODO: load redactor only if form has RichText fields
  	craft()->templates->includeCssResource('/lib/redactor/redactor.css');
		craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
		craft()->templates->includeJs("$('.richtext').redactor();");
  	
  	// Date & Time Picker
  	// TODO: load date picker only if form has Date & Time fields
  	craft()->templates->includeJsFile(UrlHelper::getResourceUrl('/lib/jquery-ui.min.js'));
  	craft()->templates->includeJsFile(UrlHelper::getResourceUrl('lib/jquery.timepicker/jquery.timepicker.min.js'));
  	craft()->templates->includeCssFile(UrlHelper::getResourceUrl('formbuilder2/css/libs/datetimepicker.css'));

    return;
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
	 * Get Total Number of Entries
	 * 
	 */
	public function totalEntries()
	{
		$count = craft()->formBuilder2_entry->getTotalEntries();
		return $count;
	}
	
	/**
	 * Get Input HTML
	 * 
	 */
	public function getInputHtml($field) 
	{
	  $theField = craft()->fields->getFieldById($field->fieldId);
	  $fieldType = $theField->getFieldType();

	  $requiredField = $field->required; 
	  $theField->required = $requiredField; 

	  $attributes 			= $theField->attributes;
	  $pluginSettings 	= craft()->plugins->getPlugin('FormBuilder2')->getSettings();

	  craft()->path->setTemplatesPath(craft()->path->getPluginsPath());
	  
	  // var_dump($attributes);

	  switch ($theField->type) {
	    case "PlainText":
	    	$variables = [
	        'name'  					=> $attributes['name'],
	        'handle'  				=> $attributes['handle'],
	        'label'  					=> $attributes['name'],
	        'instructions'  	=> $attributes['instructions'],
	        'placeholder'  		=> $attributes['settings']['placeholder'],
	        'required'	  		=> $theField->required
	    	];
	    	if ($attributes['settings']['multiline']) {
		      $html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/textarea', $variables);
		    } else {
		      $html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/text', $variables);
		    }
	    break;
	    case "Checkboxes":
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/checkboxGroup', array(
	        'name'  				=> $attributes['name'],
	        'handle'  			=> $attributes['handle'],
	        'label'  				=> $attributes['name'],
	        'instructions'  => $attributes['instructions'],
	        'options'  			=> $attributes['settings']['options'],
	        'required'	  	=> $theField->required
	      ));
	    break;
	    case "RadioButtons":
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/radioGroup', array(
	        'name'  				=> $attributes['name'],
	        'handle'  			=> $attributes['handle'],
	        'label'  				=> $attributes['name'],
	        'instructions'  => $attributes['instructions'],
	        'options'  			=> $attributes['settings']['options'],
	        'values'  			=> $attributes['settings']['values'],
	        'required'	  	=> $theField->required
	      ));
	    break;
	    case "MultiSelect":
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/multiselect', array(
	        'name'  				=> $attributes['name'],
	        'handle'  			=> $attributes['handle'],
	        'label'  				=> $attributes['name'],
	        'instructions'  => $attributes['instructions'],
	        'options'  			=> $attributes['settings']['options'],
	        'values'  			=> $attributes['settings']['values'],
	        'required'	  	=> $theField->required
	      ));
	    break;
	    case "RichText":
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/textarea', array(
	        'class'  					=> 'richtext',
	        'name'  					=> $attributes['name'],
	        'handle'  				=> $attributes['handle'],
	        'label'  					=> $attributes['name'],
	        'instructions'  	=> $attributes['instructions'],
	        'placeholder'  		=> $attributes['settings']['placeholder'],
	        'required'	  		=> $theField->required
	      ));
	    break;
	    case "Lightswitch":
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/lightswitch', array(
	        'name'  					=> $attributes['name'],
	        'handle'  				=> $attributes['handle'],
	        'label'  					=> $attributes['name'],
	        'instructions'  	=> $attributes['instructions'],
	        'placeholder'  		=> $attributes['settings']['placeholder'],
	        'on'		  				=> $attributes['settings']['default'],
	        'required'	  		=> $theField->required
	      ));
	    break;
	    case "Color":
	    	$value = '#000000';
	    	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/color', array(
	        'name'  					=> $attributes['name'],
	        'handle'  				=> $attributes['handle'],
	        'label'  					=> $attributes['name'],
	        'value'  					=> $value,
	        'instructions'  	=> $attributes['instructions'],
	        'required'	  		=> $theField->required
	      ));
	    break;
	    case "Date":
      	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/datetime', array(
	    		'name'  					=> $attributes['name'],
	    		'handle'  				=> $attributes['handle'],
	    		'label'  					=> $attributes['name'],
	    		'instructions'  	=> $attributes['instructions'],
	    		'minuteIncrement' => $attributes['settings']['minuteIncrement'],
	    		'showTime'			 	=> $attributes['settings']['showTime'],
	    		'showDate'			 	=> $attributes['settings']['showDate'],
	    		'required'	  		=> $theField->required
      	));
	    break;
	    case "Number":
      	$html = craft()->templates->render('formbuilder2/templates/'.$pluginSettings['inputTemplatePath'].'/text', array(
	        'type'  					=> 'number',
	        'name'  					=> $attributes['name'],
	        'handle'  				=> $attributes['handle'],
	        'label'  					=> $attributes['name'],
      		'value' 					=> craft()->numberFormatter->formatDecimal($attributes['settings']['decimals'], false),
	        'instructions'  	=> $attributes['instructions'],
	        'placeholder'  		=> $attributes['settings']['placeholder'],
	        'min'  						=> $attributes['settings']['min'],
	        'max'  						=> $attributes['settings']['max'],
	        'required'	  		=> $theField->required
      	));
	    break;
	    case "Assets":
	    	var_dump($attributes);
	    break;
	  }

	  return $html;
	}

}
