{if $services}
    <div class="sl-services">
        <div class="checkers form-group sl-row">
            {foreach $services as $key => $service index=$index}
                <div class="d-col">
                    <div class="checkbox">
                        <label class="col-form-label sl-service">
                            <input type="radio" name="sl_service" value="{$key}" id="service_{$key}" {if $index == 0}checked=""{/if}>
                            <div class="visual_block">
                                <div class="visual_block_inner">
                                    <div class="visual_block_inner-image">
                                        <img src="{$service['logo']}" alt="{$service['name']}">
                                    </div>
                                    <div class="visual_block_inner-text service_info_{$key}">
                                        <div class="price"><b class="{$key}_price"></b> руб.</div>
                                        <div class="srok"><b class="{$key}_srok"></b></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="service-map">
            <div class="sl-alert sl-alert-info">{('shoplogistic_choose_pvz') | lexicon}</div>
            <div id="service-map"></div>
            <div class="choosed_data">
                <span class="sl_pvz"></span>
            </div>
        </div>
    </div>
{/if}