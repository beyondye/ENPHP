<?php

namespace system;

class Locale
{

   static function lang(string $lang = LANG)
   {
      static $var = '';

      if ($var == '') {
         $var = $lang;
      }

      return $var;
   }


   static function load(string $file)
   {
      $lang = self::lang();
      $path = SYSTEM_PATH . 'lang' . DS . $lang . DS . $file . '.php';

      if (is_file($path)) {
         return include $path;
      }

      return [];
   } 


static function sys(string $key)
{
   $lang = self::load('validator');
   return $lang[$key] ?? $key;
}

}