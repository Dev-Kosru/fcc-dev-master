<?
add_action( 'init', function() {
  register_post_status( 'wc-ready', array(
    'label'                     => 'Ready for Pick-Up',
    'public'                    => true,
    'exclude_from_search'       => false,
    'show_in_admin_all_list'    => true,
    'show_in_admin_status_list' => true,
    'label_count'               => _n_noop( 'Ready for Pick-Up (%s)', 'Ready for Pick-Up (%s)' )
  ) );
});

add_filter( 'wc_order_statuses', function( $order_statuses ) {
  $new_order_statuses = array();
  // add new order status after processing
  foreach ( $order_statuses as $key => $status ) {
    $new_order_statuses[ $key ] = $status;

    if ( 'wc-processing' === $key ) {
      $new_order_statuses['wc-ready'] = 'Ready for Pick-Up';
    }
  }

  return $new_order_statuses;
});