<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
/** @var $order_url */
?>
<?php echo $view->__('order_paid.dear', ['%name%' => $customer_name], 'email'); ?>,<br />
<br />
<?php echo $view->__('order_paid.text1', ['%url%' => $order_url, '%order_id%' => $order_reference], 'email'); ?><br />
<br />
<?php echo $view->__('order_paid.text3', [], 'email') ?><br />
<br />
<a href="<?php echo $shop_url; ?>"><?php echo $shop_name; ?></a> <?php echo $view->__('order_paid.administration', [], 'email') ?>