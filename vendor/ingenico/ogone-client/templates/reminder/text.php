<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $products_html */
/** @var $products_text */
/** @var $complete_payment_link */
?>
<?php echo $view->__('reminder.dear', ['%name%' => $customer_name], 'email'); ?>,
<?php echo $view->__('reminder.text1', ['%shop_name%' => $shop_name], 'email'); ?>
<?php echo $view->__('reminder.text2', [], 'email'); ?>

<?php foreach ($products as $product): ?>
    <?php echo $product['name']?> <?php echo $product['price']?>
<?php endforeach; ?>

<?php echo $view->__('reminder.text3', [], 'email'); ?>
<?php echo $view->__('reminder.text4', [], 'email'); ?>

