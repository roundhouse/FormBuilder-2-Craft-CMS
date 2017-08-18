<?php
namespace Craft;

class FormBuilder2_TemplateController extends BaseController
{
 
 	protected $allowAnonymous = true;

    public function actionIndex()
    {
        $templates  = fb()->templates->getAllTemplates();

        $variables['title']       = 'FormBuilder2';
        $variables['templates']   = $templates;

        return $this->renderTemplate('formbuilder2/templates/index', $variables);
    }

    public function actionEdit(array $variables = array())
    {
        $type = $variables['type'];
        $variables['fullPageForm'] = true;

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
            }
            $variables['title'] = Craft::t('Create a new template');
        }

        // Load Redactor Scripts for Rich Text fields
        craft()->templates->includeCssResource('/lib/redactor/redactor.css');
        craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
        craft()->templates->includeCssResource('formbuilder2/css/libs/alignment.css');
        craft()->templates->includeJsResource('formbuilder2/js/libs/alignment.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontfamily.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontsize.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontcolor.js');

        craft()->templates->includeJsResource('formbuilder2/js/libs/colorpicker.js');

        // $this->renderTemplate('formbuilder2/templates/_'.$type, $variables);
        $this->renderTemplate('formbuilder2/templates/_edit', $variables);
    }



  // public function actionEditTemplate(array $variables = array())
  // {
  //   $variables['brandNewTemplate'] = false;

  //   if (!empty($variables['templateId'])) {
  //     if (empty($variables['template'])) {
  //       $variables['template'] = fb()->templates->getTemplateById($variables['templateId']);
  //       if (!$variables['template']) { 
  //         throw new HttpException(404, Craft::t('No templates exist.'));
  //       }
  //     }
  //     $variables['title'] = $variables['template']->name;
  //   } else {
  //     if (empty($variables['template'])) {

  //       $variables['template'] = new FormBuilder2_TemplateModel();
  //       $variables['brandNewTemplate'] = true;
  //     }
  //     $variables['title'] = Craft::t('Create a new template');
  //   }


  //   $this->renderTemplate('formbuilder2/templates/_edit', $variables);
  // }


    public function actionReorder()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $ids = JsonHelper::decode(craft()->request->getRequiredPost('ids'));

        if ($success = fb()->templates->reorderTemplates($ids)) {
            return $this->returnJson(array('success' => $success));
        }

        return $this->returnJson(array('error' => Craft::t("Couldn't reorder templates.")));
    }

    public function actionDeleteTemplate()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $templateId = craft()->request->getRequiredPost('id');
        $totalTemplates = fb()->templates->getTemplatesCount();

        if (fb()->templates->deleteTemplateById($templateId)) {
            $this->returnJson(array(
                'success' => true,
                'count' => $totalTemplates
            ));
        } else {
            $this->returnJson(array(
                'success' => false,
                'count' => $totalTemplates
            ));
        }

    }

  // /**
  //  * Get Template Information By Name
  //  * 
  //  * @return array Returns array of template file variables
  //  */
  // public function actionGetTemplateByName()
  // {
  //   $templateName = craft()->request->getPost('templateName');

  //   if (!$templateName == 0) {
  //       $templateInformation = fb()->templates->getTemplateByName($templateName);
  //   } else {
  //       $templateInformation = false;
  //   }

  //   $this->returnJson($templateInformation);
  // }


  // public function actionGetEmailTemplate()
  // {
  //   $id = craft()->request->getPost('templateId');
  //   // $emailTemplate = craft()->formBuilder2
  //   $variables['id'] = $id;
  //   $variables['foo'] = 'bar';

  //   craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());
  //   $html = craft()->templates->render('formbuilder2/templates/email/layouts/template-'.$id, $variables);
  //   craft()->templates->setTemplatesPath(craft()->templates->getTemplatesPath());

  //   $this->returnJson($html);
  // }


    public function actionSaveTemplate()
    {
        $this->requirePostRequest();
        $template = new FormBuilder2_TemplateModel();

        $template->id                 = craft()->request->getPost('templateId');
        $template->name               = craft()->request->getPost('name');
        $template->handle             = craft()->request->getPost('handle');
        $template->type               = craft()->request->getPost('type');

        $template->content    = craft()->request->getPost('content');
        $template->styles     = craft()->request->getPost('styles');
        $template->settings   = craft()->request->getPost('settings');

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
