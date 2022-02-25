<section class="sl-def-section content withovh">
    <div class="sl-base_wrapper">
        <div class="cart_wrap">
            <div class="sl-row">
                <div class="d-col-lg-8">
                    {$_modx->runSnippet("sl.ms_getfields", [])}
                    {$_modx->runSnippet("!msCart", [
                        "tpl" => "tpl.shoplogistic.ms2_cart"
                    ])}
                    <div class="order_form">
                        <form class="form-horizontal ms2_form" id="msOrder" method="post">
                            <div class="form_block_content">
                                <div class="form_block_title inliner vam">
                                    <span class="number">1</span>
                                    <span class="title">Доставка и оплата</span>
                                </div>
                                <div class="form_block_content">
                                    <div id="deliveries" class="mb-2">
                                        <div class="checkers sl-row">
                                            {foreach $deliveries as $delivery index=$index}
                                                {var $checked = !$order.delivery && $index == 0 || $delivery.id == $order.delivery}
                                                <div class="d-col">
                                                    <div class="checkbox">
                                                        <label class="col-form-label delivery input-parent">
                                                            <input type="radio" name="delivery" value="{$delivery.id}" id="delivery_{$delivery.id}" data-payments="{$delivery.payments | json_encode}" {$checked ? 'checked' : ''}>
                                                            <div class="visual_block">
                                                                <div class="visual_block_inner">
                                                                    <span class="label">{$delivery.name}</span>
                                                                    {if $delivery.description?}
                                                                        <p class="small">
                                                                            {$delivery.description}
                                                                        </p>
                                                                    {/if}
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    <div id="payments"  class="mb-2">
                                        <div class="checkers sl-row">
                                            {foreach $payments as $payment index=$index}
                                                {var $checked = !$order.payment && $index == 0 || $payment.id == $order.payment}
                                                <div class="d-col">
                                                    <div class="checkbox">
                                                        <label class="col-form-label payment input-parent">
                                                            <input type="radio" name="payment" value="{$payment.id}" id="payment_{$payment.id}" {$checked ? 'checked' : ''}>
                                                            <div class="visual_block">
                                                                <div class="visual_block_inner">
                                                                    <span class="label">{$payment.name}</span>
                                                                    {if $payment.description?}
                                                                        <p class="small">
                                                                            {$payment.description}
                                                                        </p>
                                                                    {/if}
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form_block_content">
                                <div class="form_block_title inliner vam">
                                    <span class="number">2</span>
                                    <span class="title">Адрес доставки и получатель</span>
                                </div>
                                <div class="form_block_content">
                                    <div class="sub_block">
                                        <span class="subblock_title">Получатель</span>
                                        <div class="sl-row">
                                            {foreach ['receiver','email','phone'] as $key => $field}
                                                <div class="d-col-12 {if $field != 'receiver'}d-col-md-6{/if}">
                                                    <div class="form_input_group input-parent">
                                                        <input type="text" id="{$field}" placeholder="{('ms2_frontend_' ~ $field) | lexicon}"
                                                               name="{$field}" value='{$form[$field]?:$_modx->getPlaceholder("address."~$field)}'
                                                               class="base_input{($field in list $errors) ? ' error' : ''}">
                                                        <label for="{$field}" class="s-complex-input__label">{('ms2_frontend_' ~ $field) | lexicon}</label>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    <div class="sub_block">
                                        <span class="subblock_title">Адрес доставки</span>
                                        <div class="sl-row">
                                            {foreach [
                                            'index' => 'd-col-12 d-col-md-4',
                                            'region' => 'd-col-12 d-col-md-8',
                                            'city' => 'd-col-12',
                                            'street' => 'd-col-12 d-col-md-6',
                                            'building' => 'd-col-6 d-col-md-3',
                                            'room' => 'd-col-6 d-col-md-3'] as $field => $val}
                                                <div class="{$val}">
                                                    <div class="form_input_group input-parent">
                                                        <input type="text" id="{$field}" placeholder="{('ms2_frontend_' ~ $field) | lexicon}"
                                                               name="{$field}" value='{$form[$field]?:$_modx->getPlaceholder("address."~$field)}'
                                                               class="base_input{($field in list $errors) ? ' error' : ''}">
                                                        <label for="{$field}" class="s-complex-input__label">{('ms2_frontend_' ~ $field) | lexicon}</label>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    <div class="sub_block">
                                        <span class="subblock_title">Комментарий</span>
                                        <div class="form_input_group input-parent">
                                            <textarea name="comment" id="comment" class="base_input{('comment' in list $errors) ? ' error' : ''}" placeholder="Комментарий">{$form.comment}</textarea>
                                            <label for="comment" class="s-complex-input__label">Комментарий</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="ms2_action" value="order/submit" class="btn btn-primary ms2_link hidden">
                                {'ms2_frontend_order_submit' | lexicon}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="d-col-lg-4">
                    <div class="summary_block">
                        <div class="summary_title_block">
                            <span class="summary_title">Итого</span>
                        </div>
                        <div class="summary_promocode">
                            {'!mspcForm' | snippet:[
                                'tpl' => 'tpl.shoplogistic.mspc_promocode'
                            ]}
                        </div>
                        <ul class="summary-summation">
                            <li>
                                <div class="summary-summation__term ">Товары</div>
                                <div class="summary-summation__data "><span class="ms2_total_cost">{$order.cost ?: 0}</span>&thinsp;₽</div>
                            </li>
                            <li>
                                <div class="summary-summation__total"><span class="ms2_total_cost">{$order.cost ?: 0}</span>&thinsp;₽</div>
                            </li>
                        </ul>
                        <div class="text-center">
                            <button class="sl-btn sl-btn-primary pseudo_submit">
                                <span class="tm">Оформить заказ</span>
                            </button>
                            <div class="">
                                <button type="button" data-toggle="modal" data-target="#callback" class="sl-btn sl-btn-text texter"> <i class="fa fa-phone"></i> Оформить по телефону</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>