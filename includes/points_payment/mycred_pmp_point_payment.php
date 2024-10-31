<?php

define("MYCRED_PMP_POINT_PAY", dirname(__FILE__));

//load payment gateway class
require_once(MYCRED_PMP_POINT_PAY . "/classes/class.mycred_pmp_point_payment.php");

add_filter( 'mycred_all_references', 'mycredpro_add_custom_references');

function mycredpro_add_custom_references( $list ) {

    $list['mycred_pmp_fee'] = __('Membership Fee', 'myCred_pmp');

    return $list;

}


function pmp_mycred_custom_schedules( $schedules ) {
    // add a 'weekly' schedule to the existing set
    $schedules['yearly'] = array(
        'interval' => 31536000,
        'display' => __('Once a Year')
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'pmp_mycred_custom_schedules' );