<?php
namespace Craft;

class FormBuilder2Variable
{

	/**
	 * Get Total Number of Forms
	 * 
	 */
	public function totalFormsVariable()
	{
		$count = craft()->formBuilder2_form->getTotalForms();
		return $count;
	}

	/**
	 * Get Total Number of Entries
	 * 
	 */
	public function totalEntriesVariable()
	{
		$count = craft()->formBuilder2_entry->getTotalEntries();
		return $count;
	}

}
