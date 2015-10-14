<?php
namespace Craft;

class FormBuilder2_FormController extends BaseController
{

  protected $allowAnonymous = true;


  /**
   * Get All Forms
   *
   */
  public function actionFormsIndex()
  { 

    $formItems = craft()->formBuilder2_form->getAllForms();
    $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
    $plugins = craft()->plugins->getPlugin('FormBuilder2');

    return $this->renderTemplate('formbuilder2/forms/index', array(
      'formItems'  => $formItems,
      'settings'  => $settings,
      'plugin'  => $plugins
    ));
  }


  /**
   * Edit a Form
   *
   */
  public function actionEditForm(array $variables = array())
  {
    $variables['brandNewForm'] = false;

    if (!empty($variables['formId'])) {
      if (empty($variables['form'])) {
        $variables['form'] = craft()->formBuilder2_form->getFormById($variables['formId']);
        if (!$variables['form']) { throw new HttpException(404); }
      }
      $variables['title'] = $variables['form']->name;
    } else {
      if (empty($variables['form'])) {
        $variables['form'] = new FormBuilder2_FormModel();
        $variables['brandNewForm'] = true;
      }
      $variables['title'] = Craft::t('Create a new form');
    }

    $this->renderTemplate('formbuilder2/forms/_edit', $variables);
  }

  /**
   * Saves New Form.
   *
   */
  public function actionSaveForm()
  {
    $this->requirePostRequest();
    $form = new FormBuilder2_FormModel();

    $form->id                           = craft()->request->getPost('formId');
    $form->name                         = craft()->request->getPost('name');
    $form->handle                       = craft()->request->getPost('handle');
    $form->customRedirect               = craft()->request->getPost('customRedirect');
    $form->customRedirectUrl            = craft()->request->getPost('customRedirectUrl');
    $form->ajaxSubmit                   = craft()->request->getPost('ajaxSubmit');
    $form->spamTimeMethod               = craft()->request->getPost('spamTimeMethod');
    $form->spamTimeMethodTime           = craft()->request->getPost('spamTimeMethodTime');
    $form->spamHoneypotMethod           = craft()->request->getPost('spamHoneypotMethod');
    $form->spamHoneypotMethodString     = craft()->request->getPost('spamHoneypotMethodString');
    $form->spamHoneypotMethodMessage    = craft()->request->getPost('spamHoneypotMethodMessage');
    $form->successMessage               = craft()->request->getPost('successMessage');
    $form->errorMessage                 = craft()->request->getPost('errorMessage');
    $form->notifySubmission             = craft()->request->getPost('notifySubmission');
    $form->notifyEmail                  = craft()->request->getPost('notifyEmail');
    $form->emailSubject                 = craft()->request->getPost('emailSubject');
    $form->notifyTemplatePath           = craft()->request->getPost('notifyTemplatePath');
    $form->fieldLayoutId                = craft()->request->getPost('fieldLayoutId');

    $fieldLayout = craft()->fields->assembleLayoutFromPost();
    $fieldLayout->type = ElementType::Asset;
    $form->setFieldLayout($fieldLayout);

    if (craft()->formBuilder2_form->saveForm($form)) {
      craft()->userSession->setNotice(Craft::t('Form saved.'));
      $this->redirectToPostedUrl($form);
    } else {
      craft()->userSession->setError(Craft::t('Couldn’t save form.'));
    }

    craft()->urlManager->setRouteVariables(array(
      'form' => $form
    ));
  }

  /**
   * Delete Form.
   *
   */
  public function actionDeleteForm()
  {
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    $formId = craft()->request->getRequiredPost('id');

    craft()->formBuilder2_form->deleteFormById($formId);
    $this->returnJson(array('success' => true));
  }

  

}
