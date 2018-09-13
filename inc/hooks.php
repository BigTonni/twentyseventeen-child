<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/* 
 * Hooks
 */
if( is_admin() && class_exists( 'WooCommerce' ) ){
    add_filter( 'woocommerce_shipping_settings', 'tsc_shipping_settings' );
}

/*
 * Callbacks
 */

//Settings
function tsc_shipping_settings($args){

    $new_args = array( 
        array(
            'desc'          => "Charge shipping for each shipping zone individually",
            'id'            => "woocommerce_shipping_individually_zone",
            'default'       => "no",
            'type'          => "checkbox",
            'checkboxgroup' => "start",
            'autoload'      => false
        )
    );    
    array_splice($args, 3, 0, $new_args);
    
    return $args;
}

// Update shipping packages if option "Separate rows" checked in adminpanel (#Jule 3, 2017)
add_filter('woocommerce_shipping_packages', 'tsc_shipping_packages');
function tsc_shipping_packages( $packages ){

        if( get_option( 'woocommerce_shipping_individually_zone' ) == 'yes' ){
           
                $shipping_methods = array();
       
                $debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );
                
                foreach ($packages as $key => $package) {                    
   
                        // Check if we need to recalculate shipping for this package
                        $package_to_hash = $package;
                        // Remove data objects so hashes are consistent
                        foreach ( $package_to_hash['contents'] as $item_id => $item ) {
                                unset( $package_to_hash['contents'][ $item_id ]['data'] );
                        }

                        $package_hash = 'wc_ship_' . md5( json_encode( $package_to_hash ) . WC_Cache_Helper::get_transient_version( 'shipping' ) );
                   
                        $session_key  = 'shipping_for_package_' . $key;
                        $stored_rates = WC()->session->get( $session_key );
                        
                        if( ! is_array( $stored_rates ) || $package_hash !== $stored_rates['package_hash'] ){
                            
                                $new_package['rates'] = array();

                                $shipping_zone_ids = tsc_get_zone_ids_from_packages( $package );

                                if( !empty($shipping_zone_ids) ){
                                        foreach ($shipping_zone_ids as $k_zone => $matching_zone_id) {
                                                $zone_id = $matching_zone_id['zone_id'] ? $matching_zone_id['zone_id'] : 0;
                                                $shipping_zone = new WC_Shipping_Zone( $zone_id );
                                                $shipping_methods[$zone_id][] = $shipping_zone->get_shipping_methods( true );
           
                                                if ( $debug_mode && ! wc_has_notice( 'Customer matched zone "' . $shipping_zone->get_zone_name() . '"' ) ) {
                                                        wc_add_notice( 'Customer matched zone "' . $shipping_zone->get_zone_name() . '"' );
                                                }
                                        }
                                        foreach ($shipping_methods as $k_out => $arr_shipping_methods) {
                                                foreach ($arr_shipping_methods[0] as $k_in => $shipping_method) {
                                                        if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() ) {
                                                                $new_package['rates'] = $new_package['rates'] + $shipping_method->get_rates_for_package( $package );
                                                        }
                                                }
                                        }
                                }

                                $package['rates'] = !empty($new_package['rates']) ? $new_package['rates'] : $package['rates'];
                                

                                WC()->session->set( $session_key, array(
                                        'package_hash' => $package_hash,
                                        'rates'        => $package['rates'],
                                ) );
                        }

                        $packages[$key]['rates'] = $package['rates'];
                }
        }else{
            //default code... 
        }

        return $packages;
}