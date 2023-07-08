<?php

add_action( 'login_enqueue_scripts',  function() { ?>
  <style type="text/css">
    #login h1 a, .login h1 a {
      background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png);
      background-position: center;
      background-color: #034663;
      background-size: 80%;
      border-radius: 5px;
    }
    .login #nav { display: none; }
  </style>
<?php } );

add_action( 'admin_enqueue_scripts', function() {
  wp_enqueue_style( 'admin_css', get_stylesheet_directory_uri() . '/style-admin.css');
});

add_filter('sanitize_option_fcc_pages', 'serialize');

add_action('admin_init', function() {
  register_setting('reading', 'fcc_pages');
  add_settings_field(
    'fcc_press_freedom_page', // ID
    'Press Freedom Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[press_freedom]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['press_freedom'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_statements', // ID
    'FCC Statements', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[fcc_statements]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['fcc_statements'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_members_area_page', // ID
    'Members Area Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[members_area]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['members_area'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_news_page', // ID
    'News Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[news]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['news'],
    ]);},
    'reading',
  );
  add_settings_field(
    'fcc_dining_page', // ID
    'Dining Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[dining]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['dining'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_shop_page', // ID
    'Shop Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[shop]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['shop'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_directory_page', // ID
    'Correspondents & Journalists Directory Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[directory]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['directory'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_gallery_page', // ID
    'Gallery Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[gallery]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['gallery'],
    ]);},
    'reading',
  ); 
  add_settings_field(
    'fcc_correspondent_page', // ID
    'The Correspondent Page', // Title 
    function() { echo wp_dropdown_pages([
      'name'              => 'fcc_pages[correspondent]',
      'echo'              => 0,
      'show_option_none'  => __( '&mdash; Select &mdash;' ),
      'option_none_value' => '0',
      'selected'          => unserialize(get_option( 'fcc_pages' ))['correspondent'],
    ]);},
    'reading',
  ); 
});

add_filter('manage_edit-gallery_columns', function ($columns) {
  $new = [];
  foreach($columns as $key => $title) {
    if ($key == 'categories') {
      $new['shortcode'] = 'Shortcode';
    }
    $new[$key] = $title;
  }

  return $new;
}, 13);
add_filter('manage_gallery_posts_custom_column', function($column_name, $post_ID) {
  if ($column_name == 'shortcode') {
    echo "[gallery id='$post_ID']";
  }
}, 10, 2);

add_filter('manage_dining_request_posts_columns', function($defaults) {
  unset($defaults['date']);
  unset($defaults['title']);
  $defaults['member'] = 'Member';
  $defaults['_date'] = 'Date';
  $defaults['venue'] = 'Venue';
  $defaults['adults'] = 'Adults';
  $defaults['children'] = 'Children';
  $defaults['email'] = 'Email';
  $defaults['phone'] = 'Phone';
  $defaults['_comments'] = 'Comments';
  $defaults['status'] = 'Status';

  return $defaults;
});
add_action('manage_dining_request_posts_custom_column', function($column_name, $post_ID) {
  switch ($column_name) {
    case 'member':
      $author_id = get_post_field ('post_author', $post_ID);
      $user = get_userdata($author_id);
      $numbers = getCardMemberNumbers($user);
      echo "$user->display_name ($numbers[1])"; break;
    case '_date':
      $date = strtotime(get_post_meta($post_ID, "date", true));
      echo date("d/m/Y H:i", $date); 
      break;
    case 'venue':
      echo get_the_title($post_ID); break;
    case 'adults':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['adults'];
      break;
    case 'children':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['children'];
      break;
    case 'email':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['email'];
      break;
    case 'phone':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['phone'];
      break;
    case '_comments':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['comments'];
      break;
    case 'status':
      $status = get_post_meta($post_ID, "status", true);
      if ($status == 'Pending') {
        $nonce = wp_create_nonce( 'request_action' ); 
        echo "<a href='" . admin_url( "post.php?post={$post_ID}&action=confirm&_wpnonce=$nonce" ) . "' class='button-primary'>Confirm</a> <a href='" . admin_url( "post.php?post={$post_ID}&action=reject&_wpnonce=$nonce" ) . "' class='button-primary'>Reject</a>";
      } else {
        echo $status;
      }
      break;
  }
}, 10, 2);
add_action( 'post_action_confirm', function($id) {
  requestStatus($id, 'Confirmed');
});
add_action( 'post_action_reject', function($id) {
  requestStatus($id, 'Reject');
});

function requestStatus($id, $status) {
  if (wp_verify_nonce( $_REQUEST['_wpnonce'], 'request_action' )) {
    update_post_meta($id, 'status', $status);

    $request = get_post($id);
    $user = get_user_by('id', $request->post_author);
    $message = "Dear {$user->display_name},<br/>
      <br/>
      Your request status has been updated.<br/>
      You may check the current status in <a href='". get_site_url() ."/members-area#/app/activities'>My Bookings</a><br/>";
    send_email(get_user_meta($user->ID, 'email'), 'Request Status Updated', $message);
  
    wp_redirect( wp_get_referer() );
		exit;
  }
}

add_filter('manage_private_request_posts_columns', function($defaults) {
  unset($defaults['date']);
  unset($defaults['title']);
  $defaults['member'] = 'Member';
  $defaults['_date'] = 'Date';
  // $defaults['date2'] = 'Date 2';
  $defaults['duration'] = 'Duration';
  $defaults['venue'] = 'Venue';
  $defaults['type'] = 'Type';
  $defaults['attendees'] = 'Attendees';
  $defaults['email'] = 'Email';
  $defaults['phone'] = 'Phone';
  $defaults['setup'] = 'Setup';
  $defaults['_comments'] = 'Comments';
  $defaults['status'] = 'Status';

  return $defaults;
});
add_action('manage_private_request_posts_custom_column', function($column_name, $post_ID) {
  global $wpdb;

  switch ($column_name) {
    case 'member':
      $author_id = get_post_field ('post_author', $post_ID);
      $user = get_userdata($author_id);
      $numbers = getCardMemberNumbers($user);
      echo "$user->display_name ($numbers[1])"; break;
    case '_date':
      $date = strtotime(get_post_meta($post_ID, "date", true));
      echo date("d/m/Y H:i", $date); break;
    // case 'date2':
    //   $date = strtotime(get_post_meta($post_ID, "date2", true));
    //   echo date("d/m/Y H:i", $date); break;
    case 'duration':
      echo (strtotime(get_post_meta($post_ID, "end", true)) - strtotime(get_post_meta($post_ID, 'date', true))) / 60 / 60; break;
    case 'venue':
      echo get_the_title($post_ID); break;
    case 'type':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['type'];
      break;
    case 'attendees':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['attendees'];
      break;
    case 'email':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['email'];
      break;
    case 'phone':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['phone'];
      break;
    case 'setup':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['setup'];
      break;
    case '_comments':
      $data = unserialize(get_the_content(null, false, $post_ID));
      echo $data['comments'];
      break;
    case 'status':
      $status = get_post_meta($post_ID, "status", true);
      if ($status == 'Pending') {
        $nonce = wp_create_nonce( 'request_action' ); 
        echo "
        <a href='" . admin_url( "post.php?post={$post_ID}&action=confirm1&_wpnonce=$nonce" ) . "' class='button-primary'>Confirm</a> " . 
        // "<a href='" . admin_url( "post.php?post={$post_ID}&action=confirm2&_wpnonce=$nonce" ) . "' class='button-primary'>Confirm Date 2</a>" .
        "<a href='" . admin_url( "post.php?post={$post_ID}&action=reject&_wpnonce=$nonce" ) . "' class='button-primary'>Reject</a>";
      } else {
        echo $status;
      }
      break;
  }
}, 10, 2);
add_action( 'post_action_confirm1', function($id) {
  requestStatus($id, 'Confirmed');
});
// add_action( 'post_action_confirm2', function($id) {
//   requestStatus($id, 'Confirmed Date 2');
// });

add_filter('wp_handle_sideload_prefilter', function($file) {
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  if (!$ext)
    $file['name'] .= '.jpg';
  return $file;
});

add_filter( 'woocommerce_checkout_fields' , function( $fields ) { 
  $fields['shipping']['shipping_delivery_date'] = [ 
    'label' => __('Delivery date', 'woocommerce'), 
    'placeholder' => _x('Delivery date', 'placeholder', 'woocommerce'), 
    'required' => true, 
    'class' => ['form-row-wide'], 
    'clear' => true 
  ];
  $fields['shipping']['shipping_pickup_date'] = [ 
    'label' => __('Pickup date', 'woocommerce'), 
    'placeholder' => _x('Pickup date', 'placeholder', 'woocommerce'), 
    'required' => true, 
    'class' => ['form-row-wide'], 
    'clear' => true 
  ];

  return $fields; 
});
add_filter( 'woocommerce_admin_shipping_fields', function($fields) {
  $fields['delivery_date'] = [
    'label' => __('Delivery date', 'woocommerce'),
    'show' => true,
  ];
  $fields['pickup_date'] = [
    'label' => __('Pickup date', 'woocommerce'),
    'show' => true,
  ];
  return $fields;
});

acf_add_options_page([
  'page_title' 	=> 'FCC Settings',
  'menu_title'	=> 'FCC Settings',
  'menu_slug' 	=> 'fcc-settings',
  'capability'	=> 'edit_posts',
  'redirect'		=> false
]);
add_action('acf/init', function () {
  acf_add_local_field_group([
    'key' => 'group_crosssell',
    'title' => 'E-Shop',
    'fields' => [
      [
        'key' => 'field_crosssell',
        'label' => 'Cross-Sell Products',
        'name' => 'crosssell',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'product'
        ),
        'post_status' => ['publish'],
        'multiple' => 1,
        'return_format' => 'id',
        'ui' => 1,
      ],
      [
        'key' => 'field_shop_banner',
        'label' => 'Shop Home Banner',
        'name' => 'shop_banner',
        'type' => 'repeater',
        'sub_fields' => [
          [
            'key' => 'field_shop_banner_product',
            'label' => 'Product',
            'name' => 'product',
            'type' => 'post_object',
            'post_type' => array(
              0 => 'product'
            ),
            'post_status' => ['publish'],
            'multiple' => 0,
            'return_format' => 'id',
            'ui' => 1,
          ],
          [
            'key' => 'field_shop_banner_bg',
            'label' => 'Background',
            'name' => 'bg',
            'type' => 'image',
          ],
          [
            'key' => 'field_shop_banner_text',
            'label' => 'Text',
            'name' => 'text',
            'type' => 'text',
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'fcc-settings',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'group_notifications',
    'title' => 'Notifications',
    'fields' => [
      [
        'key' => 'field_dining',
        'label' => 'Dining Requests Emails',
        'instructions' => 'Comma-separated',
        'name' => 'dining_email',
        'type' => 'text',
      ],
      [
        'key' => 'field_dining_message',
        'label' => 'Dining Request Message',
        'name' => 'dining_message',
        'type' => 'wysiwyg',
      ],
      [
        'key' => 'field_private',
        'label' => 'Private Function Requests Emails',
        'instructions' => 'Comma-separated',
        'name' => 'private_email',
        'type' => 'text',
      ],
      [
        'key' => 'field_private_message',
        'label' => 'Private Function Request Message',
        'name' => 'private_message',
        'type' => 'wysiwyg',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'fcc-settings',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'dining_blocked',
    'title' => 'Dining Blocked Slots',
    'fields' => [
      [
        'key' => 'field_slots',
        'label' => 'Slots',
        'name' => 'blocked_slots',
        'type' => 'repeater',
        'sub_fields' => [
          [
            'key' => 'field_slots_venue',
            'label' => 'Venue',
            'name' => 'venue',
            'type' => 'select',
            'choices' => [
              'Main Bar' => 'Main Bar',
              'Lounge' => 'Lounge',
              'Dining Room' => 'Dining Room',
              'Verandah' => 'Verandah',
              'Bert\'s' => 'Bert\'s',
              'Burton Room' => 'Burton Room',
              'Hughes Room' => 'Hughes Room',
            ],
            'multiple' => true,
          ],
          [
            'key' => 'field_slots_start',
            'label' => 'Start Date and Time',
            'name' => 'start',
            'type' => 'date_time_picker',
            'display_format' => 'Y-m-d H:i',
            'return_format' => 'Y-m-d H:i',
          ],
          [
            'key' => 'field_slots_end',
            'label' => 'End Date and Time',
            'name' => 'end',
            'type' => 'date_time_picker',
            'display_format' => 'Y-m-d H:i',
            'return_format' => 'Y-m-d H:i',
          ],
        ]
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'fcc-settings',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'post_options',
    'title' => 'Options',
    'fields' => [
      [
        'key' => 'field_redirect_url',
        'label' => 'Redirect URL',
        'name' => 'redirect_url',
        'type' => 'text',
      ],
    ],
    'position' => 'side',
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'post',
        ],
      ],
    ],
  ]);
});

add_action( 'woocommerce_before_order_itemmeta', function($item_id, $item, $product) {
  global $post_id;
  $product_id = $product->id;
  $notes = wc_get_order_notes([
    'order_id' => $post_id,
    'type' => 'customer',
  ]);

  if (count($notes)) {
    $notes = json_decode($notes[0]->content);

    if ($notes->$product_id)
      echo "<br/>Note: " . $notes->$product_id;
  }
}, 10, 3);

add_action('woocommerce_admin_order_items_after_line_items', function($order_id) {
  $notes = wc_get_order_notes([
    'order_id' => $order_id,
    'type' => 'customer',
  ]);

  if (count($notes)) {
    $notes = json_decode($notes[0]->content);

    if ($notes[0])
      echo "<tr><td colspan='5'>Note: $notes[0] </td></tr>";
  }
});

add_action( 'customize_register', function ($wp_customize) {	
	$wp_customize->remove_section( 'custom_css' );
}, 15 );

add_filter('rest_pre_dispatch', function($request) {
  if (is_wp_error($request) && $request->get_error_code() == 'jwt_auth_bad_auth_header')
    return null;
  return $request;
}, 11, 2);

add_action('woocommerce_admin_order_data_after_billing_address', function($order) {
  $user = $order->get_user();
  $email = get_user_meta($user->ID, 'email', true);
  $numbers = getCardMemberNumbers($user);
  echo "<p><strong>Member email:</strong><br/>$email</p>";
  echo "<p><strong>Member number:</strong><br/>$numbers[1]</p>";
  echo "<p><strong>Card number:</strong><br/>$numbers[0]</p>";
});