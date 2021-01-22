<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var string $subjects */
/** @var string $shop_name */
/** @var string $shop_logo */
/** @var string $shop_url */
/** @var string $customer_name */
/** @var array  $products */
/** @var string $complete_payment_link */
?>
<?php echo $view->__('reminder.dear', ['%name%' => '<span style="color: #306ba8">' . $customer_name . '</span>'], 'email'); ?>,<br/>
<?php echo $view->__('reminder.text1', ['%shop_name%' => '<br/><a href="' . $shop_url . '" style="text-decoration: none; color: #306ba8">' . $shop_name . '</a>'], 'email'); ?><br />
<?php echo $view->__('reminder.text2', [], 'email'); ?><br/>
<br/>
<table style="width:100%;border-collapse: collapse;">
    <thead>
    <tr bgcolor="#f2f5f7">
        <th style="font-size: 15px; font-weight: 300; text-align: left; padding: 6px 13px" width="90%">
            <?php echo $view->__('reminder.item', [], 'email'); ?>
        </th>
        <th style="font-size: 15px; font-weight: 300; text-align: left; padding: 6px 13px; text-align: right" width="10%">
            <?php echo $view->__('reminder.price', [], 'email'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $product): ?>
        <tr>
            <td style="padding: 6px 10px; line-height: 75px" width="90%">
                <img style="float: left; margin-right: 10px" height="75px" src="<?php echo $product['image']; ?>" alt="<?php echo $shop_name; ?>"/>
                <?php echo $product['name']?>
            </td>
            <td style="padding: 6px 10px; font-weight: 600; color: #000; text-align: right" width="10%">
                <?php echo $product['price']?>
            </td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td style="padding: 6px 10px; text-align: right"width="90%">
            <?php echo $view->__('reminder.total', [], 'email'); ?>:
        </td>
        <td style="padding: 6px 10px; font-weight: 600; color: #000; text-align: right"width="10%;">
            <?php echo $order_total?>
        </td>
    </tr>
    </tbody>
</table>
<hr style="margin: 20px 0; color: #ddd; background-color: #f2f5f7; height: 2px; border: 0;">
<div style="line-height: 50px; float: left">
    <?php echo $view->__('reminder.text3', [], 'email'); ?>
</div>
<a href="<?php echo $complete_payment_link; ?>" style="float: right; background: #306ba8; border-radius: 21px; padding: 15px 20px; color: #FFFFFF; text-decoration: none; font-weight: 600; font-size: 15px">
    <?php echo $view->__('reminder.text4', [], 'email'); ?>
</a>