<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference*/
/** @var $ingenico_logo */
/** @var $order_view_url */
/** @var $platform_name */
?>
<?php echo $view->__('order_paid.dear', ['%name%' => $shop_name], 'email'); ?>,<br />
<br />
<?php echo $view->__('admin_order_paid.text1', ['%url%' => $order_view_url, '%order_id%' => $order_reference], 'email'); ?><br />
<br />
<?php echo $view->__('admin_order_paid.text3', [], 'email'); ?><br />
<br />
<?php echo $view->__('admin_authorization.regards', [], 'email'); ?>,<br/>
<?php echo $view->__('admin_authorization.team', ['%platform_name%' => $platform_name], 'email'); ?><br/>
