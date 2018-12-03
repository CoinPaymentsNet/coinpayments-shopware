{extends file="frontend/index/index.tpl"}
{block name="frontend_index_content"}
    <p>Your transaction # is: <span>{$txn_id}</p>
    <p>We'll email you an order confirmation with details and tracking info.</p>
    <h3><a href="{$status_url}" target="_blank">Status link</a></h3>
    <div>
        <div>
            <p><span>Address</span>:<span>{$address}</span></p>
        </div>
        <div>
            <p><span>Amount</span>:<span>{$amount}</span></p>
        </div>
        <div>
            <p>
                <span>QR code</span>:
                <span><img class="thumb" src="{$qrcode_url}"></span>
            </p>
        </div>
    </div>
    </div>
{/block}
