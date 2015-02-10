<?php
 function smarty_modifier_spacify($string, $spacify_char = ' ') { return implode($spacify_char, preg_split('//' . Smarty::$_UTF8_MODIFIER, $string, -1, PREG_SPLIT_NO_EMPTY)); } ?>