<?php
namespace Craft;

class FormBuilder2_FormElementType extends BaseElementType
{
  //======================================================================
  // Get ElementType Name
  //======================================================================
  public function getName()
  {
    return Craft::t('FormBuilder2_Form');
  }

  public function defineSortableAttributes()
  {
    $attributes = array(
      'id'        => Craft::t('ID'),
      'name'      => Craft::t('Name'),
      'handle'    => Craft::t('Handle')
    );

    return $attributes;
  }

  /**
   * Define Table Attributes
   *
   */
  public function defineTableAttributes($source = null)
  {
    $attributes = array(
      'id'      => Craft::t('ID'),
      'name'    => Craft::t('Name'),
      'handle'  => Craft::t('Handle')
    );

    return $attributes;
  }




  /**
   * Define Tablet HTML
   *
   */
  public function getTableAttributeHtml(BaseElementModel $element, $attribute)
  {
    // First give plugins a chance to set this
    $pluginAttributeHtml = craft()->plugins->callFirst('getEntryTableAttributeHtml', array($element, $attribute), true);

    if ($pluginAttributeHtml !== null)
    {
      return $pluginAttributeHtml;
    }

    switch ($attribute)
    {
      case 'section':
      {
        return Craft::t($element->getSection()->name);
      }

      default:
      {
        return parent::getTableAttributeHtml($element, $attribute);
      }
    }
  }

  /**
   * @inheritDoc IElementType::defineCriteriaAttributes()
   *
   */
  public function defineCriteriaAttributes()
  {
    return array(
      'id'      => AttributeType::Mixed,
      'order'   => array(AttributeType::String, 'default' => 'formbuilder2_forms.dateCreated desc')
    );
  }


  /**
   * @inheritDoc IElementType::modifyElementsQuery()
   *
   */
  public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
  {
    $query
      ->addSelect('formbuilder2_forms.id, formbuilder2_forms.name, formbuilder2_forms.handle')
      ->join('formbuilder2_forms formbuilder2_forms', 'formbuilder2_forms.id = elements.id');

    if ($criteria->id) {
      $query->andWhere(DbHelper::parseParam('formbuilder2_forms.id', $criteria->id, $query->params));
    }
  }

  /**
   * @inheritDoc IElementType::populateElementModel()
   *
   */
  public function populateElementModel($row, $normalize = false)
  {
    $entry = FormBuilder2_EntryModel::populateModel($row);

    return $entry;
  }

}