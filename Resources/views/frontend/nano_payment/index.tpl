{extends file="frontend/checkout/finish.tpl"}


{block name="frontend_index_body_classes"}
    is--ctl-checkout is--act-finish is--user is--minimal-header
{/block}

{block name="frontend_index_content"}
    <script src="https://brainblocks.io/brainblocks.min.js"></script>
    <div class="content checkout--content finish--content">
        <div class="finish--teaser panel has--border is--rounded">
            <h2 class="panel--title teaser--title is--align-center">{s name="NanoPaymentHeadline"}Pay with Nano{/s}</h2>
            <div class="panel--body is--wide is--align-center">
                <p class="teaser--text">
                    {s name="NanoPaymentDetails"}
                        Please pay now. Simply click the button below for payment details!
                    {/s}
                </p>
                <div id="nano-button" style="display: inline-block;"></div>
                <p class="teaser--actions">
                    <a href="{$cancelUrl}" class="btn is--secondary teaser--btn-back is--icon-left"
                       title="{"{s name="CancelPaymentBackToConfirm"}Cancel payment{/s}"|replace:' ':'&nbsp;'}">
                        <i class="icon--arrow-left"></i>&nbsp;{"{s name="CancelPaymentBackToConfirm"}Cancel payment{/s}"|replace:' ':'&nbsp;'}
                    </a>
                </p>
            </div>
        </div>
    </div>
{/block}


//<![CDATA[
{block name="frontend_index_header_javascript_inline"}
    {$smarty.block.parent}
    brainblocks.Button.render({
        payment: {
            destination: '{$xrb_destination}',
            currency:    '{$currency}',
            amount:      '{$amount}'
        },

        onPayment: function(data) {
            var params = 'token=' + data.token + '&amount={$amount}&currency={$currency}&signature={$signature}';
            window.location = '{$returnUrl}?' + params;
        }

        }, '#nano-button');
{/block}
//]]>
