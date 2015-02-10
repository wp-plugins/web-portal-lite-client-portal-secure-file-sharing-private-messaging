<?php
 function smarty_variablefilter_htmlspecialchars($source, $smarty) { return htmlspecialchars($source, ENT_QUOTES, Smarty::$_CHARSET); } ?>