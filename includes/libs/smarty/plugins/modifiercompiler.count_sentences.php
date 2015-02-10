<?php
 function smarty_modifiercompiler_count_sentences($params, $compiler) { return 'preg_match_all("#\w[\.\?\!](\W|$)#S' . Smarty::$_UTF8_MODIFIER . '", ' . $params[0] . ', $tmp)'; } ?>