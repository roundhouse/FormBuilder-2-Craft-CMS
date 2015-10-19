<?php  
namespace Craft;

use Twig_Extension;  
use Twig_Filter_Method;

class FormBuilder2TwigExtension extends \Twig_Extension  
{
  public function getName() {
    Craft::t('AddSpace');
  }

  public function getFilters() {
    return array(
     'addSpace' => new Twig_Filter_Method($this, 'addSpace'),
     'replaceUnderscoreWithSpace' => new Twig_Filter_Method($this, 'replaceUnderscoreWithSpace'),
     'checkArray' => new Twig_Filter_Method($this, 'checkArray'),
    );
  }

  public function addSpace($string) {
    $addSpace = preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
    $fullString = ucfirst($addSpace);
    return $fullString;
  }

  public function replaceUnderscoreWithSpace($string) {
    $output = str_replace('_', ' ', $string);
    return $output;
  }

  public function checkArray($array) {
    $check = is_array($array);
    return $check;
  }
}