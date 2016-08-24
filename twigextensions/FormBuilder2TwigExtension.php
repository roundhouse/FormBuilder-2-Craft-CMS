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
     'camelCase' => new Twig_Filter_Method($this, 'camelCase'),
     'uncamelCase' => new Twig_Filter_Method($this, 'uncamelCase'),
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

  public function camelCase($str) {
    $i = array("-","_");
    $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
    $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
    $str = str_replace($i, ' ', $str);
    $str = str_replace(' ', '', ucwords(strtolower($str)));
    $str = strtolower(substr($str,0,1)).substr($str,1);
    return $str;
  }

  public function uncamelCase($str) {
    $str = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $str);
    $str = strtolower($str);
    return $str;
  }
}