<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference*/
/** @var $order_url */
?>
<?php echo $view->__('authorization.dear', ['%name%' => $customer_name], 'email'); ?>,
<?php echo $view->__('authorization.text1', ['%url%' => $order_url, '%order_id%' => $order_reference], 'email'); ?>
<?php echo $view->__('authorization.text2', [], 'email'); ?>
<?php echo $shop_name; ?> <?php echo $view->__('authorization.administration', [], 'email'); ?> [<?php echo $shop_url; ?>]
