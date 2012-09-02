<?php
/**
 * Settings to share session between subdomains (Internet Explorer share sessions by default)
 * http://php.net/manual/en/function.session-set-cookie-params.php
 *
 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 * DO NOT EDIT THIS FILE! --- use common/session.php instead
 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 *
 */
$cross_subdomain_session = array(
	'tolc.lo' => '.tolc.lo',
	'www.tolc.lo' => '.tolc.lo',
	'sub.tolc.lo' => '.tolc.lo',
);
$cross_domain = $cross_subdomain_session[$_SERVER['SERVER_NAME']];
if(isset($cross_domain)) {
	session_set_cookie_params(0, '/', $cross_domain, false, false);
}
session_start();
session_regenerate_id();
?>