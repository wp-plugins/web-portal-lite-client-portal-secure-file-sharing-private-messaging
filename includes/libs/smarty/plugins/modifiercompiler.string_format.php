<?php
 function smarty_modifiercompiler_string_format($params, $compiler) { return 'sprintf(' . $params[1] . ',' . $params[0] . ')'; } ?>