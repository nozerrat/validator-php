<?php

namespace Validator;

class Validator {
   /*
   |--------------------------------------------------------------------------
   | Validation Language Lines
   |--------------------------------------------------------------------------
   |
   | The following language lines contain the default error messages used by
   | the validator class. Some of these rules have multiple versions such
   | as the size rules. Feel free to tweak each of these messages here.
   |
   */

   public $messages = [];

   public $fails    = [];

   public $roles_created = [];

   public $sqlStatement = ["select","insert","update","delete","from","where","group","having","order","avg","count","sum","max","min","change","create ","drop ","abort","begin","cluster","commit","copy","declare","end","explain","fetch","grant","listen","load","lock","move","notify","reset","revoke","rollback","set","show","truncate","mysql","mysqladmin","mysqldump","explain","kill","lock","flush","unlock","script","function","echo","exec","passthru","xp_","xp_cmdshell","system","and","or",";","=","\-\-",];

   public function __construct($locale='es',$dir=null) {
      if($locale===false) return true;
      if ( defined( 'LOCALE' ) ) {
         $locale = $locale ? $locale : LOCALE;
      }
      $dir = $dir ? $dir.$locale."/" : __DIR__."/../lang/".$locale."/";
      $this->messages = (array) @$_SESSION[$dir];

      if (@!$_SESSION[$dir]) {
         $d = dir($dir);
         while (($entry = $d->read()) !== false) {
            if ($entry=='.' || $entry=='..' || $entry=='index.php' || $entry=='index.html') continue;
            if (is_file($dir.$entry)) {
               $this->messages = @$_SESSION[$dir] = array_merge($this->messages, include($dir.$entry));
            }
         }
         $d->close();
      }
   }

   /**
    * Prepara las validaciones
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param Array $data
    * @param Array $rules
    * @param Array $messages dafaul []
    * @param Bool $clean default true
    * @throws Exception
    * @return Object
    */
   public function make($data, $rules, $messages=[], $clean = true) {
      // parametros de los roles
      $param = [];
      $temp  = "";
      if($clean)
         $this->fails = null;

      foreach ($rules as $field => $rule) {
//    foreach ($data as $field => $value) {

         if(!isset($rules[$field])) continue;

         $value = @$data[$field];

         if(!is_array($value)) $value = trim($value);

         if(!is_array($rules[$field])) $rules[$field] = explode("|", $rules[$field]);

         foreach ($rules[$field] as $key_rol => $rol) {
            $rol = trim($rol);

            if (preg_match("/:/", $rol)) {
               if(preg_match("/regex/", $rol)) {
                  $temp_rol = explode(":", $rol);
                  $rol      = ($temp_rol[0]);
                  unset($temp_rol[0]);
                  $param[0]    = implode(':', @$temp_rol);
               } else{
                  $temp_rol = explode(":", $rol);
                  $rol      = ($temp_rol[0]);
                  unset($temp_rol[0]);
                  $param    = explode(",", implode(':', @$temp_rol));
               }
            }
            switch ($rol) {
               case 'accepted':
                  break;
               case 'active_url':
                  break;
               case 'after':
                  break;
               case 'alpha':
                  if ($value!='') {
                     // ASCII lATIN1
                     if (!preg_match('/^[a-zA-Zá-üÁ-Ü\s]*$/', $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'alpha_dash':
                  if ($value!='') {
                     // ASCII lATIN1
                     if (!preg_match("/^[0-9a-zA-Zá-üÁ-Ü\s_\-\.,\(\)]*$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'alpha_num':
                  if ($value!='') {
                     // ASCII lATIN1
                     if (!preg_match("/^[0-9a-zA-Zá-üÁ-Ü\s\.]*$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'array':
                  if ($value!='') {
                     if (!is_array($value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'before':break;
               case 'between':
                  //    "numeric"            "file"            "string"          "array"
                  if (@$param[0]=='' || !is_numeric(@$param[0]) || !is_numeric(@$param[1])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     $len = $value;
                     if (is_array($value)) {
                        $len = count($value);
                     }elseif (!is_numeric($value)) {
                        $len = strlen($value);
                     }

                     if ($len < @$param[0]) {
                        $fail_max = true;
                     }

                     if ($len > @$param[1]) {
                        $fail_min = true;
                     }

                     if (!count($param) || @$fail_max || @$fail_min ) {
                        if (is_array($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['array'])));
                        }elseif (!is_numeric($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['string'])));
                        }else{
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['numeric'])));
                        }

                        $temp = str_replace(":min", @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":max", @$param[1]?@$param[1]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'confirmed':break;
               case 'date':break;
               case 'date_format':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);

                  if ($value!='') {
                     $format = date_parse_from_format(@$param[0], $value);

                     if ($format['error_count']) {

                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":format", @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'different':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);

                  $d = [
                     $field => $value,
                  ];
                  $r = [
                     $field => implode('|', $rules[@$param[0]]),
                  ];

                  $Validator = new Validator;
                  $v = $Validator->make($d,$r,@$messages);

                  if (count($v->fails) || strtolower($value)==strtolower($data[@$param[0]])) {
                     if (count($v->fails)) {
                        $this->fails["messages"][$field]   = $v->fails["messages"][$field];
                        $this->fails["failed"]  [$field]   = $v->fails["failed"]  [$field];
                     }else{
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":other", @$param[0], $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'digits':
                  if (!is_numeric(@$param[0])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     if (!preg_match("/^[0-9]*$/", $value) || !count($param) || strlen($value) != @$param[0]) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":digits",    @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'digits_between':
                  if (!is_numeric(@$param[0]) || !is_numeric(@$param[1])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     $len = strlen($value);

                     if ($len < @$param[0]) $fail_max = true;

                     if ($len > @$param[1]) $fail_min = true;

                     if (@$fail_max || @$fail_min ) {

                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));

                        $temp = str_replace(":min", @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":max", @$param[1]?@$param[1]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'email':
                  if ($value!='') {
                     $value=filter_var($value, FILTER_SANITIZE_EMAIL);
                     if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'exists':break;
               case 'image':
                  if ($value!='') {
                     if (!preg_match("/.*(\.jpg)|(\.jpeg)|(\.png)|(\.bmp)|(\.gif)$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'in':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     if (!is_numeric(array_search(strtolower($value), array_map( function ($v){return strtolower($v);}, $param)))) {

                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":values", implode(',', @$param), $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'integer':
                  if ($value!='') {
                     if (!preg_match("/^[0-9]*$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'ip':
                  if ($value!='') {
                     if (!preg_match("/^([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'max':
                  //    "numeric"            "file"            "string"          "array"
                  if (!is_numeric(@$param[0])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     $len = $value;
                     if (is_array($value)) {
                        $len = count($value);
                     }elseif (is_string($value)) {
                        $len = strlen($value);
                     }

                     if (!count($param) || ($len > @$param[0])) {
                        if (is_array($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['array'])));
                        }elseif (!is_numeric($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['string'])));
                        }else{
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['numeric'])));
                        }

                        $temp = str_replace(":max"      , @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'mimes':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);

                  if ($value!='') {
                     $param = implode('|', $param);

                     if (!preg_match("/.*".$param."$/", $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":values", str_replace('|', ',', @$param), $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'min':
                  //    "numeric"            "file"            "string"          "array"
                  if (!is_numeric(@$param[0])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     $len = $value;
                     if (is_array($value)) {
                        $len = count($value);
                     }elseif (is_string($value)) {
                        $len = strlen($value);
                     }
                     
                     if (!count($param) || ($len < @$param[0])) {
                        if (is_array($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['array'])));
                        }elseif (!is_numeric($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['string'])));
                        }else{
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['numeric'])));
                        }

                        $temp = str_replace(":min"      , @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'not_in':
                  if (!@$param[0]) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     if (is_numeric(array_search(strtolower($value), array_map( function ($v){return strtolower($v);}, $param)))) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":values", implode(',', @$param), $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'numeric':
                  if ($value!='') {
                     if ( !is_numeric( str_replace( ',','.', $value ) ) ) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'regex':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     if (!preg_match($param[0], $value)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'required':
                  if ($value==="" || $value===false || $value===null) {
                     $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     $temp = str_replace(":data", $value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                  }
                  break;
               case 'required_if':
                  # syntax: required_if:field1,value1,field2,value2,...
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ((count(@$param)%2)!=0) throw new Exception(" Los valores del parametro del rol {$rol} deben ser de campo-valor. Ej. field1,value1,field2,value2,... ", 1);
                  $str = [];
                  foreach($param as $key => $v) {
                     if(($key%2)==0)
                        $d = $data[$v];
                     else
                        $str[] = "( '".trim($d)."' == '".trim($v)."' )";
                  }

                  $eval = null;
                  eval('$eval = '.join('||', $str).';');

                  if ( ($value==="" || $value===false || $value===null) && $eval ) {
                     $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     $temp = str_replace(":other", @$param[0], $temp);
                     $temp = str_replace(":values", @$param[1], $temp);
                     $temp = str_replace(":data", $value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                  }
                  break;
               case 'required_less':
                  # syntax: required_less:field1,value1,field2,value2,...
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ((count(@$param)%2)!=0) throw new Exception(" Los valores del parametro del rol {$rol} deben ser de campo-valor. Ej. field1,value1,field2,value2,... ", 1);
                  $str = [];
                  foreach($param as $key => $v) {
                     if(($key%2)==0)
                        $d = $data[$v];
                     else
                        $str[] = "( '".trim($d)."' != '".trim($v)."' )";
                  }

                  $eval = null;
                  eval('$eval = '.join('||', $str).';');

                  if ( ($value==="" || $value===false || $value===null) && $eval ) {
                     $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     $temp = str_replace(":other", @$param[0], $temp);
                     $temp = str_replace(":values", @$param[1], $temp);
                     $temp = str_replace(":data", $value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                  }
                  break;
               case 'required_with':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if (!is_numeric(array_search(strtolower($value), array_map( function ($v){return strtolower($v);}, $param)))) {
                     $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     $temp = str_replace(":values", implode(',', @$param), $temp);
                     $temp = str_replace(":data", $value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                  }
                  break;
               case 'required_without':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if (is_numeric(array_search(strtolower($value), array_map( function ($v){return strtolower($v);}, $param)))) {
                     $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     $temp = str_replace(":values", implode(',', @$param), $temp);
                     $temp = str_replace(":data", $value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                  }
                  break;
               case 'same':
                  if (@$param[0]=='') throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);

                  $d = [$field => $value,];
                  $r = [$field => implode('|', $rules[@$param[0]]),];

                  $Validator = new Validator;
                  $v = $Validator->make($d,$r,$messages);

                  if (count($v->fails) || strtolower($value)!=strtolower($data[@$param[0]])) {
                     if (count($v->fails)) {
                        $this->fails["messages"][$field]   = $v->fails["messages"][$field];
                        $this->fails["failed"]  [$field]   = $v->fails["failed"]  [$field];
                     }else{
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":other", @$param[0], $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'size':
                  //    "numeric"            "file"            "string"          "array"
                  if (@$param[0]=='' || !is_numeric(@$param[0])) throw new Exception(" El parametro del rol {$rol} es incorrecto ", 1);
                  if ($value!='') {
                     $len = $value;
                     if (is_array($value)) {
                        $len = count($value);
                     }elseif (!is_numeric($value)) {
                        $len = strlen($value);
                     }

                     if (!count($param) || ($len) != (@$param[0])) {
                        if (is_array($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['array'])));
                        }elseif (!is_numeric($value)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['string'])));
                        }else{
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol]['numeric'])));
                        }

                        $temp = str_replace(":size", @$param[0]?@$param[0]:0, $temp);
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'text':
                  if ($value!='') {
                     // ASCII lATIN1
                     // if ($value) {
                     //    $this->fails["messages"][$field][] = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                     //    $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     // }
                  }
                  break;
               case 'unique':break;
               case 'url':
                  if ($value!='') {
                     if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                        $temp = str_replace(":data", $value, $temp);

                        $this->fails["messages"][$field][] = $temp;
                        $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                     }
                  }
                  break;
               case 'sqlStatement':
                  if ($value!='') {
                     $strings = explode(' ',$value);
                     foreach($strings as $key => $string) {
                        if(in_array(strtolower($string), $this->sqlStatement)) {
                           $temp = str_replace(":attribute", $field, (@$messages[$rol] ? @$messages[$rol] : (@$messages[$field] ? @$messages[$field] : $this->messages[$rol])));
                           $temp = str_replace(":values", strtoupper($string), $temp);
                           $temp = str_replace(":data", $value, $temp);

                           $this->fails["messages"][$field][] = $temp;
                           $this->fails["failed"]  [$field][] = $rules[$field][$key_rol];
                           break;
                        }
                     }
                  }
                  break;
               default:
                  $error = false;
                  $message = null;
                  if(@$this->roles_created[$rol] instanceof closure || gettype(@$this->roles_created[$rol])==='object') { // 'rol' => function();
                     $error = $this->roles_created[$rol]($value, $param, $data, $this);
                  }
                  elseif(@$this->roles_created[$rol][0] instanceof closure || gettype(@$this->roles_created[$rol][0])==='object') { // 'rol' => [function()];
                     $error = $this->roles_created[$rol][0]($value, $param, $data, $this);
                  }
                  elseif(is_string(@$this->roles_created[$rol][0]) && (@$this->roles_created[$rol][1] instanceof closure || gettype(@$this->roles_created[$rol][1])==='object')) { // 'rol' => ['message',function()];
                     $message = $this->roles_created[$rol][0];
                     $error = $this->roles_created[$rol][1]($value, $param, $data, $this);
                  }

                  if(is_array($error)) {
                     $temp_error = $error;
                     $error   = @$temp_error['error'];
                     if(@$temp_error['message'])
                        $message = @$temp_error['message'];
                  }
                  if($error) {
                     $temp_value = is_array($value) ? 'Array' : $value;
                     $message = $message ? $message : @$messages[$rol];
                     $temp = str_replace(":attribute", $field, $message);
                     $temp = str_replace(":values", implode(',', @$param), $temp);
                     $temp = str_replace(":data", $temp_value, $temp);

                     $this->fails["messages"][$field][] = $temp;
                     $this->fails["failed"]  [$field][] = $rol;
                  }

                  break;
            }
         }
      }

      return $this;
   }

   /**
    * crea una regleta de validacion personalisada por el usuario
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param Array $roles
    * @param Boolean $error
    * @return Object
    ** Ejemplos:
    *
    * $validador = new Validator;
    *
    *******
    * $validador->create([
    *    'rol_email_1'=>function($value, $param, $data, $object){
    *       return $error = true;
    *    }
    *       ,'rol_email_1'=>function($value, $param, $data, $object){
    *       return ['error'=>true];
    *    }
    *       ,'rol_email_1'=>function($value, $param, $data, $object){
    *       return ['error'=>true, 'message'=>'message error'];
    *    }
    *       ,'rol_email_1'=>[function($value, $param, $data, $object){
    *       return $error = true;
    *    }]
    *       ,'rol_email_1'=>['message error', function($value, $param, $data, $object){
    *       return $error = true;
    *    }]
    *       ,'rol_email_1'=>[function($value, $param, $data, $object){
    *       return ['error'=>true];
    *    }]
    *       ,'rol_email_1'=>[function($value, $param, $data, $object){
    *       return ['error'=>true, 'message'=>'message error'];
    *    }]
    *       ,'rol_email_1'=>['message error', function($value, $param, $data, $object){
    *       return ['error'=>true, 'message'=>'message error overwrite'];
    *    }]
    * ]);
    *
    * $validador->make(
    *     ['field'=>'value']
    *    ,['field'=>'rol_email_1']
    *    ,['rol_email_1'=>'message error 1']
    * );
    *
    * foreach($validador->messages() as $name => $msj) {
    *    if($validador->has($name)) $error_validador[] = $msj; // obtenemos los mesajes de error
    * }
    *
    *
    **/
   public function create($roles,&$error=null) {
      $key_rol = array_keys($roles);
      if(is_string(@$key_rol[0]) && ( @$roles[$key_rol[0]] instanceof closure || gettype(@$roles[$key_rol[0]])==='object' )) {
         'is correct';
      }
      elseif(is_string(@$key_rol[0]) && (@$roles[$key_rol[0]][0] instanceof closure || gettype(@$roles[$key_rol[0]][0])==='object')) {
         'is correct';
      }
      elseif(is_string(@$key_rol[0]) && is_string(@$roles[$key_rol[0]][0]) && ( @$roles[$key_rol[0]][1] instanceof closure || gettype(@$roles[$key_rol[0]][1])==='object')) {
         'is correct';
      }
      elseif(is_array(@$roles[0])) {
         foreach($roles as $key => $rol) {
            $this->create($rol,$error);
         }
      }
      else {
         $error = true;
      }

      if($error) {
         $this->roles_created = [];
         return false;
      } else{
         $this->roles_created = $roles;
         return $this;
      }
   }

   /**
    * indica si tiene errores
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @return Boolean
    **/
   public function fails() {
      $ban = false;
      if (count(@$this->fails["failed"]) > 0 || count(@$this->fails["messages"]) > 0) {
         $ban = true;
      }
      return $ban;
   }

   /**
    * indica si no tiene errores
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @return Boolean
    **/
   public function passes() {
      $ban = false;
      if (count(@$this->fails["failed"]) == 0 && count(@$this->fails["messages"]) == 0) {
         $ban = true;
      }
      return $ban;
   }

   /**
    * retorna todos los mensages fallidos
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $format
    * @return Array
    **/
   public function messages($format='') {
      if ($format) {
         if (is_array(@$this->fails["messages"])) {
            foreach ($this->fails["messages"] as $field => $value) {
               $this->fails["messages"][$field] = array_map(function ($v) use ($format) {return str_replace(':message', $v, $format);}, $value);
            }
         }
      }
      return count(@$this->fails["messages"])?@$this->fails["messages"]:array();
   }

   /**
    * retorna todos los roles fallidos
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @return Array
    **/
   public function failed() {
      return count(@$this->fails["failed"])?@$this->fails["failed"]:array();
   }

   /**
    * retorna el primer mensage del campo indicado
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $field
    * @param String  $format
    * @return String
    **/
   public function first($field,$format='') {
      if ($format) {
         @$this->fails["messages"][$field][0] = str_replace(':message', @$this->fails["messages"][$field][0], $format);
      }
      return @$this->fails["messages"][$field][0]?@$this->fails["messages"][$field][0]:'';
   }

   /**
    * retorna todos mensages del campo indicado
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $field
    * @param String  $format
    * @return Array
    **/
   public function get($field,$format='') {
      if ($format) {
         if (is_array(@$this->fails["messages"])) {
            foreach ($this->fails["messages"] as $field => $value) {
               $this->fails["messages"][$field] = array_map(function ($v) use ($format) {return str_replace(':message', $v, $format);}, $value);
            }
         }
      }
      return count(@$this->fails["messages"][$field])?@$this->fails["messages"][$field]:array();
   }

   /**
    * aplica un mensaje de error
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $field
    * @param String  $msg
    * @return Array
    **/
   public function set($field, $msg) {
      @$this->fails['messages'][$field][] = str_replace(":attribute", $field, $msg);
      @$this->fails["failed"]  [$field][] = true;
   }

   /**
    * retorna todos mensages y todos los roles
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $format
    * @return Array
    **/
   public function all($format='') {
      if ($format) {
         if (is_array(@$this->fails["messages"])) {
            foreach ($this->fails["messages"] as $field => $value) {
               $this->fails["messages"][$field] = array_map(function ($v) use ($format) {return str_replace(':message', $v, $format);}, $value);
            }
         }
      }

      return count($this->fails)?$this->fails:array();
   }

   /**
    * indeca si un campo tiene mensage
    * @author Carlos Garcia <garlos.figueroa@gmail.com>
    * @param String  $field
    * @return Boolean
    **/
   public function has($field) {
      return count(@$this->fails["messages"][$field]) > 0;
   }

}

