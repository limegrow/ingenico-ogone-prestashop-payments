<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var string $contents */
/** @var string $locale */
/** @var string $shop_name */
/** @var string $shop_logo */
/** @var string $shop_url */
/** @var string $customer_name */
/** @var string $ingenico_logo */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?php echo substr($locale, 0, 2); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <title>Message from <?php echo $shop_name; ?></title>
    <style>
        a {
            text-decoration: none;
            color: #306ba8
        }
    </style>
</head>
<body style="-webkit-text-size-adjust:none;background-color:#fff;width:650px;font-family:Open-sans, sans-serif;color:#575665;font-size:15px;line-height:18px;margin:auto" >
<table class="table table-mail" style="width:100%;margin-top:10px;" cellspacing="0">
    <tr>
        <td class="space" style="width:20px;padding:7px 0">&nbsp;</td>
        <td align="left" style="padding:7px 0;">
            <?php echo $contents; ?>
        </td>
    </tr>
</table>
</body>
</html>