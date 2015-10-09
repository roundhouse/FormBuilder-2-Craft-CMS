<?php
namespace Craft;

/**
 * OFFICIAL DOCUMENTATION:
 * http://buildwithcraft.com/docs/plugins/controllers
 */

/**
 * Business Logic Controller
 *
 * Controller methods get a little more complicated... There are several ways to access them:
 *
 *     1. Submitting a form can trigger a controller action.
 *     2. Using an AJAX request can trigger a controller action.
 *     3. Routing to an action URL will trigger a controller action.
 *
 * A controller can do many things, but be wary... If your logic gets too complex, you may want
 * to off-load much of it to the Service file.
 */

class FormBuilder2Controller extends BaseController
{

	/**
	 * By default, access to controllers is restricted to logged-in users.
	 * However, you can allow anonymous access by uncommenting the line below.
	 *
	 * It is also possible to allow anonymous access to only certain methods,
	 * by supplying an array of method names, rather than a boolean value.
	 *
	 * See also:
	 * http://buildwithcraft.com/docs/plugins/controllers#allowing-anonymous-access-to-actions
	 */
	protected $allowAnonymous = true;

	/**
	 * For a normal form submission, send it here.
	 *
	 * HOW TO USE IT
	 * The HTML form in your template should include this hidden field:
	 *
	 *     <input type="hidden" name="action" value="formbuilder2/exampleFormSubmit">
	 *
	 */
	public function actionExampleFormSubmit()
	{
		// ... whatever you want to do with the submitted data...
		$this->redirect('thankyou/page/url');
	}

	/**
	 * When you need AJAX, this is how to do it.
	 *
	 * HOW TO USE IT
	 * In your front-end JavaScript, POST your AJAX call like this:
	 *
	 *     // example uses jQuery
	 *     $.post('actions/formbuilder2/exampleAjax' ...
	 *
	 * Or if your plugin is doing something within the control panel,
	 * you've got a built-in function available which Craft provides:
	 *
	 *     Craft.postActionRequest('formBuilder2/exampleAjax' ...
	 *
	 */
	public function actionExampleAjax()
	{
		$this->requireAjaxRequest();
		// ... whatever your AJAX does...
		$response = array('response' => 'Round trip via AJAX!');
		$this->returnJson($response);
	}

	/**
	 * Load Dashboard
	 *
	 */
	public function actionDashboard()
	{
    $variables['forms'] = craft()->formBuilder2_form->getAllForms();
    $variables['settings'] = craft()->plugins->getPlugin('FormBuilder2');
    return $this->renderTemplate('formbuilder2/dashboard', $variables);
	}

}
