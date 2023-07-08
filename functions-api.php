<?php
use \Firebase\JWT\JWT;

function aspen_sql($query) {
  if (strpos($_SERVER['SERVER_NAME'], '.local')) {
    $mysqli = new mysqli('127.0.0.1', 'root', 'mysqlpasswd', 'fcc_aspen');
    $result = $mysqli->query($query);
    if (is_bool($result))
      return $result;

    $rows = $result->fetch_all(MYSQLI_ASSOC);

    $result -> free_result();
    $mysqli -> close();

    return $rows;
  } else {
    $conn = sqlsrv_connect("167.16.1.5", ["Database" => 'aspen', "UID" => 'bossdigitial', "PWD" => '5DuW=d$-&s']);
    $stmt = sqlsrv_query($conn, $query);
    
    if (is_bool($stmt))
      return $stmt;
    
    while($row = sqlsrv_fetch_array($stmt)) {
      $rows[] = $row;
    }
    return $rows;
  }
}

function fcc_generate_token($user_id) {
  $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
  $issuedAt = time();
  $notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
  $expire = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

  $token = array(
    'iss' => get_bloginfo('url'),
    'iat' => $issuedAt,
    'nbf' => $notBefore,
    'exp' => $expire,
    'data' => array(
      'user' => array(
        'id' => $user_id
      ),
    ),
  );

  /** Let the user modify the token data before the sign. */
  return JWT::encode($token, $secret_key, 'HS256');
}

function getCardMemberNumbers($user) {
  preg_match('/(.+)@(.+)\.echo/', $user->user_email, $m); 
  return [$m[1], $m[2]];
}

function obfuscateEmail($email) {
  $mail_parts = explode("@", $email);
  $length = strlen($mail_parts[0]);
  $show = 1;
  $hide = $length - $show;
  $replace = str_repeat("*", $hide);
  
  return substr_replace($mail_parts[0], $replace, $show, $hide) . "@" . substr_replace($mail_parts[1], "****", 0, 4);
}

add_action('rest_api_init', function () {
  register_rest_route('echo/v1', '/create', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $mbnum = trim( $request->get_param( 'mbnum' ) );
      $email = trim( $request->get_param( 'email' ) );
      $login = trim( $request->get_param( 'login' ) );
    
      $aspen = aspen_sql("SELECT * FROM MBSCARD0 WHERE MBNUM='$mbnum' AND CARD_EMAIL='$email'");
      if (!$aspen)
        return new WP_Error('Error', 'This member could not be found', ['status' => 403]);

      $card = trim($aspen[0]['CARDNO']);

      if (get_user_by('email', "$card@$mbnum.echo"))
        return new WP_Error('Error', 'This member is already registered. You may use Forgot Password to reset your password.', ['status' => 403]);
        
      if (trim($aspen[0]['CARD_STAT']) != 'A')
        return new WP_Error('Error', 'The member card is not active', ['status' => 403]);

      if (username_exists($login))
        return new WP_Error('Error', 'This login is already in use', ['status' => 403]);
        
      if (!$aspen[0]['CARD_EMAIL'])
        return new WP_Error('Error', 'The E-mail address for your member card is missing from our records. Please contact us to update this first.', ['status' => 403]);

      $code = rand(10000, 99999);
      $id = wp_insert_user([
        'user_login' => $login,
        'first_name' => $aspen[0]['CARD_GIV'],
        'last_name' => $aspen[0]['CARD_SUR'],
        'display_name' => "{$aspen[0]['CARD_GIV']} {$aspen[0]['CARD_SUR']}",
        'role' => 'subscriber',
        'user_email' => "$card@$mbnum.echo",
        'user_activation_key' => $code,
      ]);
      if ($id instanceof WP_Error) {
        return new WP_Error('Error', 'Could not create user: ' . $id->get_error_message(), ['status' => 403]);
      }
        
      update_user_meta($id, 'email', $aspen[0]['CARD_EMAIL']);
      update_user_meta($id, 'phone', $aspen[0]['CARD_MOBIL']);

      send_email($aspen[0]['CARD_EMAIL'], 'Security Code', "Dear {$aspen[0]['CARD_GIV']} {$aspen[0]['CARD_SUR']},<br/><br/>Someone has requested a security code for your account. If it wasn't you just ignore this message.<br/><br/>Security Code: $code<br/><br/>");

      return [
        'email' => $aspen[0]['CARD_EMAIL'],
      ];
    }
  ]);

  register_rest_route('echo/v1', '/lost', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $login = trim(sanitize($request->get_param( 'login' ) ));

      $user = get_user_by('login', $login);
      if (!$user)
        return new WP_Error('Error', 'The user could not be found', ['status' => 403]);
      
      $code = rand(10000, 99999);
      wp_update_user([
        'ID' => $user->ID,
        'user_activation_key' => $code,
      ]);

      $numbers = getCardMemberNumbers($user);
      $cardDetails = aspen_sql("SELECT CARD_EMAIL FROM MBSCARD0 WHERE CARDNO='$numbers[0]'");
      $email = $cardDetails[0]['CARD_EMAIL'];
      send_email($email, 'Security Code', "Dear {$user->display_name},<br/><br/>Someone has requested a security code for your account. If it wasn't you just ignore this message.<br/><br/>Security Code: $code<br/><br/>");

      return [
        'email' => obfuscateEmail($email),
      ];
    }
  ]);

  register_rest_route('echo/v1', '/forgotLogin', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $mbnum = trim( $request->get_param( 'mbnum' ) );
      $email = trim( $request->get_param( 'email' ) );
    
      $aspen = aspen_sql("SELECT * FROM MBSCARD0 WHERE MBNUM='$mbnum' AND CARD_EMAIL='$email'");
      if (!$aspen)
        return new WP_Error('Error', 'This member could not be found', ['status' => 403]);

      $card = trim($aspen[0]['CARDNO']);

      $user = get_user_by('email', "$card@$mbnum.echo");
      if (!$user)
        return new WP_Error('Error', 'This member is not signed up for the members area', ['status' => 403]);

      send_email($email, 'FCC Members area Login ID reminder', "Dear {$user->display_name},<br/><br/>Someone has requested a Login ID reminder for your account. If it wasn't you just ignore this message.<br/><br/>Login ID: {$user->user_login}<br/><br/>");

      return 'OK';
    }
  ]);

  register_rest_route('echo/v1', '/password', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      global $wpdb;

      $login = trim(sanitize($request->get_param( 'login' ) ));
      $code = trim(sanitize($request->get_param( 'code' ) ));
      $password = trim( $request->get_param( 'password' ) );

      if (get_transient('failed_' . $login) == 5)
        return new WP_Error('Empty data', 'Too many unsuccessful login attempts.', ['status' => 403]);
    
      $user = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users WHERE user_login='$login' AND user_activation_key='$code'");
      if (!$user || !count($user)) {
        $failed = intval(get_transient('failed_' . $login));
        if (!$failed) $failed = 0;
        set_transient('failed_' . $login, $failed + 1, HOUR_IN_SECONDS);
        if ($failed + 1 == 5)
          return new WP_Error('Error', 'Too many unsuccessful login attempts. Please try again in an hour.');

        return new WP_Error('Error', 'The security code in invalid', ['status' => 403]);
      }

      wp_set_password($password, $user[0]->ID);
      
      return 'OK';
    }
  ]);
  
  register_rest_route('echo/v1', '/validate', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $user = wp_get_current_user();

      $card = getCardMemberNumbers($user)[0];
      $aspen = aspen_sql("SELECT * FROM MBSCARD0 c JOIN CMSMEMB0 m ON m.MBNUM=c.MBNUM JOIN CMSMTYP0 t ON m.mtyp_code=t.mtyp_code JOIN CMSMBST0 st ON c.mbst_code=st.mbst_code WHERE CARDNO='$card'");

      $aspen = aspen_sql("SELECT m.MBST_CODE, c.CARD_STAT, CARD_EMAIL, CARD_MOBIL, st.MBST_DESC, ARIO_CODE FROM MBSCARD0 c JOIN CMSMEMB0 m ON m.MBNUM=c.MBNUM JOIN CMSMTYP0 t ON m.mtyp_code=t.mtyp_code JOIN CMSMBST0 st ON c.mbst_code=st.mbst_code WHERE CARDNO='$card'");

      if ($aspen === false)
        return new WP_Error('Error', 'Sorry, there was a technical problem, please try again later');

      if (!count($aspen))
        return new WP_Error('Error', 'Member card is invalid');

      if (substr($aspen[0]['MBST_CODE'], 0, 3) !== 'ACT')
        return new WP_Error('Error', 'Member is inactive');

      if ($aspen[0]['CARD_STAT'] !== 'A')
        return new WP_Error('Error', 'Member card is inactive');

      return [
        'token' => fcc_generate_token($user->ID),
        'email' => $aspen[0]['CARD_EMAIL'],
        'phone', $aspen[0]['CARD_MOBIL'],
        'status' => trim($aspen[0]['MBST_DESC']),
        'owner' => trim($aspen[0]['ARIO_CODE']),
        'type' => trim($aspen[0]['ARIO_CODE']) == 'M' ? trim($aspen[0]['MTYP_DESC']) : '',
      ];
    }
  ]);

  register_rest_route('echo/v1', '/login', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $username = trim($request->get_param('username'));
      $password = $request->get_param('password');
      if (trim($username) == '' || trim($password) == '')
        return new WP_Error('Empty data', 'Empty credentials', ['status' => 403]);

      if (get_transient('failed_' . $username) == 5)
        return new WP_Error('Empty data', 'Too many unsuccessful login attempts.', ['status' => 403]);
      
      $user = get_user_by( 'login', $username );
      if ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID ) ) {
        $card = getCardMemberNumbers($user)[0];
        $aspen = aspen_sql("SELECT m.MBST_CODE, c.CARD_STAT, CARD_GIV, CARD_SUR, m.MBNUM, CARD_EMAIL, CARD_MOBIL, st.MBST_DESC, ARIO_CODE FROM MBSCARD0 c JOIN CMSMEMB0 m ON m.MBNUM=c.MBNUM JOIN CMSMTYP0 t ON m.mtyp_code=t.mtyp_code JOIN CMSMBST0 st ON c.mbst_code=st.mbst_code WHERE CARDNO='$card'");

        if ($aspen === false)
          return new WP_Error('Error', 'Sorry, there was a technical problem, please try again later');

        if (!count($aspen))
          return new WP_Error('Error', 'Member card is invalid');

        if (substr($aspen[0]['MBST_CODE'], 0, 3) !== 'ACT')
          return new WP_Error('Error', 'Member is inactive');

        if ($aspen[0]['CARD_STAT'] !== 'A')
          return new WP_Error('Error', 'Member card is inactive');
        
        update_user_meta($user->ID, 'email', $aspen[0]['CARD_EMAIL']);
        update_user_meta($user->ID, 'phone', $aspen[0]['CARD_MOBIL']);

        return [
          'token' => fcc_generate_token($user->ID),
          'login' => $user->user_login,
          'first_name' => trim($aspen[0]['CARD_GIV']),
          'last_name' => trim($aspen[0]['CARD_SUR']),
          'member_number' => trim($aspen[0]['MBNUM']),
          'email' => $aspen[0]['CARD_EMAIL'],
          'phone' => $aspen[0]['CARD_MOBIL'],
          'status' => trim($aspen[0]['MBST_DESC']),
          'owner' => trim($aspen[0]['ARIO_CODE']),
          'type' => trim($aspen[0]['ARIO_CODE']) == 'M' ? trim($aspen[0]['MTYP_DESC']) : '',
        ];
      } else {
        $failed = intval(get_transient('failed_' . $username));
        if (!$failed) $failed = 0;
        set_transient('failed_' . $username, $failed + 1, HOUR_IN_SECONDS);
        if ($failed + 1 == 5)
          return new WP_Error('Error', 'Too many unsuccessful login attempts. Please try again in an hour.');
  
        return new WP_Error('Error', 'Wrong password');
      }
    }
  ]);

  register_rest_route('echo/v1', '/logout', array(
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
      list($token) = sscanf($auth, 'Bearer %s');
      $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
      try {
        $token_decoded = JWT::decode($token, $secret_key, array('HS256'));
        set_transient('token_blacklist_' . substr($token, strrpos($token, ".") + 1), '', $token_decoded->exp - time());
      } catch(Exception $e) {}
      return [];
    }
  ));
  
  register_rest_route('echo/v1', '/dashboard', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $notices = get_posts([
        'category_name' => 'club-notices',
        'posts_per_page' => 3
      ]);
      $notices = array_map(fn($notice) => ['id' => $notice->ID, 'title' => html_entity_decode($notice->post_title)], $notices); 
      $notices_chunks = array_chunk($notices, 1);
      
      $events = get_posts([
        'post_type' => 'event',
        'meta_query' => [
          'relation' => 'AND',
          'start' => [
            'key' => '_event_start_local',
            'value' => "9999-99-99 99:99:99",
            'compare' => '<=', 
          ],
          'end' => [
            'key' => '_event_end_local',
            'value' => date("Y-m-d 00:00:00"),
            'compare' => '>=', 
          ],
        ],
        'tax_query' => [
          [
            'taxonomy' => 'event-categories',
            'terms' => 'fb-promotions',
            'field' => 'slug',
            'operator' => 'NOT IN',
          ]
        ],
        'orderby' => [
          'start' => 'ASC'
        ],
        'posts_per_page' => 12
      ]);
      $events = array_map(fn($event) => ['id' => $event->ID, 'title' => html_entity_decode($event->post_title), 'img' => get_the_post_thumbnail_url($event, 'medium_large'), 'date' => fcc_event_date($event)], $events);

      $news = get_posts([
        'category_name' => 'news',
        'posts_per_page' => 12
      ]); 
      $news = array_map(fn($news) => ['id' => $news->ID, 'title' => html_entity_decode($news->post_title)], $news);
      $news_chunks = array_chunk($news, 3); 

      $correspondent = get_posts([
        'category_name' => 'correspondent',
        'posts_per_page' => 12
      ]); 
      $correspondent = array_map(fn($item) => ['id' => $item->ID, 'title' => html_entity_decode($item->post_title)], $correspondent);
      $correspondent_chunks = array_chunk($correspondent, 3); 

      $press_freedom = get_posts([
        'category_name' => 'press-freedom',
        'posts_per_page' => 12
      ]); 
      $press_freedom = array_map(fn($item) => ['id' => $item->ID, 'title' => html_entity_decode($item->post_title)], $press_freedom);
      $press_freedom_chunks = array_chunk($press_freedom, 3);
      
      $promotions = get_posts([
        'post_type' => 'event',
        'meta_query' => [
          'relation' => 'AND',
          'start' => [
            'key' => '_event_start_local',
            'value' => "9999-99-99 99:99:99",
            'compare' => '<=', 
          ],
          'end' => [
            'key' => '_event_end_local',
            'value' => date("Y-m-d 00:00:00"),
            'compare' => '>=', 
          ],
        ],
        'orderby' => [
          'start' => 'ASC'
        ],
        'tax_query' => [
          [
            'taxonomy' => 'event-categories',
            'terms' => 'fb-promotions',
            'field' => 'slug',
          ]
        ],
        'posts_per_page' => 12
      ]);
      $promotions = array_map(fn($promotion) => ['id' => $promotion->ID, 'title' => html_entity_decode($promotion->post_title), 'img' => get_the_post_thumbnail_url($promotion, 'medium_large'), 'date' => fcc_event_date($promotion), 'desc' => mb_strimwidth(trim(strip_tags(do_shortcode(html_entity_decode($promotion->post_content)))), 0, 100, '...')], $promotions);

      return [
        'notices' => $notices_chunks,
        'events' => $events,
        'news' => $news_chunks,
        'correspondent' => $correspondent_chunks,
        'press_freedom' => $press_freedom_chunks,
        'promotions' => $promotions,
      ];
    }
  ]);
  
  register_rest_route('echo/v1', '/news_index', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $category = sanitize($request->get_param("category"));
      $date = sanitize($request->get_param("date"));
      $search = sanitize($request->get_param("search"));
      $paged = sanitize($request->get_param("paged"));

      if ($category || $date || $search) {
        
        if ($category && $category == 'correspondent')
          $posts = get_posts([
            'post_type' => 'correspondent',
            'posts_per_page' => 10,
            'paged' => $paged,
            'date_query' => [
              [
                'before' => $date,
                'inclusive' => true,
              ]
            ],
            's' => $search,
          ]);
        else
          $posts = get_posts([
            'posts_per_page' => 10,
            'paged' => $paged,
            'date_query' => [
              [
                'before' => $date,
                'inclusive' => true,
              ]
            ],
            's' => $search,
            'category_name' => $category,
          ]);

        return array_map(fn($post) => [
          'id' => $post->ID, 
          'title' => html_entity_decode($post->post_title), 
          'date' => $post->post_date, 
          'thumb' => get_the_post_thumbnail_url($post, 'thumbnail'), 
          'content' => mb_strimwidth(trim(preg_replace("/[\n\r]+/", " ", strip_tags(do_shortcode(html_entity_decode($post->post_content))))), 0, 300, '...'),
        ], $posts); 
      } else {
        $notices = get_posts([
          'category_name' => 'club-notices',
          'posts_per_page' => 12
        ]);
        $notices = array_map(fn($notice) => ['id' => $notice->ID, 'title' => html_entity_decode($notice->post_title)], $notices); 
        $notices_chunks = array_chunk($notices, 3);
  
        $press_freedom = get_posts([
          'category_name' => 'press-freedom',
          'posts_per_page' => 12
        ]); 
        $press_freedom = array_map(fn($item) => ['id' => $item->ID, 'title' => html_entity_decode($item->post_title)], $press_freedom);
        $press_freedom_chunks = array_chunk($press_freedom, 3);

        $news = get_posts([
          'category_name' => 'news',
          'posts_per_page' => 3
        ]); 
        $news = array_map(fn($news) => ['id' => $news->ID, 'title' => html_entity_decode($news->post_title), 'date' => $news->post_date, 'img' => get_the_post_thumbnail_url($news, 'medium_large')], $news);
  
        $correspondent = get_posts([
          'category_name' => 'correspondent',
          'posts_per_page' => 12
        ]); 
        $correspondent = array_map(fn($item) => ['id' => $item->ID, 'title' => html_entity_decode($item->post_title)], $correspondent);
        $correspondent_chunks = array_chunk($correspondent, 3); 

        $corr = get_posts([
          'post_type' => 'correspondent',
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'online-ebook',
              'field' => 'slug',
              'operator' => 'NOT IN'
            ]
          ],
          'posts_per_page' => 1,
        ])[0];
  
        $issue = get_the_terms($corr, 'magazine-issue')[0];
  
        $ebook = get_posts([
          'post_type' => 'correspondent',
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'online-ebook',
              'field' => 'slug',
            ],
            [
              'taxonomy' => 'magazine-issue',
              'terms' => $issue->term_id,
            ]
          ],
          'posts_per_page' => 1,
        ]); 
        $thumb = $ebook ? get_the_post_thumbnail_url($ebook[0], 'medium_large') : (get_stylesheet_directory_uri() . '/images/logo-blue.png');

        return [
          'notices' => $notices_chunks,
          'press_freedom' => $press_freedom_chunks,
          'news' => $news,
          'corr_thumb' => $thumb,
          'corr_id' => $corr->ID,
          'corr_title' => html_entity_decode($corr->post_title),
          'corr_date' => $corr->post_date,
          'corr_content' => mb_strimwidth(trim(preg_replace("/[\n\r]+/", " ", strip_tags(do_shortcode(html_entity_decode($corr->post_content))))), 0, 100, '...'),
          'correspondent' => $correspondent_chunks,
        ];
      }
    }
  ]);
  
  register_rest_route('echo/v1', '/notices_index', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $page = sanitize($request->get_param("page"));

      $query = array(
        'category_name' => 'club-notices',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => 25,
      );
    
      $notices = get_posts($query);
      $result['notices'] = array_map(function($notice) {
        $item = [];
        $item['content'] = strip_tags(html_entity_decode($notice->post_content));
    
        $item['id'] = $notice->ID;
        $item['title'] = html_entity_decode($notice->post_title);
        $item['date'] = strtotime($notice->post_date);
        $item['excerpt'] = $notice->post_excerpt;
    
        return $item;
      }, $notices);
    
      return $result;
    }
  ]);

  register_rest_route('echo/v1', '/notices', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $notice = get_post($id);

      $res = (object)array(
        'id' => $id,
        'title' => html_entity_decode($notice->post_title),
        'content' => preg_replace('/width: \d+px/', '', preg_replace('/ width="\d+"/', '', html_entity_decode($notice->post_content))),
        'date' => strtotime($notice->post_date),
        'poster' => get_the_post_thumbnail_url($notice, 'medium_large'),
      );

      return $res;
    }
  ]);

  register_rest_route('echo/v1', '/news', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $news = get_post($id);

      $res = (object)array(
        'id' => $id,
        'title' => html_entity_decode($news->post_title),
        'content' => html_entity_decode($news->post_content),
        'date' => strtotime($news->post_date),
        'poster' => get_the_post_thumbnail_url($news, 'medium_large'),
      );

      return $res;
    }
  ]);

  register_rest_route('echo/v1', '/promotions_index', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $page = sanitize($request->get_param("page"));

      $query = array(
        'post_type' => 'event',
        'category_name' => 'club-notices',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => 25,
      );
    
      $notices = get_posts($query);
      $result['notices'] = array_map(function($notice) {
        $item = [];
        $item['content'] = strip_tags(html_entity_decode($notice->post_content));
    
        $item['id'] = $notice->ID;
        $item['title'] = html_entity_decode($notice->post_title);
        $item['date'] = strtotime($notice->post_date);
        $item['excerpt'] = $notice->post_excerpt;
    
        return $item;
      }, $notices);
      
      $result['categories'] = [];
    
      return $result;
    }
  ]);

  register_rest_route('fcc/v1', '/event_cal', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $event = get_post($id);
      $meta = get_post_custom($id);
    
      $ical =
        "BEGIN:VCALENDAR
    VERSION:2.0
    PRODID:-//hacksw/handcal//NONSGML v1.0//EN
    BEGIN:VEVENT
    UID:" . md5(uniqid(mt_rand(), true)) . "@lrc.com.hk
    DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
    DTSTART;TZID=Asia/Hong_Kong:" . date('Ymd\THis', strtotime($meta['_event_start_local'][0])) . "
    DTEND;TZID=Asia/Hong_Kong:" . date('Ymd\THis', strtotime($meta['_event_end_local'][0])) . "
    SUMMARY:" . html_entity_decode($event->post_title) .
    ($meta['lrc_event_venue'][0] ? "LOCATION:" . $meta['lrc_event_venue'][0] : "") . "
    END:VEVENT
    END:VCALENDAR";
    
      header("Content-type: application/force-download");
      header('Content-Disposition: attachment; filename=event.ics');
    
      echo $ical;
      exit;
    }
  ]);
    
  register_rest_route('echo/v1', '/dining', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $user = wp_get_current_user();
      $numbers = getCardMemberNumbers($user);
      $venue = sanitize($request->get_param('venue'));
      $date = sanitize($request->get_param('date'));
      $venues = explode(' & ', $venue);

      if ($date < date("Y-m-d H:i:s"))
        return "The time selected is in the past";

      if ($date < date("Y-m-d H:i:s", strtotime('+12 hours')))
        return "The time selected must be at least 12 hours later than the current time";

      $blocked_slots = get_field('blocked_slots', 'option');
      foreach ($blocked_slots as $slot)
        foreach ($venues as $ven)
          if (in_array($ven, $slot['venue']) && $slot['start'] <= $date && $date < $slot['end'])
            return 'The selected venue is not available for this date and time';

      wp_insert_post(array(
        'post_type' => 'dining_request',
        'post_status' => 'publish',
        'post_author' => $user->ID,
        'post_title' => $venue,
        'post_content' => serialize([
          'adults' => sanitize($request->get_param('adults')),
          'children' => sanitize($request->get_param('children')),
          'email' => sanitize($request->get_param('email')),
          'phone' => sanitize($request->get_param('phone')),
          'comments' => sanitize($request->get_param('comments')),
        ]),
        'meta_input' => array(
          'date' => $date,
          'status' => 'Pending',
        )
      ));
    
      $message = "We received a new dining enquiry:<br/><br/>";
      $message .= "Name: $user->first_name $user->last_name<br/>";
      $message .= "Member number: $numbers[1]<br/>";
      $message .= "Card number: $numbers[0]<br/>";
      $message .= "Date: " . sanitize($request->get_param('date')) . "<br/>";
      $message .= "Time: " . sanitize($request->get_param('time')) . "<br/>";
      $message .= "Venue: " . sanitize($request->get_param('venue')) . "<br/>";
      $message .= "Adults: " . sanitize($request->get_param('adults')) . "<br/>";
      $message .= "Children: " . sanitize($request->get_param('children')) . "<br/>";
      $message .= "Email: " . sanitize($request->get_param('email')) . "<br/>";
      $message .= "Phone: " . sanitize($request->get_param('phone')) . "<br/>";
      $message .= "Comments: " . sanitize($request->get_param('comments')) . "<br/>";
      $message .= "Enquiry date: " . date("d/m/Y H:i") . "<br/>";
    
      $from = get_option('admin_email');
      $headers = array('Content-Type: text/html; charset=UTF-8', "From: $from");
    
      wp_mail(explode(",", (string)get_field('dining_email', 'option')), 'New dining enquiry', $message, $headers);
      send_email(sanitize($request->get_param('email')), 'Dining Enquiry', "Dear {$user->display_name},<br/><br/>" . get_field('dining_message', 'option'));
    
      return 'OK';
    }
  ]);

  register_rest_route('echo/v1', '/private', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $date = sanitize($request->get_param('date'));
      // $date2 = sanitize($request->get_param('date2'));
      $duration = sanitize($request->get_param('duration'));
      $end = date("Y-m-d H:i:s", strtotime($date) + $duration * 60 * 60);
      $venue = sanitize($request->get_param('venue'));
      $venues = explode(' & ', $venue);

      if ($date < date("Y-m-d H:i:s"))
        return "The date selected is in the past";

      $blocked_slots = get_field('blocked_slots', 'option');
      foreach ($blocked_slots as $slot)
        foreach ($venues as $ven)
          if (in_array($ven, $slot['venue']) && $slot['start'] <= $date && $date < $slot['end'])
            return 'The selected venue is not available for this date and time';
          
      $user = wp_get_current_user();
      $numbers = getCardMemberNumbers($user);

      // [x1:x2] and [y1:y2] : x1 <= y2 && y1 < x2
      // if (get_posts([
      //   'post_type' => 'event',
      //   'post_status' => 'publish',
      //   'posts_per_page' => 1,
      //   'meta_query' => [
      //     [
      //       'key' => '_event_end_local',
      //       'value' => $date,
      //       'compare' => '>=', 
      //     ],
      //     [
      //       'key' => '_event_start_local',
      //       'value' => $end,
      //       'compare' => '<', 
      //     ],
      //     [
      //       'key' => 'event_venue',
      //       'value' => $venue,
      //       'compare' => 'LIKE'
      //     ],
      //   ],
      // ]))
      //   return 'Sorry, the time slot conflicts with an existing event';

      if (get_posts([
        'post_type' => 'private_request',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'title' => $venue,
        'meta_query' => [
          [
            'key' => 'end',
            'value' => $date,
            'compare' => '>=', 
          ],
          [
            'key' => 'date',
            'value' => $end,
            'compare' => '<', 
          ],
          [
            'key' => 'status',
            'value' => 'Confirmed',
            'compare' => '='
          ],
        ],
      ]))
        return 'Sorry, the time slots conflicts with an existing private function reservation';

      wp_insert_post([
        'post_type' => 'private_request',
        'post_status' => 'publish',
        'post_author' => $user->ID,
        'post_title' => $venue,
        'post_content' => serialize([
          'type' => sanitize($request->get_param('type')),
          'attendees' => sanitize($request->get_param('attendees')),
          'email' => sanitize($request->get_param('email')),
          'phone' => sanitize($request->get_param('phone')),
          'setup' => sanitize($request->get_param('setup')),
          'comments' => sanitize($request->get_param('comments')),
        ]),
        'meta_input' => [
          'date' => $date,
          'end' => $end,
          'status' => 'Pending',
        ],
      ]);
    
      $message = "We received a new private function enquiry:<br/><br/>";
      $message .= "Name: $user->first_name $user->last_name<br/>";
      $message .= "Member number: $numbers[1]<br/>";
      $message .= "Card number: $numbers[0]<br/>";
      $message .= "Date: " . $date . "<br/>";
      // $message .= "Date 2: " . $date2  . "<br/>";
      $message .= "Duration: " . $duration . "<br/>";
      $message .= "Venue: " . $venue . "<br/>";
      $message .= "Type: " . sanitize($request->get_param('type')) . "<br/>";
      $message .= "Attendees: " . sanitize($request->get_param('attendees')) . "<br/>";
      $message .= "Email: " . sanitize($request->get_param('email')) . "<br/>";
      $message .= "Phone: " . sanitize($request->get_param('phone')) . "<br/>";
      $message .= "Setup: " . sanitize($request->get_param('setup')) . "<br/>";
      $message .= "Comment: " . sanitize($request->get_param('comments')) . "<br/>";
    
      $from = get_option('admin_email');
      $headers = array('Content-Type: text/html; charset=UTF-8', "From: $from");
    
      wp_mail(explode(",", (string)get_field('private_email', 'option')), 'New private function enquiry', $message, $headers);
      send_email(sanitize($request->get_param('email')), 'Private Function Enquiry', "Dear {$user->display_name},<br/><br/>" . get_field('private_message', 'option'));
  
      return 'OK';
    }
  ]);

  function sanitize($input) {
      return ltrim(sanitize_textarea_field($input), "=+-@");
  }

  register_rest_route('echo/v1', '/bookings', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $user = wp_get_current_user();

      $response['dining'] = [];
      $dining = get_posts([
        'post_type' => 'dining_request',
        'author' => $user->ID,
        'meta_key' => 'date',
        'meta_value' => date("Y-m-d 00:00:00"),
        'meta_compare' => '>',
      ]);
      foreach ($dining as $item) {
        $data = unserialize(get_the_content(null, false, $item->ID));
        $response['dining'][] = array_merge($data, [
          'id' => $item->ID,
          'date' => strtotime(get_post_meta($item->ID, 'date', true)),
          'venue' => html_entity_decode($item->post_title),
          'thumb' => get_stylesheet_directory_uri() . '/images/thumb-dining.jpg',
          'status' => get_post_meta($item->ID, 'status', true),
        ]);
      }

      $response['private'] = [];
      $private = get_posts([
        'post_type' => 'private_request',
        'author' => $user->ID,
        'meta_key' => 'date',
        'meta_value' => date("Y-m-d 00:00:00"),
        'meta_compare' => '>',
      ]);
      foreach ($private as $item) {
        $date = strtotime(get_post_meta($item->ID, 'date', true));
        $data = unserialize(get_the_content(null, false, $item->ID));
        $response['private'][] = array_merge($data, [
          'id' => $item->ID,
          'date' => $date,
          // 'date2' => strtotime(get_post_meta($item->ID, 'date2', true)),
          'duration' => ($date - strtotime(get_post_meta($item->ID, 'end', true))) / 60 / 60,
          'venue' => get_the_title($item->ID),
          'thumb' => get_stylesheet_directory_uri() . '/images/thumb-dining.jpg',
          'status' => get_post_meta($item->ID, 'status', true),
        ]);
      }

      $response['events'] = [];
      $events = get_posts([
        'post_type' => 'event',
        'meta_query' => [
          [
            'key' => 'event_signups_[0-9]+_member',
            '_key_compare' => 'REGEXP',
            'value' => $user->ID,
          ],
          'end' => [
            'key' => '_event_end_local',
            'value' => date("Y-m-d h:i:s"),
            'compare' => '>=', 
          ],
        ],
        'orderby' => [
          'start' => 'ASC',
        ],
      ]);
      foreach ($events as $event) {
        $thumb = get_the_post_thumbnail_url($event->ID, 'thumb');
        $signups = get_field('event_signups', $event->ID);
        $signups = array_filter((array)$signups, fn($signup) => $signup['member'] == $user->ID);

        $response['events'][] = [
          'id' => $event->ID,
          'thumb' => $thumb,
          'title' => html_entity_decode($event->post_title),
          'date' => strtotime(get_field('_event_start_local', $event->ID, false)),
          'end' => strtotime(get_field('_event_end_local', $event->ID, false)),
          'date' => fcc_event_start_timestamp($event),
          'end' => fcc_event_end_timestamp($event),
          'signups' => $signups,
        ];
      }

      return $response;
    }
  ]);

  register_rest_route('echo/v1', '/request_cancel', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $id = $request->get_param('id');
      $user = wp_get_current_user();
      $numbers = getCardMemberNumbers($user);

      $post = get_post($id);
      if ($post->post_author != $user->ID)
        return new WP_Error('Error', 'No permission');

      update_post_meta($id, 'status', 'Cancelled');

      switch ($post->post_type) {
        case 'dining_request':
          $content = unserialize($post->post_content);
          $meta = get_post_meta($post->ID);

          $message = "CANCELLATION of a dining enquiry:<br/><br/>";
          $message .= "Name: $user->first_name $user->last_name<br/>";
          $message .= "Member number: $numbers[1]<br/>";
          $message .= "Card number: $numbers[0]<br/>";
          $message .= "Date: " . $meta['date'][0] . "<br/>";
          $message .= "Venue: " . $post->post_title . "<br/>";
          $message .= "Adults: " . $content['adults'] . "<br/>";
          $message .= "Children: " . $content['children'] . "<br/>";
          $message .= "Email: " . $content['email'] . "<br/>";
          $message .= "Phone: " . $content['phone'] . "<br/>";
          $message .= "Comments: " . $content['comments'] . "<br/>";
        
          $from = get_option('admin_email');
          $headers = array('Content-Type: text/html; charset=UTF-8', "From: $from");
        
          wp_mail(explode(",", (string)get_field('dining_email', 'option')), 'CANCELLED dining enquiry', $message, $headers);
          send_email($content['email'], 'CANCELLED Dining Enquiry', "Dear {$user->display_name},<br/><br/>Your Dining table booking request has been cancelled.");

          break;

        case 'private_request':
          $content = unserialize($post->post_content);
          $meta = get_post_meta($post->ID);
        
          $message = "CANCELLATION of a private function enquiry:<br/><br/>";
          $message .= "Name: $user->first_name $user->last_name<br/>";
          $message .= "Member number: $numbers[1]<br/>";
          $message .= "Card number: $numbers[0]<br/>";
          $message .= "Date: " . $meta['date'][0] . "<br/>";
          $message .= "Venue: " . $post->post_title . "<br/>";
          $message .= "Type: " . $content['type'] . "<br/>";
          $message .= "Attendees: " . $content['attendees'] . "<br/>";
          $message .= "Email: " . $content['email'] . "<br/>";
          $message .= "Phone: " . $content['phone'] . "<br/>";
          $message .= "Setup: " . $content['setup'] . "<br/>";
          $message .= "Comment: " . $content['comments'] . "<br/>";
        
          $from = get_option('admin_email');
          $headers = array('Content-Type: text/html; charset=UTF-8', "From: $from");
        
          wp_mail(explode(",", (string)get_field('private_email', 'option')), 'CANCELLED private function enquiry', $message, $headers);
          send_email(sanitize($request->get_param('email')), 'CANCELLED Private Function Enquiry', "Dear {$user->display_name},<br/><br/>Your Dining table booking request has been cancelled.");

          break;
      }

      return 'OK';
    }
  ]);

  register_rest_route('echo/v1', '/statement', [
    'methods' => 'GET',
    'callback' => function(WP_REST_Request $request) {
      $month = sanitize($request->get_param('month'));
      $year = sanitize($request->get_param('year'));

      $user = wp_get_current_user();
      $mbnum = $request->get_param('member_number') ?: getCardMemberNumbers($user)[1];

      $statement = aspen_sql("SELECT STMT_BF, STMT_AUTO, STMT_DUEDA FROM ARSSTMT2 WHERE MBNUM='$mbnum' AND DATEPART(yy, STMT_AGED1) = $year AND DATEPART(mm, STMT_AGED1) = $month")[0];
      
      $owner = aspen_sql("SELECT MEMB_SAL, MEMB_SUR, MEMB_GIV FROM CMSMEMB0 WHERE MBNUM='$mbnum'")[0];
      $default = trim($owner['MEMB_SAL']) . ' ' . trim($owner['MEMB_SUR']) . ' ' . trim($owner['MEMB_GIV']) . ' ';

      $transactions = [];
      $paymentTotal = 0;
      $payments = aspen_sql("SELECT MRTT_DATE, MRTT_REF, MRTT_DESC, MRTT_AMT FROM CMSMRTT1 WHERE MBNUM='$mbnum' AND DATEPART(yy, MRTT_DATE) = $year AND DATEPART(mm, MRTT_DATE) = $month");
      foreach ($payments as $payment) {
        $transactions[$default][] = [
          'date' => $payment['MRTT_DATE']->format("d/m/Y"),
          'dateYmd' => $payment['MRTT_DATE']->format("Y/m/d"),
          'no' => $payment['MRTT_REF'],
          'desc' => $payment['MRTT_DESC'],
          'credit' => $payment['MRTT_AMT'],
        ];
        $paymentTotal += $payment['MRTT_AMT'];
      }
      
      $trans = aspen_sql("SELECT MITT_DATE, MITT_SOUR1, MITT_AMT, MITT_DESC, REVC_DESC, MITT_INVNO, MBSCARD0.CARDNO, CARD_SAL, CARD_SUR, CARD_GIV FROM CMSMITT1 LEFT JOIN CMSREVC0 ON CMSMITT1.REVC_CODE = CMSREVC0.REVC_CODE LEFT JOIN CMSMITT2 ON CMSMITT1.MITTNO = CMSMITT2.MITTNO LEFT JOIN MBSCARD0 ON MBSCARD0.CARDNO = CMSMITT2.CARDNO WHERE CMSMITT1.MBNUM='$mbnum' AND DATEPART(yy, MITT_DATE) = $year AND DATEPART(mm, MITT_DATE) = $month ORDER BY MITT_DATE");
      
      $spendTotal = 0;
      foreach ($trans as $tran) {
        if ($tran['MITT_SOUR1'] === 'CH') {
          $name = trim($tran['CARD_SAL']) . ' ' . trim($tran['CARD_SUR']) . ' ' . trim($tran['CARD_GIV']);
          $spendTotal += $tran['MITT_AMT'];
        } else {
          $name = $default;
          $paymentTotal -= $tran['MITT_AMT'];
        }

        $transactions[$name][] = [
          'date' => $tran['MITT_DATE']->format("d/m/Y"),
          'dateYmd' => $tran['MITT_DATE']->format("Y/m/d"),
          'no' => trim($tran['MITT_INVNO']),
          'desc' => trim($tran['MITT_DESC']) ?: trim($tran['REVC_DESC']),
          'debit' => $tran['MITT_AMT'],
        ];
      }

      foreach($transactions as $memName => $trans)
        usort($transactions[$memName], function($a, $b) {
          return strcmp($a['dateYmd'], $b['dateYmd']);
        });

      $limit = aspen_sql("SELECT MEMB_AUTOL FROM CMSARAC0 WHERE MBNUM='$mbnum'")[0]['MEMB_AUTOL'];
      
      return [
        'bf' => $statement['STMT_BF'],
        'trans' => $transactions,
        // 'total' => $total,
        'autopay_limit' => $limit,
        'overdue' => $statement ? $statement['STMT_BF'] - $paymentTotal : null,
        'autopay_date' => $statement ? $statement['STMT_AUTO']->format("d/m/Y") : null,
        'autopay_amount' => $statement ? min($limit, $statement['STMT_BF'] - $paymentTotal + $spendTotal) : null,
        'cheque_date' => $statement ? $statement['STMT_DUEDA']->format("d/m/Y") : null,
        // 'cheque_amount' => $statement ? $total - min($limit, $total) : null,
      ];
    }
  ]);

  register_rest_route('echo/v1', '/shop_home', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $categories = array_filter(get_terms([
        'taxonomy' => 'product_cat',
        'parent' => false,
        'hide_empty' => false,
        'hierarchical' => false,
      ]), function($term) { return $term->slug !== 'uncategorized'; });
      
      $best_sellers = get_posts([
        'post_type' => 'product',
        'tax_query' => [
          [
            'taxonomy' => 'product_promo',
            'terms' => 'best-sellers',
            'field' => 'slug',
          ],
          [
            'taxonomy' => 'product_visibility',
            'terms'     => ['exclude-from-catalog'],
            'field'     => 'name',
            'operator'  => 'NOT IN',
          ]
        ],
      ]);
      
      $sale = get_posts([
        'post_type' => 'product',
        'tax_query' => [
          [
            'taxonomy' => 'product_promo',
            'terms' => 'on-sale',
            'field' => 'slug',
          ],
          [
            'taxonomy' => 'product_visibility',
            'terms'     => ['exclude-from-catalog'],
            'field'     => 'name',
            'operator'  => 'NOT IN',
          ]
        ],
      ]);

      return [
        'browse' => getBrowse(),
        'categories' => array_values(array_map(function($cat) { 
          $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
          $image = wp_get_attachment_image_src( $thumbnail_id, 'medium_large' )[0];

          return [
            'tax' => $cat->taxonomy,
            'slug' => $cat->slug,
            'title' => html_entity_decode($cat->name),
            'img' => $image,
          ];
        }, $categories)),
        'best_sellers' => array_map('getProductJson', $best_sellers),
        'featured' => array_map(function($banner) {
          return [
            'id' => $banner['product'],
            'img' => $banner['bg']['sizes']['1536x1536'],
            'desc' => $banner['text'],
          ];
        }, get_field('shop_banner', 'option') ?: []),
        'on_sale' => array_map('getProductJson', $sale),
      ];
    }
  ]);

  function getBrowse() {
    $result = getTerms('product_cat');
    return $result;
  }

  function getTerms($taxonomy) {
    $parents = array_filter(get_terms([
      'taxonomy' => $taxonomy,
      'parent' => false,
      'hide_empty' => false,
    ]), function($term) { return $term->slug !== 'uncategorized'; });

    return array_values(array_map(
      function($parent) use ($taxonomy) {
        $children = get_terms([
          'taxonomy' => $taxonomy,
          'parent' => $parent->term_id,
        ]);

        $children = array_values(array_map(
          function($child) use ($taxonomy) {
            return [
              'tax' => $taxonomy,
              'slug' => $child->slug,
              'title' => html_entity_decode($child->name),
            ];
          },
          $children
        ));

        array_unshift($children, [
          'tax' => $taxonomy,
          'slug' => $parent->slug,
          'title' => 'All ' . html_entity_decode($parent->name),
        ]);

        return [
          'title' => html_entity_decode($parent->name),
          'children' => $children,
        ];
      },
      $parents
    ));
  }

  function getProductJson($product) {
    $product = wc_get_product($product);

    if (!$product)
      return null;

    if (is_a($product, 'WC_Product_Variable')) {
      $variations = $product->get_available_variations();
      $variation_price = 100000000;
      foreach ($variations as $variation)
        if ($variation['display_price'] < $variation_price)
          $variation_price = $variation['display_price'];
    }

    $categories = wp_get_post_terms($product->get_id(), 'product_cat');

    return [
      'id' => $product->get_id(),
      'title' => html_entity_decode($product->get_name()),
      'img' => get_the_post_thumbnail_url($product->get_id(), 'medium'),
      'categories' => count($categories) ? [
        [
          'slug' => end($categories)->slug,
          'name' => html_entity_decode(end($categories)->name),
        ]
      ] : '',
      'desc' => $product->get_description(),
      'attr' => [
        'Country' => $product->get_attribute('pa_country'),
        'Region' => $product->get_attribute('pa_region'),
        'Grape' => $product->get_attribute('pa_grape'),
      ],
      'regular_price' => $product->get_regular_price(),
      'price' => $product->get_price(),
      'variation_price' => $variation_price,
    ];
  }

  register_rest_route('echo/v1', '/shop_index', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      global $wpdb;
      
      $page = sanitize($request->get_param("page"));
      $filters = $request->get_param("filters");
      $search = $request->get_param("search");

      $result = [
        'browse' => getBrowse(),
      ];
      
      if (is_array($filters)) {
        $term = get_term_by('slug', $filters[0]['slug'], $filters[0]['tax']);
        $result['title'] = html_entity_decode($term->name);

        if ($term->taxonomy == 'product_cat' && !$term->parent) {
          $categories = array_filter(get_terms([
            'taxonomy' => 'product_cat',
            'parent' => $term->term_id,
            'hide_empty' => false,
            'hierarchical' => false,
          ]), function($term) { return $term->slug !== 'uncategorized'; });
          // $categories = array_slice($categories, 0, 7);

          $children = implode(',', array_merge([$term->term_id], get_term_children($term->term_id, 'product_cat')));
          $sections_ids = $wpdb->get_col("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'product_selection' AND term_taxonomy_id IN (
            SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id IN (
              SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ({$children})
            )
          )");
          $selections = empty($sections_ids) ? [] : get_terms('product_selection', ['include' => $sections_ids]);
          for ($i = 0; $i < min(3, count($selections)); $i++) {
            $selection = $selections[$i]; 
            $seasonal[] = [
              'title' => html_entity_decode($selection->name),
              'img' => get_field('product-selection-image', $selection->taxonomy . '_' . $selection->term_id)['sizes']['woocommerce_single'],
              'tax' => 'product_selection',
              'slug' => $selection->slug,
            ];
          }

          $result = [
            'title' => html_entity_decode($term->name),
            'browse' => getBrowse(),
            'banner' => get_field('product-category-banner', $term->taxonomy . '_' . $term->term_id)['sizes']['1536x1536'],
            'desc' => $term->description,
            'categories' => !empty(get_field('product-category-bg', $term->taxonomy . '_' . $term->term_id)) ? 
              array_values(array_map(function($cat) { 
                $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                $image = wp_get_attachment_image_src( $thumbnail_id, 'medium_large' )[0];
      
                return [
                  'tax' => $cat->taxonomy,
                  'slug' => $cat->slug,
                  'title' => html_entity_decode($cat->name),
                  'img' => $image,
                ];
              }, $categories))
              : null,
            'featured' => array_values(array_map('getProductJson', get_posts([
              'post_type' => 'product',
              'tax_query' => [
                [
                  'taxonomy' => 'product_promo',
                  'terms' => 'shop-home-featured',
                  'field' => 'slug',
                ],
                [
                  'taxonomy' => 'product_cat',
                  'terms' => $term->term_id,
                ],
                [
                  'taxonomy' => 'product_visibility',
                  'terms'     => ['exclude-from-catalog'],
                  'field'     => 'name',
                  'operator'  => 'NOT IN',
                ]
              ],
            ]))),
            'best_sellers' => array_values(array_map('getProductJson', get_posts([
              'post_type' => 'product',
              'tax_query' => [
                [
                  'taxonomy' => 'product_promo',
                  'terms' => 'best-sellers',
                  'field' => 'slug',
                ],
                [
                  'taxonomy' => 'product_cat',
                  'terms' => $term->term_id,
                ],
                [
                  'taxonomy' => 'product_visibility',
                  'terms'     => ['exclude-from-catalog'],
                  'field'     => 'name',
                  'operator'  => 'NOT IN',
                ]
              ],
            ]))),
            'seasonal' => $seasonal,
          ];
        }
      }

      $query = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => 25,
        'tax_query' => [
          [
            'taxonomy' => 'product_visibility',
            'terms'     => ['exclude-from-catalog'],
            'field'     => 'name',
            'operator'  => 'NOT IN',
          ],
        ]
      ];

      if ($search) {
        $result['title'] = 'Search: ' . $search;
        $result['browse'] = getBrowse();
        $query['s'] = $search;
      }

      if (is_array($filters)) {
        foreach ($filters as $filter)
          $query['tax_query'][] = [
            'taxonomy' => $filter['tax'],
            'terms' => $filter['slug'],
            'field' => 'slug',
          ];
      }
    
      $products = get_posts($query);
      $result['products'] = array_map('getProductJson', $products);
    
      return $result;
    }
  ]);

  register_rest_route('echo/v1', '/shop', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $product = get_post($id);
      $product_object = wc_get_product($id);
      $attributes = get_post_meta($id, '_product_attributes');
      $atts = [];
      foreach ($attributes as $attribute_l1) {
        foreach ($attribute_l1 as $attr) {
          if (!$attr['is_visible'])
            continue;

          if ($attr['is_taxonomy']) {
            $tax = get_taxonomy($attr['name']);
            $name = html_entity_decode($tax->label);

            $terms = wp_get_post_terms($id, $attr['name']);
            foreach ($terms as $term)
              $values[] = html_entity_decode($term->name);

            $value = join(', ', $values);
            $values = [];
          } else {
            $name = $attr['name'];
            $value = $attr['value'];
          }

          $atts[] = [
            'name' => $name,
            'value' => $value,
          ];
        }
      }

      $categories = wp_get_post_terms($id, 'product_cat');
      $tags = wp_get_post_terms($id, 'product_tag');
      $upsells = $product_object->get_upsell_ids();

      return [
        'id' => $id,
        'img' => get_the_post_thumbnail_url($id, 'woocommerce_single'),
        'title' => html_entity_decode($product->post_title),
        'price' => get_post_meta($id, '_price', true),
        'desc' => html_entity_decode($product->post_content),
        'attributes' => $atts,
        'categories' => count($categories) ? [
          [
            'slug' => end($categories)->slug,
            'name' => html_entity_decode(end($categories)->name),
          ]
        ] : '',
        'tags' => array_map(function($tag) {
          return html_entity_decode($tag->name);
        }, $tags),
        'stock' => get_post_meta($id, '_stock_status', true),
        'upsells' => array_map(function($upsell) {
          return getProductJson($upsell);
        }, $upsells),
        'variations' => is_a($product_object, 'WC_Product_Variable') ? array_map(function($variation) {
          return [
            'id' => $variation['variation_id'],
            'attribute' => array_values($variation['attributes'])[0],
            'price' => $variation['display_price'],
          ];
        }, $product_object->get_available_variations()) : [],
      ];
    }
  ]);

  register_rest_route('echo/v1', '/shop_cart', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $items = $request->get_param("items");
      $coupons = $request->get_param("coupons");
      $wine_delivery = $request->get_param("wine_delivery");

      $cart = WC()->cart;
      $cart->empty_cart();
      $qtyPerSupplier = [];
      $moqPerSupplier = [];
      foreach ($items as $item) {
        $cart->add_to_cart($item['id'], $item['qty'], $item['variation'] ?: 0);
        $supplier = get_the_terms($item['id'], 'product_supplier');
        $supplier = count($supplier) ? $supplier[0] : false;
        if ($supplier) {
          isset($qtyPerSupplier[$supplier->slug]) ? $qtyPerSupplier[$supplier->slug] += $item['qty'] : $qtyPerSupplier[$supplier->slug] = $item['qty'];
          if (!isset($moqPerSupplier[$supplier->slug]))
            $moqPerSupplier[$supplier->slug] = get_field('product-supplier-moq', 'product_supplier_' . $supplier->term_id);
        }
      }

      $cart->set_shipping_total(0);
      foreach ($moqPerSupplier as $supplier => $moq)
        if ($qtyPerSupplier[$supplier] < $moq) {
          $cart->set_shipping_total(300);
          break;
        }

      if ($wine_delivery) {
        $user = wp_get_current_user();
        $numbers = getCardMemberNumbers($user);
        $aspen = aspen_sql("SELECT MEMB_BADD1, MEMB_RADD1, MEMB_RMAIL FROM CMSMEMB0 WHERE MBNUM='{$numbers[1]}'");
        $residential = $aspen[0]['MEMB_RADD1'];
        $business = $aspen[0]['MEMB_BADD1'];
        $email = $aspen[0]['MEMB_RMAIL'];
      }

      foreach ($coupons as $item) {
        $cart->apply_coupon($item);
      }

      $coupons = $cart->get_coupon_discount_totals();

      $crosssell = (array)get_field('crosssell', 'option');

      $items = array_map(function($item) {
        $product = wc_get_product($item['id']);

        if (!$product)
          return ['id' => 0];

        $categories = get_the_terms($item['id'], 'product_cat');
        $cats = [];
        foreach ( $categories as $category ) {
          $cats[] = [
            'slug' => $category->slug,
            'name' => html_entity_decode($category->name),
          ];
          $anc = get_ancestors($category->term_id, 'product_cat', 'taxonomy');
          if (!empty($anc)) {
            foreach ($anc as $term) {
              $t = get_term_by( 'term_id', $term, 'product_cat');
              $cats[] = [
                'slug' => $t->slug,
                'name' => html_entity_decode($t->name),
              ];
            }
          }
          break; 
        }
        
        return [
          'id' => $item['id'],
          'qty' => $item['qty'],
          'price' => $product->get_price(false),
          'img' => get_the_post_thumbnail_url($item['id'], 'medium'),
          'title' => $product->get_name(),
          'categories' => $cats,
        ];
      }, $items);
      $items = array_filter($items, function($item) { return $item['id'] !== 0; });

      return [
        'items' => $items,
        'subtotal' => $cart->get_subtotal(),
        'coupons' => $coupons,
        'shipping' => $cart->get_shipping_total(),
        'total' => $cart->get_total(false),
        'residential' => $residential,
        'business' => $business,
        'email' => $email,
        'crosssell' => getProductJson($crosssell[array_rand($crosssell)]),
      ];
    }
  ]);

  register_rest_route('echo/v1', '/shop_order', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $items = $request->get_param("items");
      $coupons = $request->get_param("coupons");
      $address = sanitize($request->get_param("address"));
      $email = sanitize($request->get_param("email"));
      $date = sanitize($request->get_param("date"));
      $pickupDate = sanitize($request->get_param("pickupDate"));
      $note = sanitize($request->get_param("note"));

      if ($note)
        $notes[0] = $note;

      $cart = WC()->cart;
      $cart->empty_cart();
      $qtyPerSupplier = [];
      $moqPerSupplier = [];
      foreach ($items as $item) {
        $cart->add_to_cart($item['id'], $item['qty'], $item['variation'] ?: 0);
        
        if ($item['note'])
          $notes[$item['id']] = $item['note'];
          
        $supplier = get_the_terms($item['id'], 'product_supplier');
        $supplier = count($supplier) ? $supplier[0] : false;
        if ($supplier) {
          isset($qtyPerSupplier[$supplier->slug]) ? $qtyPerSupplier[$supplier->slug] += $item['qty'] : $qtyPerSupplier[$supplier->slug] = $item['qty'];
          if (!isset($moqPerSupplier[$supplier->slug]))
            $moqPerSupplier[$supplier->slug] = get_field('product-supplier-moq', 'product_supplier_' . $supplier->term_id);
        }
      }
      if ($cart->get_cart_contents_count() == 0) {
        return new WP_Error('Error', 'The cart is empty');
      }

      foreach ($coupons as $item) {
        $cart->apply_coupon($item);
      }

      $checkout = WC()->checkout();
      $order_id = $checkout->create_order([]);
      $order = wc_get_order($order_id);
      
      // $order->set_shipping_address_1($address);
      // $shipping = false;
      // foreach ($moqPerSupplier as $supplier => $moq)
      //   if ($qtyPerSupplier[$supplier] < $moq) {
      //     $shipping = true;
      //     break;
      //   }
      // if ($shipping) {
      //   $shipping_rate = new WC_Shipping_Rate( '', 'Flatrate', 300, 0, 'flatrate_shipping_method' );
      //   $order->add_shipping($shipping_rate);
      // } else {
      //   $shipping_rate = new WC_Shipping_Rate( '', 'Free', 0, 0, 'flatrate_shipping_method' );
      //   $order->add_shipping($shipping_rate);
      // }

      update_post_meta($order_id, '_customer_user', get_current_user_id());
      update_post_meta($order_id, '_billing_email', $email);
      update_post_meta($order_id, '_shipping_delivery_date', $date);
      update_post_meta($order_id, '_shipping_pickup_date', $pickupDate);
      $order->calculate_totals();
      $order->payment_complete(); 
      if ($notes)
        $order->add_order_note(json_encode($notes), true);
      $cart->empty_cart();

      if ($order_id)
        return 'OK';
      else
        return new WP_Error('Error', 'The order could not be created');
    }
  ]);

  register_rest_route('echo/v1', '/events', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $page = sanitize($request->get_param("page"));
      $search = sanitize($request->get_param("search"));
      $category = sanitize($request->get_param("category"));
      $cats = sanitize($request->get_param("cats"));
      $cats = empty($cats) ? [] : explode(',', $cats);
      $date = sanitize($request->get_param("date"));
      $view = sanitize($request->get_param("view"));
      $calendarView = sanitize($request->get_param("calendarView"));
      $venue = sanitize($request->get_param("venue"));
      $direction = sanitize($request->get_param("direction"));
      $first = sanitize($request->get_param("first")) === 'true';
    
      $user = wp_get_current_user();
      update_user_meta($user->ID, 'eventstime', time());

      switch ($category) {
        case 'speakers':
          if ($first || empty($cats))
            $cats = ['speakers', 'club-breakfast', 'club-lunch', 'club-dinner', 'club-cocktail', 'club-screening', 'zoom-event'];
            
          $result['categories'] = catsArray([
            'speakers' => 'Speaker Event', 
            'club-breakfast' => 'Club Breakfast', 
            'club-lunch' => 'Club Lunch', 
            'club-dinner' => 'Club Dinner', 
            'club-cocktail' => 'Club Cocktail', 
            'club-screening' => 'Club Screening', 
            'zoom-event' => 'Zoom Event', 
          ], $cats);
          break;

        case 'promotional-events':
          if ($first || empty($cats))
            $cats = ['promotional-events', 'themed-dinner', 'live-band-performance', 'quiz-night'];
            
          $result['categories'] = catsArray([
            'promotional-events' => 'Promotional Events', 
            'themed-dinner' => 'Themed Dinner', 
            'live-band-performance' => 'Live Band Performance',
            'quiz-night' => 'Quiz Night',  
          ], $cats);
          break;

        case 'wall-exhibition':
          $cats = ['wall-exhibition'];
          $result['categories'] = [];
          break;

        case 'sports-schedule':
          $cats = ['sports-schedule'];
          $result['categories'] = [];
          break;

        case 'others':
          if ($first || empty($cats))
            $cats = ['others', 'induction-ceremony', 'diplomatic-cocktail', 'journalism-conference'];
            
          $result['categories'] = catsArray([
            'others' => 'Others', 
            'induction-ceremony' => 'Induction Ceremony', 
            'diplomatic-cocktail' => 'Diplomatic Cocktail', 
            'journalism-conference' => 'Journalism Conference',
          ], $cats);
          break;

        case 'events':
        default:
          if ($first || empty($cats))
            $cats = ['promotional-events', 'wall-exhibition', 'quiz-night', 'speakers', 'others'];
          
          $result['categories'] = catsArray([
            'speakers' => 'Speaker Events', 
            'club-breakfast' => 'Club Breakfast', 
            'club-lunch' => 'Club Lunch', 
            'club-dinner' => 'Club Dinner', 
            'club-cocktail' => 'Club Cocktail', 
            'club-screening' => 'Club Screening', 
            'zoom-event' => 'Zoom Event', 
            'promotional-events' => 'Promotional Events', 
            'themed-dinner' => 'Themed Dinner', 
            'live-band-performance' => 'Live Band Performance', 
            'quiz-night' => 'Quiz Night', 
            'induction-ceremony' => 'Induction Ceremony',
            'diplomatic-cocktail' => 'Diplomatic Cocktail', 
            'journalism-conference' => 'Journalism Conference',
            'wall-exhibition' => 'Wall Exhibitions',
            'others' => 'Others',
          ], $cats);
          break;
  
      }

      $timestamp = $date !== 'null' ? strtotime($date) : time();
      if ($view == 'calendar') {
        if ($calendarView == 'day') {
          $ends_after = date('Y-m-d 00:00:00', $timestamp);
          $starts_before = date('Y-m-d 00:00:00', strtotime('+1 day', $timestamp));
        } elseif ($calendarView == 'week') {
          $day = date('N', $timestamp);
          $ends_after = date('Y-m-d 00:00:00', strtotime('-' . ($day - 1) . ' days', $timestamp));
          $starts_before = date('Y-m-d 00:00:00', strtotime('+' . (8 - $day) . ' days', $timestamp));
        } elseif ($calendarView == 'month') {
          $day = date('d', $timestamp);
          $ends_after = date('Y-m-d 00:00:00', strtotime('-' . ($day - 1) . ' days', $timestamp));
          $starts_before = date('Y-m-d 00:00:00', strtotime('+' . (31 - $day) . ' days', $timestamp));
        }
      } else {
        if ($direction === 'true') {
          $starts_before = "9999-99-99 99:99:99";
          $ends_after = date('Y-m-d 00:00:00', strtotime('+1 day', $timestamp));
          $newDate = $ends_after;
          $result['date'] = substr($ends_after, 0, 10);
        } elseif ($direction === 'false') {
          $starts_before = date('Y-m-d 00:00:00', strtotime('-1 day', $timestamp));
          $ends_after = "0000-00-00 00:00:00";
          $newDate = $starts_before;
          $result['date'] = substr($starts_before, 0, 10);
        } else {
          if ($date !== 'null') {
            $starts_before = $date . ' 23:59:59';
            $ends_after = $date . ' 00:00:00';
          } else {
            $starts_before = "9999-99-99 99:99:99";
            $ends_after = "0000-00-00 00:00:00";
          }
        }
      }

      $tax_query = [
        [
          'taxonomy' => 'event-categories',
          'field' => 'slug',
          'terms' => ['club-events', 'music', 'private-event', 'speakers', 'sports-schedule', 'wall-exhibition'],
        ],
      ];
      if (!empty($cats))
        $tax_query = [
          [
            'taxonomy' => 'event-categories',
            'terms' => $cats,
            'field' => 'slug',
          ]
        ];
    
      $events = get_posts([
        'post_type' => 'event',
        'post_status' => 'publish',
        'paged' => $page,
        's' => $search,
        'posts_per_page' => $view == 'calendar' ? -1 : 10,
        'meta_query' => [
          'relation' => 'AND',
          'start' => [
            'key' => '_event_start_local',
            'value' => $starts_before,
            'compare' => '<=', 
          ],
          'end' => [
            'key' => '_event_end_local',
            'value' => $ends_after,
            'compare' => '>', 
          ],
          'venue' => !$venue || $venue == 'All venues' ? null : [
            'key' => 'event_venue',
            'value' => $venue,
            'compare' => 'LIKE'
          ] 
        ],
        'orderby' => ($direction == '' || $direction === 'true') && $date !== 'null' ? 
          [
            'start' => 'ASC'
          ] :
          [
            'end' => 'DESC',
          ],
        'tax_query' => $tax_query,
      ]);

      $result['events'] = [];
      foreach ($events as $event) {
        $thumb = get_the_post_thumbnail_url($event->ID, 'thumb');
        $start = fcc_event_start_timestamp($event);
        $end = fcc_event_end_timestamp($event);

        if ($direction != '')
          if (!$day) {
            if (date("Y-m-d 00:00:00", $start) > $newDate || date("Y-m-d 99:99:99", $end) < $newDate) {
              $day = date("Y-m-d", $direction === 'true' ? $start : $end);
              $result['date'] = $day;
            } else
              $day = substr($newDate, 0, 10);
            
          } else if (date("Y-m-d 00:00:00", $start) > $day || date("Y-m-d 99:99:99", $end) < $day)
            break;
        
        $item = [
          'thumb' => $thumb,
          'id' => $event->ID,
          'slug' => $event->post_name,
          'title' => html_entity_decode($event->post_title),
          'start' => $start,
          'end' => $end,
        ];

        $all_day = get_field('_event_all_day', $event->ID);
        if ($all_day && $view == 'calendar' && ($calendarView == 'day' || $calendarView == 'week')) {
          $endStamp = $item['end'];

          while ($item['start'] + 60*60*24 <= $endStamp) {
            $item['end'] = $item['start'] + 60*60*24;
            $result['events'][] = $item;
            $item['start'] += 60*60*24;
          }
        } else
          $result['events'][] = $item;
      }

      if ($view == 'calendar') {
        $privates = get_posts([
          'post_type' => 'private_request',
          'post_status' => 'publish',
          'posts_per_page' => 1,
          'title' => !$venue || $venue == 'All venues' ? null : $venue,
          'meta_query' => [
            'relation' => 'AND',
            'start' => [
              'key' => 'date',
              'value' => $starts_before,
              'compare' => '<=', 
            ],
            'end' => [
              'key' => 'end',
              'value' => $ends_after,
              'compare' => '>=', 
            ],
            [
              'key' => 'status',
              'value' => 'Confirmed',
              'compare' => '=', 
            ]
          ],
        ]);

        foreach ($privates as $private)
          $result['events'][] = [
            'title' => "Private Function [{$private->post_title}]",
            'start' => strtotime(get_post_meta($private->ID, 'date', true)),
            'end' => strtotime(get_post_meta($private->ID, 'end', true)),
          ];
      }
    
      return $result;
    }
  ]);

  function catsArray($cats, $selected) {
    return array_map(function($slug, $name) use ($selected) {
      return [
        'slug' => $slug,
        'name' => $name,
        'sel' => in_array($slug, $selected) ? 1 : 0,
      ];
    }, array_keys($cats), $cats);
  }

  register_rest_route('echo/v1', '/event', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $event = get_post($id);
      $meta = get_fields($id, false);
      $user = wp_get_current_user();

      $poster = get_the_post_thumbnail_url($event, 'medium_large');
      $signups = $meta['event_signups'];

      $signup = 'no';
      if ($user->user_login && $meta['event_quota'] > 0 && $meta['_event_end_local'] > date("Y-m-d h:i:s")) {
        $count = count(array_filter($signups, fn($signup) => $signup['member'] == $user->ID));
        if ($count > 0)
          $signup = 'already';
        else {
          $count = $signups ? count($signups) : 0;
          
          if ($meta['event_cutoff'] && $meta['event_cutoff'] < date("Y-m-d h:i:s")) {
            $signup = $meta['event_message'] ?: "The signup deadline has passed";
          } else if ($count < $meta['event_quota']) {
            $signup = 'yes';
            $seats = min((!$meta['event_quota'] ? PHP_INT_MAX : $meta['event_quota']) - $count, $meta['event_guests'] + 1);
          } else if ($meta['event_waitinglist']) {
            $signup = 'waitinglist';
            $seats = $meta['event_guests'] + 1;
          } else
            $signup = 'full';
        }
      }

      $res = (object)array(
        'id' => $id,
        'date' => fcc_event_start_timestamp($event),
        'end' => fcc_event_end_timestamp($event),
        'venue' => $meta['event_venue'],
        'title' => html_entity_decode($event->post_title),
        'content' => wpautop(do_shortcode(html_entity_decode($event->post_content))),
        'poster' => $poster,
        'signup' => $signup,
        'seats' => $seats,
        'meal' => array_filter(explode("\r\n", $meta['event_meal']), fn($meal) => trim($meal) != ""),
      );

      return $res;
    },
  ]);

  register_rest_route('echo/v1', '/event_signup', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $user = wp_get_current_user();
      $event = intval(sanitize($request->get_param('event')));
      $people = $request->get_param('people');
      $meta = get_fields($event);

      $signups = $meta['event_signups'];
      if (count(array_filter($signups, fn($signup) => $signup['member'] == $user->ID)) > 0)
        return "EXISTS";

      $added = 0;
      $signupsCount = count($signups);
      $waitinglist = false;
      foreach ($people as $signup) {
        if ($added < $meta['event_guests'] + 1 && (count($signups) + $added < $meta['event_quota'] || $meta['event_waitinglist'])) {
          $waitinglist = $signupsCount + $added >= $meta['event_quota'];

          $signups[] = [
            'member' => $user->ID,
            'name' => $signup['name'],
            'email' => $signup['email'],
            'meal' => $signup['meal'],
            'waitinglist' => $waitinglist
          ];
          $added++;
        }
      }
      update_field('event_signups', $signups, $event);

      if ($added > 0) {
        $event = get_post($event);
        $eventTitle = html_entity_decode($event->post_title);

        $message = "Dear {$user->display_name},<br/>
          <br/>
          Thank you for your Event signup.<br/>
          <br/>
          Event: <a href='". get_site_url() ."/members-area#/app/events_event/{$event->ID}'><strong>$eventTitle</strong></a><br/>
          <br/>
          Date: " . fcc_event_date($event) . "<br/>
          <br/>";

        if ($waitinglist)
          $message .= "<br/>NOTICE: You are signed up for a waiting list. We will contact you in case some slots become available for you.<br/>";

        send_email(get_user_meta($user->ID, 'email'), 'Event Signup', $message);
        
        return 'OK';
      }

      return 'FULL';
    }
  ]);

  register_rest_route('echo/v1', '/event_cancel', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $user = wp_get_current_user();
      $event = intval(sanitize($request->get_param('event')));
      $event = get_post($event);
  
      $signups = get_field('event_signups', $event->ID);
      $active = array_filter((array)$signups, fn($signup) => $signup['member'] == $user->ID && !$signup['waitinglist']);
      $signups = array_filter((array)$signups, fn($signup) => $signup['member'] != $user->ID);
      $activate = count($active);
      foreach ($signups as $key => $signup)
        if ($signup['waitinglist'] && $activate > 0) {
          $signups[$key]['waitinglist'] = false;
          $activate--;
  
          $activateUser = get_user_by('id', $signup['member']);
  
          $message = "Dear {$activateUser->display_name},<br/>
            <br/>
            One of your Event signups has been moved from the waiting list to the active list.<br/>
            <br/>
            Event: <a href='". get_site_url() ."/members-area#/app/events_event/{$event->ID}'><strong>{$event->post_title}</strong></a><br/>
            Guest: {$signup['name']}<br/>";
  
          send_email(get_user_meta($activateUser->ID, 'email'), 'Event Signup Updated', $message);
        }
        
      update_field('event_signups', $signups, $event->ID);
  
      $message = "Dear {$activateUser->display_name},<br/>
        <br/>
        Your Event signup has been cancelled.<br/>
        <br/>
        Event: <a href='". get_site_url() ."/members-area#/app/events_event/{$event->ID}'><strong>{$event->post_title}</strong></a><br/>";
  
      send_email(get_user_meta($activateUser->ID, 'email'), 'Event Signup Cancelled', $message);
  
      return 'OK';
    }
  ]);

  register_rest_route('echo/v1', '/promotions', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $page = sanitize($request->get_param("page"));
      $search = sanitize($request->get_param("search"));
      $cats = sanitize($request->get_param("cats"));
      $first = sanitize($request->get_param("first"));
      $parent = get_term_by('slug', 'fb-promotions', 'event-categories');
      $cat = empty($cats) ? [$parent->slug] : explode(',', $cats);
    
      $user = wp_get_current_user();
      update_user_meta($user->ID, 'promotionstime', time());
    
      if ($first) {
        $cats_cache = get_user_meta($user->ID, 'cats', true);
        if ($cats_cache != "")
          $cats = implode(",", $cats_cache);
      }
  
      $starts_before = "9999-99-99 99:99:99";
      $ends_after = date("Y-m-d 00:00:00");
      
      $promotions = get_posts([
        'post_type' => 'event',
        'post_status' => 'publish',
        'paged' => $page,
        's' => $search,
        'posts_per_page' => 10,
        'meta_query' => [
          'relation' => 'AND',
          'start' => [
            'key' => '_event_start_local',
            'value' => $starts_before,
            'compare' => '<=', 
          ],
          'end' => [
            'key' => '_event_end_local',
            'value' => $ends_after,
            'compare' => '>=', 
          ],
        ],
        'orderby' => [
          'start' => 'ASC'
        ],
        'tax_query' => [
          [
            'taxonomy' => 'event-categories',
            'field' => 'slug',
            'terms' => $cat,
          ],
        ],
      ]);
      $result['items'] = [];
      foreach ($promotions as $event) {
        $thumb = get_the_post_thumbnail_url($event->ID, 'thumb');
        $desc = mb_strimwidth(trim(strip_tags(do_shortcode(html_entity_decode($event->post_content)))), 0, 300, '...');
        
        $result['items'][] = [
          'thumb' => $thumb,
          'id' => $event->ID,
          'slug' => $event->post_name,
          'title' => html_entity_decode($event->post_title),
          'start' => fcc_event_start_timestamp($event),
          'end' => fcc_event_end_timestamp($event),
          'desc' => preg_replace("/[\n\r]+/", " ", $desc),
        ];
      }
    
      $terms = get_terms([
        'taxonomy' => 'event-categories',
        'orderby' => 'name',
        'order'   => 'ASC',
        'child_of' => $parent->term_id,
        'hide_empty' => false,
      ]);
    
      $categories = [];
      foreach($terms as $term) {
        $categories[] = [
          'slug' => $term->slug,
          'name' => html_entity_decode($term->name),
          'sel' => in_array($term->slug, $cat) ? 1 : 0,
          'order' => get_field('order', "event-categories_{$term->term_id}"),
        ];
      }
      usort($categories, function($a, $b) { return $a['order'] - $b['order']; });
      foreach ($categories as $key => $category)
        unset($categories[$key]['order']);

      $result['categories'] = $categories;
    
      return $result;
    }
  ]);

  register_rest_route('echo/v1', '/promotion', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function(WP_REST_Request $request) {
      $id = sanitize($request->get_param("id"));
      $promotion = get_post($id);
      $meta = get_fields($id);
      
      $res = (object)array(
        'id' => $id,
        'thumb' => get_the_post_thumbnail_url($promotion, 'medium_large'),
        'start' => fcc_event_start_timestamp($promotion),
        'end' => fcc_event_end_timestamp($promotion),
        'venue' => $meta['event_venue'],
        'title' => html_entity_decode($promotion->post_title),
        'content' => preg_replace('/width: \d+px/', '', preg_replace('/ width="\d+"/', '', html_entity_decode($promotion->post_content))),
      );

      return $res;
    },
  ]);

  global $member_details;
  $member_details = ['MEMB_MOBIL', 'MEMB_CONM', 'MEMB_POSTN', 'MEMB_BADD1', 'MEMB_RADD1', 'MEMB_BTEL', 'MEMB_RTEL', 'MEMB_BMAIL', 'MEMB_RMAIL', 'MEMB_SMOBI', 'MEMB_SPCON', 'MEMB_SPPOS', 'MEMB_SPAD1', 'MEMB_SPBTE', 'MEMB_SMAIL', 'MEMB_SPREM', 'MEMB_ECNA', 'MEMB_ECNO'];
  
  register_rest_route('echo/v1', '/user_info', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      global $member_details;
      $user = wp_get_current_user();
      $numbers = getCardMemberNumbers($user);
      $webid = $user->user_login;

      $cardDetails = aspen_sql("SELECT CARD_MOBIL, CARD_EMAIL FROM MBSCARD0 WHERE CARDNO='$numbers[0]'"); 
      $response['cardDetails'] = $cardDetails[0];

      $fields = implode(',', $member_details);
      $memberDetails = aspen_sql("SELECT {$fields} FROM CMSMEMB0 WHERE MBNUM=$numbers[1]"); 
      $response['details'] = $memberDetails[0];
    
      // $member_number = get_user_meta($user->ID, 'user_member_number', true);
      // $result = lrc_http("<RequestMessage ElementType=\"SUBSCRIPTION\"> 
      //   <mbnum>$member_number</mbnum>
      //   </RequestMessage>");
      // $subs = getResults($result);
      // $com = [];
      // foreach ($subs as $sub) {
      //   $channels = explode( ',', $sub['Subemail'] );
      //   $comcat = ['slug' => $sub['Subcat'], 'id' => $sub['Subcat']]; //'id' for backwards compat (<=1.24)
      //   foreach ( $channels as $channel ) {
      //     $comcat[$channel] = true;
      //   }
      //   $com[] = $comcat;
      // }
      // $response['com'] = $com;
    
      // $result = lrc_http("<RequestMessage ElementType=\"SUBSCRIPTION2\"> 
      //   <cardno>$user->user_login</cardno>
      //   </RequestMessage>");
    
      // $subs = getResults($result);
      // $cats = [];
      // get_hierarchical_categories(array(
      //   'exclude' => '1,96',
      // ), $cats);
      // foreach ($cats as &$cat) {
      //   foreach ($subs as $sub) {
      //     if ( $sub['Subcat'] == $cat['slug'] ) {
      //       $channels = explode( ',', $sub['Subemail'] );
      //       foreach ( $channels as $channel ) {
      //         $cat[ $channel ] = true;
      //       }
      //     }
      //   }
      // }
    
      // $response['categories'] = $cats;
      $response['webid'] = $webid;
      return $response;
    }
  ]);
  
  register_rest_route('echo/v1', '/user_info', [
    'methods' => 'POST',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      global $member_details;
      $user = wp_get_current_user();
      $numbers = getCardMemberNumbers($user);
      $webid = $user->user_login;
      $newwebid = trim(sanitize($request->get_param('webid')));
      $details = $request->get_param('details');
      $cardDetails = $request->get_param('cardDetails');
      $cats = $request->get_param('cats');
      $com = $request->get_param('com');
      $password = $request->get_param('password');
      $newpassword = $request->get_param('newpassword');
      $code = $request->get_param('code');
    
      $errors = [];
    
      if (!empty($newpassword) && trim($newpassword) != '') {
        if (!empty($password) && trim($password) != '') {
          if ($user->user_activation_key !== $code)
            $errors[] = "Security code is incorrect";
          else if (!wp_check_password($password, $user->user_pass, $user->ID))
            $errors[] = "Current Password is incorrect";
          else
            wp_set_password( $newpassword, $user->ID );
        } else {
          $errors[] = "Current Password is empty";
        }
      } else if (!empty($code)) {
        $code = rand(10000, 99999);
        wp_update_user([
          'ID' => $user->ID,
          'user_activation_key' => $code,
        ]);

        $email = get_user_meta($user->ID, 'email', true);
        send_email($email, 'Security Code', "Dear {$user->display_name},<br/><br/>Someone has requested a security code for your account. If it wasn't you just ignore this message.<br/><br/>Security Code: $code<br/><br/>");

        return [
          'email' => obfuscateEmail($email),
        ];
      }
    
      if (!empty($newwebid) && $newwebid != $webid) {
        $exists = get_user_by('login', $newwebid);
        if ($exists)
          $errors[] = "Username: already exists";
        else {
          global $wpdb;
          $updated = $wpdb->update(
            $wpdb->users, 
            ['user_login' => $newwebid], 
            ['ID' => $user->ID]
          );
          if ($updated === false)
            $errors[] = "Username could not be changed";
        }
      }
    
      if (!empty($cardDetails)) {
        $CARD_MOBIL = sanitize($cardDetails['CARD_MOBIL']);
        $CARD_EMAIL = sanitize($cardDetails['CARD_EMAIL']);
        $result = aspen_sql("UPDATE MBSCARD0 SET CARD_MOBIL='{$CARD_MOBIL}', CARD_EMAIL='{$CARD_EMAIL}' WHERE CARDNO='$numbers[0]'");
        if (!$result)
          $errors[] = "There was an error trying to update the Card Details. Please try again later.";
      }
    
      if (!empty($details)) {
        $updates = [];
        foreach ($details as $key => $value) {
          $value = sanitize($value);
          if (in_array($key, $member_details))
            $updates[] = "$key = '$value'";
        }
    
        $updates = implode(',', $updates);
        $result = aspen_sql("UPDATE CMSMEMB0 SET {$updates} WHERE MBNUM=$numbers[1]");
        if (!$result)
          $errors[] = "There was an error trying to update the Details. Please try again later.";
      }
    
      // if (!empty($cats)) {
      //   $sub = [];
      //   foreach ($cats as $cat) {
      //     if (is_numeric($cat['id']) && ($cat['B'] === true || $cat['B'] === 'true' || $cat['R'] === true || $cat['R'] === 'true' || $cat['P'] === true || $cat['P'] === 'true'))
      //       $sub[] = $cat['id'];
      //   }
      //   $sub[] = 61;
      //   $sub[] = 96;
    
      //   update_user_meta($user->ID, 'cats', $sub);
      // }
    
      // function buildRequest($cats) {
      //   $request = '';
      //   foreach ($cats as $cat) {
      //     $sub = [];
      //     foreach (array('B', 'R', 'S', 'Y', 'P') as $item)
      //       if ($cat[$item] === true || $cat[$item] === 'true')
      //         $sub[] = $item;
    
      //     $request .= count($sub) ? "<Subscription><Subcat>" . (isset($cat['slug']) ? $cat['slug'] : $cat['id']) . "</Subcat><Subemail>" . implode(',', $sub) . "</Subemail></Subscription>" : "";
      //   }
    
      //   return $request;
      // }
    
      // if (isset($com)) {
      //   $request = buildRequest($com);
    
      //   $member_number = get_user_meta($user->ID, 'user_member_number', true);
    
      //   $result = lrc_http("<RequestMessage ElementType=\"SUBUPDATE\" mbnum=\"$member_number\">
      //     $request
      //     </RequestMessage>");
      //   $doc = new SimpleXMLElement($result);
    
      //   if ((String)$doc->Response->AnswerStatus == 'DN') {
      //     $errors[] = "Communication: " . (String)$doc->Response->Message;
      //   }
    
      //   //some parameters are physically in the details section
      //   $updates = "";
      //   foreach ($details as $key => $value) {
      //     if (in_array($key, ['MAGAZINE', 'AGMM', 'AGMS', 'MEMB_BMED', 'MEMB_ETO3']))
      //       $updates .= "<$key>$value</$key>";
      //   }
    
      //   $result = lrc_http("<RequestMessage ElementType=\"MEMBERUPDATE\">
      //     <mbnum>$member_number</mbnum>
      //     $updates
      //     </RequestMessage>");
      //   $doc = new SimpleXMLElement($result);
    
      //   if ((String)$doc->Response->AnswerStatus == 'DN') {
      //     $errors[] = "Communication: " . (String)$doc->Response->Message;
      //   }
      // }
    
      // if (isset($cats)) {
      //   $request = buildRequest($cats);
    
      //   $request .= "<Subscription><Subcat>clubevnt</Subcat><Subemail>B,R,P</Subemail></Subscription>";
      //   $request .= "<Subscription><Subcat>specannc</Subcat><Subemail>B,R,P</Subemail></Subscription>";
      //   $result = lrc_http("<RequestMessage ElementType=\"SUBUPDATE2\" cardno=\"$user->user_login\">
      //     $request
      //     </RequestMessage>");
      //   $doc = new SimpleXMLElement($result);
    
      //   if ((String)$doc->Response->AnswerStatus == 'DN') {
      //     $errors[] = "Interests: " . (String)$doc->Response->Message;
      //   }
      // }
    
      if (count($errors) > 0) {
        $response['return_code'] = "DN";
        $response['message'] = implode(",\n", $errors);
      } else {
        $response['return_code'] = "OK";
      }
    
      return $response;
    }
  ]);

  register_rest_route('echo/v1', '/orders', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $orders = wc_get_orders(['customer_id' => get_current_user_id()]);
      return array_values(array_map(function($order) {
        return [
          'id' => $order->get_id(),
          'date' => $order->get_date_created()->getTimestamp(),
          'total' => $order->get_total(),
          'status' => $order->get_status(),
          'products' => array_values(array_map(function($item) {
            $terms = get_the_terms( $item->get_product_id(), 'product_cat' );
            return [
              'id' => $item->get_product_id(),
              'img' => get_the_post_thumbnail_url($item->get_product_id(), 'medium'),
              'title' => $item->get_name(),
              'qty' => $item->get_quantity(),
              'price' => $item->get_subtotal(),
              'wine_delivery' => count(array_filter($terms, function($term) { return $term->slug === 'wine-delivery'; })) > 0,
            ];
          }, $order->get_items())),
        ];
      }, $orders));
    }
  ]);

  register_rest_route('echo/v1', '/order_cancel', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('read');
    },
    'callback' => function(WP_REST_Request $request) {
      $order = wc_get_order($request->get_param("id"));
  
      if($order && $order->get_customer_id() == get_current_user_id()) {
        $result = $order->update_status('cancelled', '', true);
        if ($result)
          return 'OK';
      }
    }
  ]);
});

add_filter( 'woocommerce_is_rest_api_request', function( $is_rest_api_request ) {
  if ( empty( $_SERVER['REQUEST_URI'] ) )
    return $is_rest_api_request;
  if ( false === strpos( $_SERVER['REQUEST_URI'], '/wp-json/echo/' ) ) {
    return $is_rest_api_request;
  }
  return false;
});