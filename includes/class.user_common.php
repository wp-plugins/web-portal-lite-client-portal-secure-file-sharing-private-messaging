<?php

if ( !class_exists( "WPC_Client_User_Common" ) ) {

    class WPC_Client_User_Common extends WPC_Client_Common {

        var $current_plugin_page;

        /**
        * constructor
        **/
        function user__common_construct() {


        }

        /*
        * Get portal pages ids for client
        */
        function ucc_get_portalpages_ids_for_client( $user_id, $category, $show_current_page = '' ) {
            global $wpdb;

            if ( false === $user_id || 0 == $user_id ) {
                return array();
            }

            $mypages_id = array();
            if( isset( $category ) && is_array( $category ) ) {
                $category_id = $category['cat_id'];

                $users_category = $this->cc_get_assign_data_by_object( 'portal_page_category', $category_id, 'client' );
                $groups_category = $this->cc_get_assign_data_by_object( 'portal_page_category', $category_id, 'circle' );

                //get all clients to category from Client Circles & Clients
                if ( is_array( $groups_category ) && 0 < count( $groups_category ) )
                    foreach( $groups_category as $group_id ) {
                        $users_category = array_merge( $users_category, $this->cc_get_group_clients_id( $group_id ) );
                    }


                //get clientpages by category_id
                if( in_array( $user_id, $users_category ) ) {
                    $sql = "
                            SELECT $wpdb->posts.ID FROM $wpdb->posts
                            INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                            WHERE
                            $show_current_page
                            $wpdb->posts.post_type = 'clientspage' AND
                            $wpdb->posts.post_status = 'publish' AND (
                            $wpdb->postmeta.meta_key = '_wpc_category_id' AND
                            $wpdb->postmeta.meta_value = " . $category['cat_id'] . " )
                    ";
                    $mypages_id = $wpdb->get_col( $sql );
                }
                /*var_dump($mypages_id);
                //get clientpages by user_ids
                $mypages_id2 = $this->cc_get_assign_data_by_assign( 'portal_page', 'client', $user_id );
                $mypages_id = array_merge( $mypages_id, $mypages_id2 );

                //get clientpages by groups_id
                $client_groups_id = $this->cc_get_client_groups_id( $user_id );

                if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {

                    foreach ( $client_groups_id as $groups_id )  {
                        $mypages_id3 = $this->cc_get_assign_data_by_assign( 'portal_page', 'circle', $groups_id );
                        $mypages_id = array_merge( $mypages_id, $mypages_id3 );
                    }

                } */

            } elseif( isset( $category ) && 0 == $category ) {

                //get clientpages by user_ids

                $mypages_id2 = $this->cc_get_assign_data_by_assign( 'portal_page', 'client', $user_id );
                $mypages_id = array_merge( $mypages_id, $mypages_id2 );


                //get clientpages by groups_id
                $client_groups_id = $this->cc_get_client_groups_id( $user_id );

                if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {

                    foreach ( $client_groups_id as $groups_id )  {

                        $mypages_id3 = $this->cc_get_assign_data_by_assign( 'portal_page', 'circle', $groups_id );
                        $mypages_id = array_merge( $mypages_id, $mypages_id3 );
                    }

                }
            }
            return array_unique( $mypages_id );
        }



        /*
        * Sort Prtal pages
        */
        function ucc_sort_portalpages_for_client( $mypages_id, $sort_type = '', $sort = '' ) {
            //sorting
            if ( isset( $sort_type ) && 'date' == strtolower( $sort_type ) ) {
                //by date
                if ( isset( $sort ) && 'desc' == strtolower( $sort ) )
                    rsort( $mypages_id );
                else
                    sort( $mypages_id );
            } elseif (  isset( $sort_type ) && 'title' == strtolower( $sort_type ) ) {
                //by alphabetical
                if ( is_array( $mypages_id ) && $mypages_id ) {

                    foreach( $mypages_id as $page_id ) {
                        $mypage = get_post( $page_id, 'ARRAY_A' );
                        $for_sort[$page_id] = strtolower( nl2br( $mypage['post_title'] ) );
                    }

                    if ( isset( $sort ) && 'desc' == strtolower( $sort ) )
                        arsort( $for_sort );
                    else
                        asort( $for_sort );

                    $mypages_id = array_keys( $for_sort );
                }
            }
            return $mypages_id;
        }















    //end class
    }
}

?>