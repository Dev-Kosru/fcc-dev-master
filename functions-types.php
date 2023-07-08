<?php
add_action('init', function() {
  register_taxonomy('event-categories', 'event', [
    'hierarchical' => true
  ]);
  register_taxonomy('product_promo', 'product', [
    'labels' => ['name' => 'Product Promotions'],
    'hierarchical' => true,
  ]);
  register_taxonomy('product_selection', 'product', [
    'labels' => ['name' => 'Product Selections'],
    'hierarchical' => true,
  ]);
  register_taxonomy('product_supplier', 'product', [
    'labels' => ['name' => 'Suppliers'],
    'hierarchical' => true,
  ]);
  register_taxonomy('magazine-issue', 'correspondent', [
    'labels' => ['name' => 'Magazine Issues'],
  ]);
  register_taxonomy('magazine-post-type', 'correspondent', [
    'labels' => ['name' => 'Magazine Post Types'],
    'hierarchical' => true,
    ]);
  register_taxonomy('year-of-board', 'board-member', [
    'labels' => ['name' => 'Years of Board'],
    'hierarchical' => true,
  ]);
  register_taxonomy('board-title', 'board-member', [
    'labels' => ['name' => 'Board Titles'],
    'hierarchical' => true,
  ]);
  register_post_type('event', [
    'label' => 'Events',
    'public' => true,
    'supports' => array('title', 'editor', 'page-attributes', 'thumbnail'),
    'has_archive' => false,
    'taxonomies' => ['event-categories'],
    'menu_icon' => 'dashicons-calendar-alt',
    // 'capability_type' => 'event',
    'map_meta_cap' => true
  ]);
  
  register_post_type('gallery', array(
    'label' => 'Galleries',
    'public' => true,
    'hierarchical' => true,
    'supports' => array('title', 'editor', 'page-attributes', 'thumbnail'),
    'has_archive' => 'galleries',
    'menu_icon' => 'dashicons-images-alt2',
    // 'capability_type' => 'promotion',
    'map_meta_cap' => true
  ));
  
  register_post_type( 'dining_request', array(
    'label' => 'Dining Requests',
    'public' => true,
    'map_meta_cap' => false,
    'menu_icon' => 'dashicons-list-view',
    'capability_type' => 'dining_request',
    'capabilities' => array(
	    'create_posts' => 'create_dining_request',
    ),
    'exclude_from_search' => true,
  ));
  
  register_post_type( 'private_request', array(
    'label' => 'Private Function Requests',
    'public' => true,
    'map_meta_cap' => false,
    'menu_icon' => 'dashicons-list-view',
    'capability_type' => 'private_request',
    'capabilities' => array(
	    'create_posts' => 'create_private_request',
    ),
    'exclude_from_search' => true,
  ));
  
  register_post_type( 'directory', array(
    'label' => 'Corr. Directory',
    'public' => true,
    'has_archive' => 'directory',
    'menu_icon' => 'dashicons-groups',
    'map_meta_cap' => true,
    // 'capability_type' => 'directory',
    'supports' => ['title'],
    'exclude_from_search' => true,
  ));
  
  register_post_type( 'partner-club', array(
    'label' => 'Reciprocal Clubs',
    'public' => true,
    'has_archive' => 'partner-club',
    'menu_icon' => 'dashicons-groups',
    'map_meta_cap' => true,
    // 'capability_type' => 'partner-club',
    'supports' => ['title'],
    'exclude_from_search' => true,
  ));
  
  register_post_type( 'correspondent', array(
    'label' => 'The Correspondent',
    'public' => true,
    'has_archive' => 'correspondent',
    'menu_icon' => 'dashicons-book',
    'map_meta_cap' => true,
    // 'capability_type' => 'correspondent',
    'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
  ));
  
  register_post_type('board-member', array(
    'label' => 'Board Members',
    'public' => true,
    'has_archive' => false,
    'menu_icon' => 'dashicons-admin-users',
    'map_meta_cap' => true,
    // 'capability_type' => 'correspondent',
    'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
    'exclude_from_search' => true,
  ));
});

add_action('acf/init', function () {
  acf_add_local_field_group([
    'key' => 'event',
    'title' => 'Event Details',
    'fields' => [
      [
        'key' => 'event_start_local',
        'label' => 'From',
        'name' => '_event_start_local',
        'type' => 'date_time_picker',
      ],
      [
        'key' => 'event_end_local',
        'label' => 'to',
        'name' => '_event_end_local',
        'type' => 'date_time_picker',
      ],
      [
        'key' => 'event_all_day',
        'name' => '_event_all_day',
        'label' => 'All day',
        'type' => 'true_false',
      ],
      [
        'key' => 'event_venue',
        'label' => 'Venue',
        'name' => 'event_venue',
        'type' => 'select',
        'choices' => [
          'Main Bar & Lounge' => 'Main Bar & Lounge',
          'Dining Room' => 'Dining Room',
          'Verandah' => 'Verandah',
          'Bert\'s' => 'Bert\'s',
          'Burton Room' => 'Burton Room',
          'Hughes Room' => 'Hughes Room',
        ],
        'multiple' => true,
      ],
      [
        'key' => 'event_gallery',
        'label' => 'Event Gallery',
        'name' => 'event_gallery',
        'type' => 'gallery',
      ],
      [
        'key' => 'event_quota',
        'label' => 'Quota ',
        'name' => 'event_quota',
        'instructions' => 'if quota is 0 or empty, NO signup is required',
        'type' => 'number',
      ],
      [
        'key' => 'event_waitinglist',
        'label' => 'Waiting list',
        'name' => 'event_waitinglist',
        'type' => 'true_false',
      ],
      [
        'key' => 'event_guests',
        'label' => 'Guests (maximum per member)',
        'name' => 'event_guests',
        'instructions' => 'if guests number is 0 or empty, NO guests are allowed',
        'type' => 'number',
      ],
      [
        'key' => 'event_cutoff',
        'label' => 'Cutoff date & time',
        'name' => 'event_cutoff',
        'type' => 'date_time_picker',
      ],
      [
        'key' => 'event_message',
        'label' => 'Customized message',
        'name' => 'event_message',
        'type' => 'text',
      ],
      [
        'key' => 'event_meal',
        'label' => 'Meal options',
        'instructions' => 'One option per line',
        'name' => 'event_meal',
        'type' => 'textarea',
      ],
      [
        'key' => 'event_signups',
        'label' => 'Signups',
        'name' => 'event_signups',
        'type' => 'repeater',
        'sub_fields' => [
          [
            'key' => 'signup_member',
            'label' => 'Member',
            'name' => 'member',
            'type' => 'user',
            'return_format' => 'id',
          ],
          [
            'key' => 'signup_name',
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
          ],
          [
            'key' => 'signup_email',
            'label' => 'Email',
            'name' => 'email',
            'type' => 'text',
          ],
          [
            'key' => 'signup_meal',
            'label' => 'Meal',
            'name' => 'meal',
            'type' => 'text',
          ],
          [
            'key' => 'signup_waitinglist',
            'label' => 'Waitinglist',
            'name' => 'waitinglist',
            'type' => 'true_false',
          ],
        ]
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'event',
        ],
      ],
    ],
  ]);

  add_filter( "acf/pre_load_reference", function($reference, $field_name, $post_id) {
    if (in_array($field_name, ['_event_start_local', '_event_end_local', '_event_start_time', '_event_end_time']))
      return $field_name;
    return $reference;
  }, 10, 3);
  
  acf_add_local_field_group([
    'key' => 'promotion',
    'title' => 'Promotion details',
    'fields' => [
      [
        'key' => 'date',
        'label' => 'Start date & time',
        'name' => 'date',
        'type' => 'date_time_picker',
      ],
      [
        'key' => 'end',
        'label' => 'End date & time',
        'name' => 'end',
        'type' => 'date_time_picker',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'promotion',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'gallery',
    'title' => 'Gallery details',
    'fields' => [
      [
        'key' => 'gallery_images',
        'label' => 'Images',
        'name' => 'images',
        'type' => 'gallery',
      ],
      [
        'key' => 'gallery_hidden',
        'name' => 'hidden',
        'type' => 'checkbox',
        'choices' => [
          1 => 'Hide from the galleries page',
        ]
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'gallery',
        ],
      ],
    ],
  ]);
  acf_add_local_field_group([
    'key' => 'tax-gallery',
    'title' => 'Category Details',
    'fields' => [
      [
        'key' => 'gallery-thumb',
        'label' => 'Thumbnail',
        'name' => 'gallery-thumb',
        'type' => 'image',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'taxonomy',
          'operator' => '==',
          'value' => 'gallery',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'page',
    'title' => 'Page Settings',
    'fields' => [
      [
        'key' => 'page-settings-slider',
        'label' => 'Slider Images',
        'name' => 'page-settings-slider',
        'type' => 'gallery',
      ],
      [
        'key' => 'page-settings-breadcrumb',
        'label' => 'Show Breadcrumb',
        'name' => 'page-settings-breadcrumb',
        'type' => 'true_false',
      ],
      [
        'key' => 'page-settings-sidebar',
        'label' => 'Sidebar Block',
        'name' => 'page-settings-sidebar',
        'type' => 'textarea',
        'new_lines' => 'wpautop',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'product-category',
    'title' => 'Category Page',
    'fields' => [
      [
        'key' => 'product-category-banner',
        'label' => 'Banner Background',
        'name' => 'product-category-banner',
        'type' => 'image',
      ],
      [
        'key' => 'product-category-bg',
        'label' => 'Subcategories Background',
        'name' => 'product-category-bg',
        'type' => 'image',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'taxonomy',
          'operator' => '==',
          'value' => 'product_cat',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'product_selection',
    'title' => 'Selection Settings',
    'fields' => [
      [
        'key' => 'product-selection-image',
        'label' => 'Image',
        'name' => 'product-selection-image',
        'type' => 'image',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'taxonomy',
          'operator' => '==',
          'value' => 'product_selection',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'product-supplier',
    'title' => 'Supplier Details',
    'fields' => [
      [
        'key' => 'field-product-supplier-moq',
        'label' => 'Minimum Order Quantity',
        'name' => 'product-supplier-moq',
        'type' => 'number',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'taxonomy',
          'operator' => '==',
          'value' => 'product_supplier',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'partner-club',
    'title' => 'Club Information',
    'fields' => [
      [
        'key' => 'partner-club-wpcf-code',
        'label' => 'Code',
        'name' => 'wpcf-code',
        'type' => 'text',
      ],
      [
        'key' => 'partner-club-wpcf-country',
        'label' => 'Country',
        'name' => 'wpcf-country',
        'type' => 'text',
      ],
      [
        'key' => 'partner-club-wpcf-contact-information',
        'label' => 'Contact Information',
        'name' => 'wpcf-contact-information',
        'type' => 'wysiwyg',
      ],
      [
        'key' => 'partner-club-wpcf-contact-person',
        'label' => 'Contact person',
        'name' => 'wpcf-contact-person',
        'type' => 'wysiwyg',
      ],
      [
        'key' => 'partner-club-wpcf-introductory-card',
        'label' => 'Introductory Card',
        'name' => 'wpcf-introductory-card',
        'type' => 'text',
      ],
      [
        'key' => 'partner-club-wpcf-guest-allowed',
        'label' => 'Guest Allowed',
        'name' => 'wpcf-guest-allowed',
        'type' => 'text',
      ],
      [
        'key' => 'partner-club-wpcf-payment-methods',
        'label' => 'Payment Methods',
        'name' => 'wpcf-payment-methods',
        'type' => 'text',
      ],
      [
        'key' => 'partner-club-wpcf-facilities',
        'label' => 'Facilities',
        'name' => 'wpcf-facilities',
        'type' => 'checkbox',
        'choices' => [
          'dining' => 'Dining',
          'outdoor' => 'Outdoor Terrace Dining',
          'function' => 'Function Room',
          'conference' => 'Conference',
          'accommodation' => 'Accommodation',
          'parking' => 'Parking',
          'healthclub' => 'Health Club',
          'swimming' => 'Swimming Pool',
          'library' => 'Library',
          'workroom' => 'Workroom',
          'wifi' => 'WIFI',
          'internet' => 'Internet',
        ]
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'partner-club',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'directory',
    'title' => 'Personal Details',
    'fields' => [
      [
        'key' => 'dir_email',
        'label' => 'E-mail Address',
        'name' => 'email',
        'type' => 'text',
      ],
      [
        'key' => 'dir_freelancer',
        'name' => 'freelancer',
        'type' => 'checkbox',
        'choices' => ['freelancer' => 'Freelancer open to commission approaches?'],
      ],
      [
        'key' => 'dir_editor',
        'name' => 'editor',
        'type' => 'checkbox',
        'choices' => ['editor' => 'Editor Open to Pitches?']
      ],
      [
        'key' => 'dir_desc',
        'label' => 'Brief Description of Experience',
        'name' => 'description',
        'type' => 'textarea',
      ],
      [
        'key' => 'dir_affiliation',
        'label' => 'Current Media Outlet Affiliation',
        'name' => 'affiliation',
        'type' => 'text',
      ],
      [
        'key' => 'dir_portfolio',
        'label' => 'Freelance Portfolio',
        'name' => 'portfolio',
        'type' => 'text',
      ],
      [
        'key' => 'dir_specialities',
        'label' => 'Coverage Specialties',
        'name' => 'specialities',
        'type' => 'textarea',
      ],
      [
        'key' => 'dir_languages-reporting',
        'label' => 'Language Skills (for reporting)',
        'name' => 'languages-reporting',
        'type' => 'text',
      ],
      [
        'key' => 'dir_languages-writing',
        'label' => 'Language Skills (for writing/filing)',
        'name' => 'languages-writing',
        'type' => 'text',
      ],
      [
        'key' => 'dir_website',
        'label' => 'Personal website / Online Portfolio',
        'name' => 'website',
        'type' => 'text',
      ],
      [
        'key' => 'dir_linkedin',
        'label' => 'Linkedin Profile',
        'name' => 'linkedin',
        'type' => 'text',
      ],
      [
        'key' => 'dir_twitter',
        'label' => 'Twitter Handle',
        'name' => 'twitter',
        'type' => 'text',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'directory',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'correspondent',
    'title' => 'Details',
    'fields' => [
      [
        'key' => 'wpcf-issuu-link',
        'label' => 'Issuu Link',
        'name' => 'wpcf-issuu-link',
        'type' => 'text',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'correspondent',
        ],
        [
          'param' => 'post_taxonomy',
          'operator' => '==',
          'value' => 'magazine-post-type:online-ebook',
        ],
      ],
    ],
  ]);

  acf_add_local_field_group([
    'key' => 'board-member',
    'title' => 'Company Information',
    'fields' => [
      [
        'key' => 'board-member-company',
        'label' => 'Company Name',
        'name' => 'wpcf-company-name',
        'type' => 'text',
      ],
      [
        'key' => 'board-member-email',
        'label' => 'Email',
        'name' => 'wpcf-email',
        'type' => 'email',
      ],
      [
        'key' => 'board-member-file',
        'label' => 'Downloadable File',
        'name' => 'wpcf-file-upload-0',
        'type' => 'file',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'board-member',
        ],
      ],
    ],
  ]);

  class EventsLocation extends ACF_Location {
    public function initialize() {
        $this->name = 'custom_page';
        $this->label = __( "Custom Page", 'acf' );
        $this->category = 'page';
        $this->object_type = 'page';
    }
    public function match( $rule, $screen, $field_group ) {
      if( isset($screen['post_id']) ) {
        $pages = get_option('fcc_pages');
        $pages = unserialize($pages);
        return $pages[$rule['value']] == $screen['post_id'];
      } else {
        return false;
      }
    }
  }
  acf_register_location_type( 'EventsLocation' );
  acf_add_local_field_group([
    'key' => 'events_page',
    'title' => 'Events Page Settings',
    'fields' => [
      [
        'key' => 'featured_events',
        'label' => 'Featured Events',
        'name' => 'featured_events',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'event'
        ),
        'post_status' => ['publish'],
        'multiple' => 1,
        'return_format' => 'object',
        'ui' => 1,
      ],
      [
        'key' => 'field_events_category',
        'label' => 'Events Category',
        'name' => 'events_category',
        'type' => 'taxonomy',
        'taxonomy' => 'event-categories',
        'field_type' => 'select',
        'return_format' => 'object',
        'allow_null' => 1,
        'add_term' => 0,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'template-events.php',
        ],
      ],
    ],
  ]);
  
  acf_add_local_field_group([
    'key' => 'event-category',
    'title' => 'Event Category',
    'fields' => [
      [
        'key' => 'event-category-order',
        'label' => 'Order',
        'name' => 'order',
        'type' => 'number',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'taxonomy',
          'operator' => '==',
          'value' => 'event-categories',
        ],
      ],
    ],
  ]);
});

add_filter('acf/fields/post_object/query', function($options, $field, $the_post) {
  $options['post_status'] = array('publish');
  return $options;
}, 10, 3);