<?php
 function smarty_modifiercompiler_count_paragraphs($params, $compiler) { return '(preg_match_all(\'#[\r\n]+#\', ' . $params[0] . ', $tmp)+1)'; } ?>