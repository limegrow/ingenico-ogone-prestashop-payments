<?php
/** @phpcs:ignore Generic.Files.LineLength.TooLong */
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
/** @var $ingenico_logo */
/** @var $platform_name */
?>
<?php echo $view->__('admin_refund_failed.hello', [], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.text1', ['%order_id%' => $order_reference], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.text2', [], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.text3', [], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.regards', [], 'email'); ?>,<br/>
<?php echo $view->__('admin_refund_failed.team', ['%platform_name%' => $platform_name], 'email'); ?><br/>
