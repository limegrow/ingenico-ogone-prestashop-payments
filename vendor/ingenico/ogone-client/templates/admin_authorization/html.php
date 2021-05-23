<?php
/** @phpcs:ignore Generic.Files.LineLength.TooLong */
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

<?php echo $view->__('admin_authorization.dear_manager', ['%name%' => $shop_name], 'email'); ?>,<br/>
<br/>
<?php echo $view->__('admin_authorization.text1', ['%url%' => $order_view_url, '%order_id%' => $order_reference], 'email'); ?><br/>
<br/>
<?php echo $view->__('admin_authorization.text2', ['%url%' => $order_view_url], 'email'); ?><br/>
<br/>
<?php echo $view->__('admin_authorization.regards', [], 'email'); ?>,<br/>
<?php echo $view->__('admin_authorization.team', ['%platform_name%' => $platform_name], 'email'); ?>
