<?php
namespace Craft;
class FormBuilder2ElementType extends BaseElementType
{
  /**
   * Get ElementType Name
   *
   */
  public function getName()
  {
    return Craft::t('FormBuilder2');
  }

  /**
   * Get Sources
   *
   */
  public function getSources($context = null)
  {
    $sources = array(
      '*' => array(
        'label' => Craft::t('All Submissons'),
      ),
    );
    foreach (craft()->formBuilder2_form->getAllForms() as $form) {
      $key = 'formId:' . $form->id;
      $sources[$key] = array(
        'label'    => $form->name,
        'criteria' => array('formId' => $form->id)
      );
    }
    return $sources;
  }

  /**
   * Define Table Attributes
   *
   */
  public function defineTableAttributes($source = null)
  {
    return array(
      'id'          => Craft::t('ID'),
      'title'       => Craft::t('Form'),
      'dateCreated' => Craft::t('Date'),
      'submission'  => Craft::t('Submission Data'),
      'files'       => Craft::t('Uploads'),
    );
  }

  /**
   * Get Tablet Attribute HTML
   *
   */
  public function getTableAttributeHtml(BaseElementModel $element, $attribute)
  {
    switch ($attribute) {
      case 'submission':
        $data = $element->viewEntryLinkOnElementsTable();
        return $element->submission;
        break;
      case 'files':
        $files = $element->normalizeFilesForElementsTable();
        return $element->files;
      break;
      default:
        return parent::getTableAttributeHtml($element, $attribute);
        break;
    }
  }

  /**
   * Define Criteria Attributes
   *
   */
  public function defineCriteriaAttributes()
  {
    return array(
      'formId' => AttributeType::Mixed,
      'order'  => array(AttributeType::String, 'default' => 'formbuilder2_entries.dateCreated desc'),
    );
  }

  /**
   * Modify Elements Query
   *
   */
  public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
  {
    $query
      ->addSelect('formbuilder2_entries.formId, formbuilder2_entries.title, formbuilder2_entries.submission')
      ->join('formbuilder2_entries formbuilder2_entries', 'formbuilder2_entries.id = elements.id');
    if ($criteria->formId) {
      $query->andWhere(DbHelper::parseParam('formbuilder2_entries.formId', $criteria->formId, $query->params));
    }
  }

  /**
   * Populate Element Model
   *
   */
  public function populateElementModel($row, $normalize = false)
  {
    $entry = FormBuilder2_EntryModel::populateModel($row);
    if ($normalize) {
      $entry = $entry->_normalizeDataForElementsTable();
    }
    return $entry;
  }
}