<?php
namespace Craft;

class FormBuilder2_LayoutController extends BaseController
{
 
 	protected $allowAnonymous = true;


  public function actionIndex()
  {
    $layouts = craft()->formBuilder2_layout->getAllLayouts();

    $variables['title']       = 'FormBuilder2';
    $variables['layouts']     = $layouts;

    return $this->renderTemplate('formbuilder2/layouts/index', $variables);
  }

  public function actionEditLayout(array $variables = array())
  {
    $variables['brandNewLayout'] = false;

    $variables['templateLayouts'] = craft()->formBuilder2_template->getTemplateFiles();

    if (!empty($variables['layoutId'])) {
      if (empty($variables['layout'])) {
        $variables['layout'] = craft()->formBuilder2_layout->getLayoutById($variables['layoutId']);
        if (!$variables['layout']) { 
          throw new HttpException(404, Craft::t('No layout exist.'));
        }
      }
      $variables['title'] = $variables['layout']->name;
      $variables['icon'] = $variables['layout']->icon;
      $criteria = craft()->elements->getCriteria(ElementType::Asset);
      $criteria->id = $variables['icon'][0];
      $criteria->limit = 1;
      $icon = $criteria->find();
      $variables['icon'] = $icon;
    } else {
      if (empty($variables['layout'])) {

        $variables['layout'] = new FormBuilder2_LayoutModel();
        $variables['brandNewLayout'] = true;
      }
      $variables['title'] = Craft::t('Create a new layout');
    }


    $this->renderTemplate('formbuilder2/layouts/_edit', $variables);
  }


    public function actionSaveLayout()
    {
        $this->requirePostRequest();
        $layout = new FormBuilder2_LayoutModel();

        $layout->id                 = craft()->request->getPost('layoutId');
        $layout->name               = craft()->request->getPost('name');
        $layout->handle             = craft()->request->getPost('handle');
        $layout->description        = craft()->request->getPost('description');
        $layout->icon               = craft()->request->getPost('icon');
        $layout->content            = craft()->request->getPost('content');

        if (craft()->formBuilder2_layout->saveLayout($layout)) {
          craft()->userSession->setNotice(Craft::t('Layout saved.'));
          $this->redirectToPostedUrl($layout);
        } else {
          craft()->userSession->setError(Craft::t('Couldn’t save layout.'));
        }

        craft()->urlManager->setRouteVariables(array(
          'layout' => $layout
        ));
    }


    public function actionSetTemplate()
    {
      $this->requirePostRequest();
      $this->requireAjaxRequest();

      $templatePath = craft()->request->getRequiredPost('templatePath');

      if ($templatePath) {
        $path = craft()->path->getPluginsPath().'formbuilder2/templates/layouts/templates/'.$templatePath;
        $file = IOHelper::getFile($path);
        $template= [
            'fileName'          => $file->getFileName(false),
            'fileOriginalName'  => $file->getFileName(),
            'fileNameCleaned'   => IOHelper::cleanFilename(IOHelper::getFileName($file->getRealPath(), false)),
            'fileExtension'     => $file->getExtension(),
            'filePath'          => $file->getRealPath(),
            'fileContents'      => $file->getContents()
        ];
        $this->returnJson([
            'success'   => true,
            'layout'    => $template
        ]);
      }


      if (craft()->formBuilder2_layout->saveLayoutMarkup($markup)) {
        $this->returnJson(array('success' => true));
      } else {
        $this->returnErrorJson(Craft::t('There was a problem saving your markup.'));
      }
    }





}
