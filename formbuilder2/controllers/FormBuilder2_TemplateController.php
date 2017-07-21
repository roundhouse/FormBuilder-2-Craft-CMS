<?php
namespace Craft;

class FormBuilder2_TemplateController extends BaseController
{
 
 	protected $allowAnonymous = true;


  public function actionIndex()
  {
    $templates  = fb()->templates->getAllTemplates();
    $layouts    = fb()->layouts->getAllLayouts();

    $variables['title']       = 'FormBuilder2';
    $variables['templates']   = $templates;
    $variables['layouts']     = $layouts;

    return $this->renderTemplate('formbuilder2/templates/index', $variables);
  }

  public function actionEditTemplate(array $variables = array())
  {
    $variables['brandNewTemplate'] = false;

    $variables['templateLayouts'] = fb()->templates->getTemplateFiles();

    if (!empty($variables['templateId'])) {
      if (empty($variables['template'])) {
        $variables['template'] = fb()->templates->getTemplateById($variables['templateId']);
        if (!$variables['template']) { 
          throw new HttpException(404, Craft::t('No templates exist.'));
        }
      }
      $variables['title'] = $variables['template']->name;
    } else {
      if (empty($variables['template'])) {

        $variables['template'] = new FormBuilder2_TemplateModel();
        $variables['brandNewTemplate'] = true;
      }
      $variables['title'] = Craft::t('Create a new template');
    }


    $this->renderTemplate('formbuilder2/templates/_edit', $variables);
  }

  public function actionDeleteTemplate()
  {
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    $templateId = craft()->request->getRequiredPost('id');

    fb()->templates->deleteTemplateById($templateId);
    $this->returnJson(array('success' => true));
  }

  /**
   * Get Template Information By Name
   * 
   * @return array Returns array of template file variables
   */
  public function actionGetTemplateByName()
  {
    $templateName = craft()->request->getPost('templateName');

    if (!$templateName == 0) {
        $templateInformation = fb()->templates->getTemplateByName($templateName);
    } else {
        $templateInformation = false;
    }

    $this->returnJson($templateInformation);
  }


  public function actionGetEmailTemplate()
  {
    $id = craft()->request->getPost('templateId');
    // $emailTemplate = craft()->formBuilder2
    $variables['id'] = $id;
    $variables['foo'] = 'bar';

    craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
    $html = craft()->templates->render('formbuilder2/templates/email/layouts/template-'.$id, $variables);
    craft()->templates->setTemplatesPath(craft()->templates->getTemplatesPath());

    $this->returnJson($html);
  }


    public function actionSaveTemplate()
    {
        $this->requirePostRequest();
        $template = new FormBuilder2_TemplateModel();

        $template->id                 = craft()->request->getPost('templateId');
        $template->name               = craft()->request->getPost('name');
        $template->handle             = craft()->request->getPost('handle');
        $template->bodyText           = craft()->request->getPost('bodyText');
        $template->footerText         = craft()->request->getPost('footerText');
        $template->templateContent    = craft()->request->getPost('templateContent');
        $template->templateStyles     = craft()->request->getPost('templateStyles');
        $template->templateSettings   = craft()->request->getPost('templateSettings');

        if (fb()->templates->saveTemplate($template)) {
          craft()->userSession->setNotice(Craft::t('Template saved.'));
          $this->redirectToPostedUrl($template);
        } else {
          craft()->userSession->setError(Craft::t('Couldn’t save template.'));
        }

        craft()->urlManager->setRouteVariables(array(
          'template' => $template
        ));
    }





}
