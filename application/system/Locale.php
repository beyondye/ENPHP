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

   function data(callable $fun){



   }
}
