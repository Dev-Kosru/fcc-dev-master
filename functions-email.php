<?php

add_filter('wpcf7_mail_html_header', 'email_header');
add_filter('wpcf7_mail_html_footer', 'email_footer');

function send_email($to, $subject, $content) {
  $message = email_header() . $content . email_footer();

	$headers = array();
	$headers[] = 'From: Foreign Correspondents Club <noreply@fcchk.org>' . "\r\n";
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	wp_mail( $to, $subject, $message, $headers );
}

function email_header() {
	ob_start(); 
  include "woocommerce/emails/email-header.php";
  return ob_get_clean();
}

function email_footer() {
	ob_start(); 
  include "woocommerce/emails/email-footer.php";
	return ob_get_clean();
}

add_filter('woocommerce_order_get_billing_email', 'echo_billing_email');
add_filter('woocommerce_customer_get_billing_email', 'echo_billing_email');
function echo_billing_email($email) {
  $user = get_user_by('email', $email);
  return get_user_meta($user->ID, 'email')[0];
}

add_action('woocommerce_email_customer_details', function($order, $sent_to_admin, $plain_text, $email) {
	$user = get_user_by('id', $order->data['customer_id']);
	echo "Member Name: {$user->display_name}<br/>";
	$numbers = getCardMemberNumbers($user);
	echo "Member Number: {$numbers[1]}<br/>";
  $pickupDate = $order->get_meta('_shipping_pickup_date');
	echo "Pickup Date: {$pickupDate}<br/>";

  $notes = wc_get_order_notes([
    'order_id' => $order->ID,
    'type' => 'customer',
  ]);

  if (count($notes)) {
    $notes = json_decode($notes[0]->content);

    if ($notes[0])
      echo "Note: $notes[0]";
  }
}, 10, 4);