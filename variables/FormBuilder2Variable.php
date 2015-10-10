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
  	// TODO: load redactor only if form has RichText fields
  	craft()->templates->includeCssResource('/lib/redactor/redactor.css');
		craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
		craft()->templates->includeJs("$('.richtext').redactor();");
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
	  }

	  return $html;
	}

}
