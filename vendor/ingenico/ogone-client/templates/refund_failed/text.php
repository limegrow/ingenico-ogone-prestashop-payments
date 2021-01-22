<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
?>
<?php echo $view->__('refund_failed.dear', ['%name%' => $customer_name], 'email'); ?>,
<?php echo $view->__('refund_failed.text1', ['%order_id%' => $order_reference], 'email'); ?>
<?php echo $view->__('refund_failed.text2', [], 'email'); ?>
<?php echo $view->__('refund_failed.text3', [], 'email'); ?>
<?php echo $shop_name; ?> <?php echo $view->__('refund_failed.administration', [], 'email'); ?> [<?php echo $shop_url; ?>]