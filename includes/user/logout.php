<?php

$data['logout_url']  = $this->cc_get_logout_url();
$data['labels']['logout'] = __( 'LOGOUT', WPC_CLIENT_TEXT_DOMAIN );

$out2 =  $this->cc_getTemplateContent( 'wpc_client_logoutb', $data );

return do_shortcode( $out2 );
?>