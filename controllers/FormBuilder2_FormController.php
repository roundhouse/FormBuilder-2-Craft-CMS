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

    $form->id                                     = craft()->request->getPost('formId');
    $form->name                                   = craft()->request->getPost('name');
    $form->handle                                 = craft()->request->getPost('handle');
    $form->emailSubject                           = craft()->request->getPost('emailSubject');
    // $form->ajaxSubmit                             = craft()->request->getPost('ajaxSubmit');
    // $form->successPageRedirect                    = craft()->request->getPost('successPageRedirect');
    // $form->redirectUrl                            = craft()->request->getPost('redirectUrl');
    // $form->useReCaptcha                           = craft()->request->getPost('useReCaptcha');
    // $form->hasFileUploads                         = craft()->request->getPost('hasFileUploads');
    // $form->uploadSource                           = craft()->request->getPost('uploadSource');
    $form->successMessage                         = craft()->request->getPost('successMessage');
    $form->errorMessage                           = craft()->request->getPost('errorMessage');
    // $form->notifyFormAdmin                        = craft()->request->getPost('notifyFormAdmin');
    // $form->toEmail                                = craft()->request->getPost('toEmail');
    // $form->notificationTemplatePath               = craft()->request->getPost('notificationTemplatePath');
    // $form->notifyRegistrant                       = craft()->request->getPost('notifyRegistrant');
    // $form->notificationTemplatePathRegistrant     = craft()->request->getPost('notificationTemplatePathRegistrant');
    // $form->notificationFieldHandleName            = craft()->request->getPost('notificationFieldHandleName');

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
