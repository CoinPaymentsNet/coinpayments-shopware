{namespace name="frontend/plugins/payment/coinpayments"}

<div class="coinpayments">
    {block name="cryptocurrency_payments_via_coinpayments"}
        <p class="none">
            <select name="coinpaymentsCurrency" id="coinpayments-currency" class="is--required" required="required" aria-required="true">
            </select>
        </p>
    {/block}
</div>
<script>
    fetch('/frontend/coinpayments/getrates')
        .then(
            function(response) {
                if (response.status !== 200) {
                    return;
                }
                response.json().then(function(data) {
                    select = document.getElementById('coinpayments-currency');
                    for (var i = 0; i <= data.length - 1; i++){
                        var opt = document.createElement('option');
                        if (data[i]['id'] === 'BTC') {
                            opt.setAttribute('selected', true)
                        }

                        opt.value = data[i].id;
                        opt.innerHTML = data[i].name;
                        select.appendChild(opt);
                    }
                });
            }
        )
        .catch(function(err) {
            console.log('Fetch Error :-S', err);
        });
</script>