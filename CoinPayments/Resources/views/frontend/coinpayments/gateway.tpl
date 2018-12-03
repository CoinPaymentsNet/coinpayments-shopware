{extends file="frontend/index/index.tpl"}

{block name="frontend_index_content"}
    <form name="coinpaymentsform" method="post" action="https://www.coinpayments.net/index.php">
        <input type="hidden" name="cmd" value="_pay_auto">
        <input type="hidden" name="reset" value="1">
        <input type="hidden" name="merchant" value="{$merchant}">
        <input type="hidden" name="item_name" value="{$itemName}">
        <input type="hidden" name="invoice" value="{$orderId}">
        <input type="hidden" name="custom" value="{$custom}">
        <input type="hidden" name="quantity" value="{$quantity}">
        <input type="hidden" name="allow_quantity" value="0">
        <input type="hidden" name="want_shipping" value="0">
        <input type="hidden" name="currency" value="{$currency}">
        <input type="hidden" name="shippingf" value="">
        <input type="hidden" name="taxf" value="">
        <input type="hidden" name="amountf" value="{$amount}">
        <input type="hidden" name="success_url" value="{$successUrl}">
        <input type="hidden" name="cancel_url" value="{$cancelUrl}">
        <input type="hidden" name="ipn_url" value="{$ipnUrl}">
        <input type="hidden" name="email" value="{$email}">
        <input type="hidden" name="first_name" value="{$firstName}">
        <input type="hidden" name="last_name" value="{$lastName}">
        <noscript><input type="submit" value="Click here to complete checkout at CoinPayments.net"></noscript>
    </form>
    <script type="text/javascript">
        document.coinpaymentsform.submit();
    </script>
{/block}
