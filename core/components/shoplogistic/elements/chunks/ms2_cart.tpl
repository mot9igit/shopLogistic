{if !count($products)}
    <div class="alert alert-info">{'ms2_cart_is_empty' | lexicon}</div>
{else}
    <div class="msCart">
        <div class="row">
            <div class="col-6">
                <h1>Корзина</h1>
            </div>
            <div class="col-6 text-right">
                <form method="post" class="ms2_form">
                    <button type="submit" name="ms2_action" value="cart/clean" class="btn btn-text">
                        Очистить корзину
                    </button>
                </form>
            </div>
        </div>
        <div class="cart-holder">
            {foreach $products as $product}
                <!-- ROW -->
                <div class="cart_row" id="{$product.key}">
                    <div class="deleter">
                        <form method="post" class="ms2_form">
                            <input type="hidden" name="key" value="{$product.key}">
                            <button class="btn btn--cart" type="submit" name="ms2_action"
                                    value="cart/remove" title="Удалить"><i class="fa fa-trash"
                                                                           aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-6 col-md-2">
                            <div class="image">
                                <a href="{$product.id | url}">
                                    {if $product.image}
                                        <img src="{$product.image}" alt="{$product.pagetitle | htmlent}" title="{$product.pagetitle | htmlent}">
                                    {else}
                                        <img src="{$_modx->config.conf_noimage}" alt="{$product.pagetitle | htmlent}" title="{$product.pagetitle | htmlent}"/>
                                    {/if}
                                </a>
                            </div>
                        </div>
                        <div class="col-6 col-md-5">
                            {if $product.article}
                                <span class="article">{$product.article}</span>
                            {/if}
                            <a href="{$product.id | url}" class="name">{$product.pagetitle | htmlent}</a>
                            {if $product.options?}
                                {foreach $product.options as $key => $option}
                                    {if $key in ['modification','modifications','msal']}{continue}{/if}

                                    {set $caption = $product[$key ~ '.caption']}
                                    {set $caption = $caption ? $caption : ('ms2_product_' ~ $key) | lexicon}

                                    {if $option is array}
                                        {$caption} - {$option | join : '; '} <br>
                                    {else}
                                        {$caption} - {$option} <br>
                                    {/if}

                                {/foreach}
                            {/if}
                        </div>
                        <div class="col-6 col-md-3">
                            <form method="post" class="ms2_form form-inline" role="form">
                                <input type="hidden" name="key" value="{$product.key}">
                                <div class="cb-qnt le-quantity inliner vam">
                                    <button type="button" class="btn btn--count minus">-</button>
                                    <input type="text" name="count" class="counter" value="{$product.count}" data-krat="{$resource.parent | resource : "krat"}" data-min="{$resource.parent | resource : "min"}">
                                    <button type="button" class="btn btn--count plus">+</button>
                                </div>
                                <button class="btn btn--cart" type="submit" name="ms2_action"
                                        value="cart/change"><i class="fa fa-refresh"
                                                               aria-hidden="true"></i></button>
                            </form>
                        </div>
                        <div class="col-6 col-md-2">
                            <span class="price"><span>{$product.price}</span> {'autos.shop.currency' | lexicon}</span>
                        </div>
                    </div>
                </div>
                <!-- / ROW -->
            {/foreach}
        </div>
    </div>
{/if}