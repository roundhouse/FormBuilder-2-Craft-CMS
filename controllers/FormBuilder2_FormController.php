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

    $variables['title']       = 'FormBuilder2';
    $variables['formItems']   = $formItems;
    $variables['settings']    = $settings;
    $variables['plugin']      = $plugins;
    $variables['navigation']  = $this->navigation();

    return $this->renderTemplate('formbuilder2/forms/index', $variables);
  }


  /**
   * View/Edit Form
   *
   */
  public function actionEditForm(array $variables = array())
  {
    $variables['brandNewForm'] = false;
    $variables['navigation']  = $this->navigation();

    if (!empty($variables['formId'])) {
      if (empty($variables['form'])) {
        $variables['form'] = craft()->formBuilder2_form->getFormById($variables['formId']);
        // $variables['crumbs'] = array(
        //   array('label' => Craft::t('FormBuilder 2'), 'url' => UrlHelper::getUrl('formbuilder2')),
        //   array('label' => Craft::t('Forms'),   'url' => UrlHelper::getUrl('formbuilder2/forms')),
        //   array('label' => $variables['formId'],  'url' => UrlHelper::getUrl('formbuilder2/forms/' . $variables['formId'] . '/edit')),
        // );

        if (!$variables['form']) { 
          throw new HttpException(404);
        }
        // Get Logo Asset
        $customEmailLogo = $variables['form']->notificationSettings['templateSettings']['emailCustomLogo'];
        if ($customEmailLogo) {
          $criteria           = craft()->elements->getCriteria(ElementType::Asset);
          $criteria->id       = $customEmailLogo[0];
          $criteria->limit    = 1;
          $elements           = $criteria->find();
        } else {
          $elements = [];
        }
        $variables['elements']  = $elements;
      }
      $variables['title'] = $variables['form']->name;
    } else {
      if (empty($variables['form'])) {
        $variables['form'] = new FormBuilder2_FormModel();
        $variables['brandNewForm'] = true;
        // $variables['crumbs'] = array(
        //   array('label' => Craft::t('FormBuilder 2'), 'url' => UrlHelper::getUrl('formbuilder2')),
        //   array('label' => Craft::t('Forms'),   'url' => UrlHelper::getUrl('formbuilder2/forms')),
        //   array('label' => Craft::t('New Form'),  'url' => UrlHelper::getUrl('formbuilder2/forms/new')),
        // );
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
    $form->fieldLayoutId                = craft()->request->getPost('fieldLayoutId');
    $form->formSettings                 = craft()->request->getPost('formSettings');
    $form->spamProtectionSettings       = craft()->request->getPost('spamProtectionSettings');
    $form->messageSettings              = craft()->request->getPost('messageSettings');
    $form->notificationSettings         = craft()->request->getPost('notificationSettings');

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

  /**
   * Sidebar Navigation
   *
   */
  public function navigation()
  {
    $navigationSections = [
      [
        'heading' => Craft::t('Menu'),
        'nav'     => [
          [
            'label' => Craft::t('Dashboard'),
            'icon'  => 'tachometer',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2'),
          ],
          [
            'label' => Craft::t('Forms'),
            'icon'  => 'list-alt',
            'extra' => craft()->formBuilder2_form->getTotalForms(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms'),
          ],
          [
            'label' => Craft::t('Entries'),
            'icon'  => 'file-text-o',
            'extra' => craft()->formBuilder2_entry->getTotalEntries(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/entries'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Quick Links'),
        'nav'     => [
          [
            'label' => Craft::t('Create New Form'),
            'icon'  => 'pencil-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ],
        ]
      ],
      [
        'heading' => Craft::t('Tools'),
        'nav'     => [
          [
            'label' => Craft::t('Export'),
            'icon'  => 'share-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/export'),
          ],
          [
            'label' => Craft::t('Configuration'),
            'icon'  => 'sliders',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/configuration'),
          ],
        ]
      ],
    ];
    return $navigationSections;
  }
}
