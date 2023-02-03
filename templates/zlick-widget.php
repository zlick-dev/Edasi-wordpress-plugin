<!-- TODO: Include this once in footer -->
<script src='https://cdn.jsdelivr.net/npm/zlick-widget@4'></script>
<div id="zlick-widget">
<span id="widget_pre_text" style="padding: 0 20px">
        <p><?= $zp_widget_text; ?></p>
    </span>
</div>
<script type="text/javascript">

    function xwwwfurlenc(srcjson){
        var urljson = "";
        var keys = Object.keys(srcjson);
        for(var i=0; i <keys.length; i++){
            urljson += (keys[i]) + "=" + (srcjson[keys[i]]);
            if(i < (keys.length-1)) {
                urljson+=decodeURIComponent("%26");
            }
        }
        return urljson;
    }


    function renderZlickWidget(zlickWidgetPurpose) {
        zlickWidget({
            env: '<?= $zp_environment; ?>',
            clientToken: '<?= $client_token; ?>',
            purpose: zlickWidgetPurpose,
            // In case of 'purchase'
            product: {
                id: '<?= $article_id; ?>', // Unique ID of the product
                amount: <?= $zp_article_price; ?> // amount in change (i.e. For 1.25 Euros, use 125 as value)
            },
            // In case of subscription
            subscription: {
                id: '<?= $zp_subscription_id; ?>' // Subscription ID
            },
            onAuthenticated: function ({ userId, accessInfoSigned }) {
                var post_data = {
                    'action': 'zp_authenticate_article',
                    'zp_user_id': userId,
                    'zp_article_id': '<?= $article_id ?>',
                    'zp_signed': accessInfoSigned,
                    'zp_nonce' : '<?= wp_create_nonce("zp_ajax_nonce")?>'
                };

                var xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.open('POST', '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>?action=zp_authenticate_article', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange=function()
                {
                    if (xhr.status==200)
                    {
                        if (xhr.responseText === 'reload') {
                            if (window._zp_delayed_reload === true) {
                                setTimeout(() => {
                                    window.location.reload();                                    
                                }, 3000);
                            } else {
                                window.location.reload();
                            }
                        }
                    } else {
                        return "something went wrong.";
                    }
                }
                xhr.send(xwwwfurlenc(post_data));
            },
            onPaymentComplete: function ({ data, signed }) {
                window._zp_delayed_reload = true;
            },
            onFailure: function ({ message }) {
                console.log(message);
            }
        }).render('#zlick-widget')
        const preText = document.getElementById('widget_pre_text')
        if (preText) {
            preText.hidden = true;
        }
    }

    renderZlickWidget('subscribe');
</script>