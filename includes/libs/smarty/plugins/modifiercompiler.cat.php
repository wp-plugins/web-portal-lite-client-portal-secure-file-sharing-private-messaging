<?php
 function smarty_modifiercompiler_cat($params, $compiler) { return '('.implode(').(', $params).')'; } ?>