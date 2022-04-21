var sl_delivery = {
    options: {
        wrapper: '.sl_order',
        del_wrap: '.sl_del',
        deliveries: '.sl_deliveries',
        address_field: '.sl_address',
        hidden_address: ".sl_address_block",
        services: ".sl-services",
        service: ".sl-services input",
        map: '.service-map',
        pvz: '.sl_pvz',
        choosed_pvz: '.choosed_data'
    },
    initialize: function(){
        // handlers event
        $(this.options.address_field).suggestions({
            token: shoplogisticConfig['dadata_api_key'],
            type: "ADDRESS",
            /* Вызывается, когда пользователь выбирает одну из подсказок */
            onSelect: sl_delivery.setDeliveryFields
        });
        this.viewAddress();
        $('input[type=radio][name=delivery]').change(function() {
            sl_delivery.viewAddress();
            $(sl_delivery.options.pvz).text('');
            $(sl_delivery.options.choosed_pvz).removeClass('active');
            var d = $('input[type=radio][name=delivery]:checked').val();
            if(d == shoplogisticConfig['punkt_delivery'] || d == shoplogisticConfig['curier_delivery']){
                var fias = $(sl_delivery.options.hidden_address).find("input[name=fias]").val();
                if (fias){
                    sl_delivery.getDeliveryPrices(fias);
                }
            }
        });
        // radio fix in miniShop2 default.js (order.add)
        $('input[type=radio][name=sl_service]').change(function() {
            var main_data = $('input[type=radio][name=sl_service]:checked').data('data');
            sl_delivery.setData(JSON.parse(main_data));
            var d = $('input[type=radio][name=delivery]:checked').val();
            $(sl_delivery.options.pvz).text('');
            $(sl_delivery.options.choosed_pvz).removeClass('active');
        });
        $(document).on("click", ".sl_check", function(e){
            e.preventDefault();
            var save_data = {};
            var data = $(this).data("info");
            var d = $('input[type=radio][name=sl_service]:checked').val();
            var main_data = $('input[type=radio][name=sl_service]:checked').data('data');
            if (typeof data === 'object'){
                save_data.pvz = data;
            }else{
                save_data.pvz = JSON.parse(data);
            }
            if (typeof main_data === 'object'){
                save_data.service = main_data;
            }else{
                save_data.service = JSON.parse(main_data);
            }
            var send_data = JSON.stringify(save_data);
            $(sl_delivery.options.wrapper).find('.delivery_data').val(JSON.stringify(send_data));
            var data = {
                sl_action: 'delivery/add_order',
                data: send_data
            }
            sl_delivery.send(data);
            sl_delivery.map.balloon.close();
            var d = $('input[type=radio][name=delivery]:checked').val();
            $(sl_delivery.options.pvz).text(save_data.pvz.code + ' || ' +  save_data.pvz.address);
            $(sl_delivery.options.choosed_pvz).addClass('active');
        });

        miniShop2.Callbacks.add('Cart.change.response.success', 'ShopLogisticCartChange', function (response) {
            sl_delivery.update_price();
        })
        miniShop2.Callbacks.add('Cart.remove.response.success', 'ShopLogisticCartRemove', function (response) {
            sl_delivery.update_price();
        })

        // map
        /*$("body").on("keyup", this.options.address_field, function (e) {
            var val = $(this).val();
            if(val.length > 2){
                var data = {
                    sl_action: "get/suggestion",
                    value: val,
                    ctx: shoplogisticConfig['ctx']
                }
                sl_delivery.send(data);
            }
        });*/
    },
    setDeliveryFields: function(suggestion){
        var address = suggestion.data;
        $(sl_delivery.options.hidden_address).show();
        if(address.fias_id){
            $(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.fias_id);
        }
        if(address.city_fias_id){
            $(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.city_fias_id);
        }
        //$(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.city_fias_id);
        if(address.area_kladr_id){
            $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.area_kladr_id);
        }
        if(address.city_kladr_id){
            $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.city_kladr_id);
        }
        $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.city_kladr_id);
        $(sl_delivery.options.hidden_address).find("input[name=geo]").val(sl_delivery.join([
            address.geo_lat, ",", address.geo_lon], ""));
        $(sl_delivery.options.hidden_address).find("input[name=index]").val(address.postal_code);
        $(sl_delivery.options.hidden_address).find("input[name=region]").val(sl_delivery.join([
            sl_delivery.join([address.region_type, address.region], " "),
            sl_delivery.join([address.area_type, address.area], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=city]").val(sl_delivery.join([
            sl_delivery.join([address.city_type, address.city], " "),
            sl_delivery.join([address.settlement_type, address.settlement], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=street]").val(sl_delivery.join([address.street_type, address.street], " "));
        $(sl_delivery.options.hidden_address).find("input[name=building]").val(sl_delivery.join([
            sl_delivery.join([address.house_type, address.house], " "),
            sl_delivery.join([address.block_type, address.block], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=room]").val(sl_delivery.join([address.flat_type, address.flat], " "));
        var fias = $(sl_delivery.options.hidden_address).find("input[name=fias]").val();
        if(fias){
            sl_delivery.getDeliveryPrices(fias);
        }
    },
    update_price: function(){
        var fias = $(sl_delivery.options.hidden_address).find("input[name=fias]").val();
        if(fias){
            sl_delivery.getDeliveryPrices(fias);
        }
    },
    viewAddress: function(){
        var d = $('input[type=radio][name=delivery]:checked').val();
        if (d == shoplogisticConfig['default_delivery']) {
            $(sl_delivery.options.deliveries).hide();
            $(sl_delivery.options.del_wrap).hide();
            $(sl_delivery.options.map).removeClass("active");
            $(sl_delivery.options.services).removeClass('active');
        }
        if(d == shoplogisticConfig['punkt_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.map).removeClass("active");
            //$(sl_delivery.options.services).show();
        }
        if(d == shoplogisticConfig['curier_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.map).removeClass("active");
            //$(sl_delivery.options.services).show();
        }
        if(d == shoplogisticConfig['post_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.map).removeClass("active");
            $(sl_delivery.options.services).removeClass('active');
        }
    },
    getDeliveryPrices: function(fias){
        if(fias){
            $(sl_delivery.options.services).addClass("active");
            $(sl_delivery.options.service).each(function(){
                var service = $(this).val();
                if(service){
                    sl_delivery.getDeliveryPrice(fias, service);
                }
            })
        }
    },
    getDeliveryPrice: async function(fias, service){
        var data = {
            sl_action: 'delivery/get_price',
            fias: fias,
            service: service
        }
        this.send(data);
    },
    setData: function(data){
        $(sl_delivery.options.services).find('.service_info_'+data.main_key).hide();
        var d = $('input[type=radio][name=delivery]:checked').val();
        if(d == shoplogisticConfig['punkt_delivery']){
            var prop = 'terminal';
        }else{
            var prop = 'door';
        }
        data.method = prop;
        if(data[data.main_key].price){
            if(data[data.main_key].price.hasOwnProperty(prop) && data[data.main_key].price[prop].hasOwnProperty('price')){
                var price = data[data.main_key].price[prop].price;
                var srok = data[data.main_key].price[prop].time;
                $('.'+data.main_key+'_price').text(price);
                $('.'+data.main_key+'_srok').text(srok);
                $('.'+data.main_key+'_price').closest('.service_info_'+data.main_key).show();
                $('input#service_'+data.main_key).removeAttr("disabled");
            }else{
                $('input#service_'+data.main_key).attr("disabled", "disabled");
                $('input#service_'+data.main_key).removeAttr("checked");
                $('input#service_'+data.main_key).prop('checked', false);
            }
        }else{
            $('input#service_'+data.main_key).attr("disabled", "disabled");
            $('input#service_'+data.main_key).removeAttr("checked");
            $('input#service_'+data.main_key).prop('checked', false);
        }
        $("input[name=sl_service][value="+data.main_key+"]").data("data", JSON.stringify(data));
        if($("input[name=sl_service][value="+data.main_key+"]").prop("checked")){
            // set delivery price
            var save_data = {};
            var main_data = data;
            if (typeof main_data === 'object'){
                save_data.service = main_data;
            }else{
                save_data.service = JSON.parse(main_data);
            }
            var send_data = JSON.stringify(save_data);
            $(sl_delivery.options.wrapper).find('.delivery_data').val(JSON.stringify(send_data));
            var data = {
                sl_action: 'delivery/add_order',
                data: send_data
            }
            setTimeout(function() {
                sl_delivery.send(data);
            }, 100);

            if(prop == 'terminal' && save_data.service[save_data.service.main_key].price.hasOwnProperty(prop) && save_data.service.main_key != "postrf"){
                $(sl_delivery.options.map).addClass("active");
                sl_delivery.setMap(save_data.service[save_data.service.main_key].price.terminals);
            }else{
                if(this.map){
                    this.map.destroy();
                }
                $(sl_delivery.options.map).removeClass("active");
            }
        }
    },
    initMap: function(center, terminals){
        if(this.map){
            this.map.destroy();
        }
        this.map = new ymaps.Map('service-map', {
            center: center,
            zoom: 9
        }, {
            searchControlProvider: 'yandex#search'
        });
        terminals.forEach((element, index, array) => {
            var coords = [element['lat'], element['lon']];
            var data = JSON.stringify(element);
            var text = '<div class="sl_baloon_header"><img src="'+element['image']+'" width="10"/>'+element['address']+'</div>';
            if(element['phones']){
                text = text+'<div class="sl_baloon_phones sl_baloon_block"><b>Телефоны:</b><br/>'+element['phones']+'</div>';
            }
            if(element['workTime']){
                text = text+'<div class="sl_baloon_works sl_baloon_block"><b>Время работы:</b><br/>'+element['workTime']+'</div>';
            }
            text = text+'<div class="sl_baloon_submit sl_baloon_block"><button type="button" class="sl_check" data-info=\''+data+'\'>Забрать отсюда</button></div>';
            var myPlacemark = new ymaps.Placemark(coords, {
                hintContent: element['address'],
                balloonContent: text
            }, {
                iconLayout: 'default#image',
                iconImageHref: element['image'],
                iconImageSize: [20, 20],
                iconImageOffset: [-10, -10]
            });
            this.map.geoObjects.add(myPlacemark);
        });
    },
    setMap: function(terminals){
        var geo = $(sl_delivery.options.deliveries).find("input[name=geo]").val();
        if(geo){
            var g = geo.split(',');
            this.initMap(g, terminals);
            $(sl_delivery.options.map).addClass('active');
        }
    },
    send: function(data){
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                console.log(data_r);
                if(data_r.main_key){
                    sl_delivery.setData(data_r);
                }else{
                    if(data_r.hasOwnProperty('data')){
                        if(data_r.data.hasOwnProperty('re_calc')){
                            if(data_r.data.re_calc){
                                miniShop2.Order.getcost();
                            }
                        }
                    }
                }
            }
        });
    },
    join: function (arr /*, separator */) {
        var separator = arguments.length > 1 ? arguments[1] : ", ";
        return arr.filter(function(n){return n}).join(separator);
    }
}

var sl_marketplace = {
    options: {
        wrapper: '.sl_wrap',
        live_form: '.sl_live_form',
        generate_api: '.regerate_apikey',
        profile_product: '.profile-products__item-wrap',
    },
    initialize: function () {
        $(document).on("click", sl_marketplace.options.generate_api, function(e) {
            e.preventDefault();
            var type = $(this).data('type');
            var id = $(this).data('id');
            var action = 'apikey/generate';
            var str = shoplogisticConfig['regexp_gen_code'];
            var gen = sl_marketplace.genRegExpString(str);
            var data = {
                sl_action: action,
                type: type,
                id: id,
                apikey: gen
            };
            sl_marketplace.send(data);
        });
        $(document).on('submit', sl_marketplace.options.live_form, function(e){
            e.preventDefault();
            var data = $(this).serialize();
            sl_marketplace.send(data);
        });
        $(sl_marketplace.options.live_form).on('keyup input', 'input[type=text]', function(e){
            if($(this).val().length >= 3 || $(this).val().length == 0){
                const url = new URL(document.location);
                const searchParams = url.searchParams;
                searchParams.delete("page");
                window.history.pushState({}, '', url.toString());
                pdoPage.keys['page'] = 1;
                $(this).closest(sl_marketplace.options.live_form).trigger('submit');
            }
        });
        $(sl_marketplace.options.live_form).on('change', 'input[type=checkbox]', function(e){
            const url = new URL(document.location);
            const searchParams = url.searchParams;
            searchParams.delete("page");
            window.history.pushState({}, '', url.toString());
            pdoPage.keys['page'] = 1;
            $(this).closest(sl_marketplace.options.live_form).trigger('submit');
        });
        $(document).on("click", sl_marketplace.options.profile_product, function(e) {
            e.preventDefault();
            var product_id = $(this).closest('.profile-products__item').data('id');
            var product_name = $(this).closest('.profile-products__item').data('name');
            var type = $(this).closest('.profile-products__item').data('type');
            var col_id = $(this).closest('.profile-products__item').data('col_id');
            var remains = $(this).closest('.profile-products__item').data('remains');
            var price = $(this).closest('.profile-products__item').data('price');
            var description = $(this).closest('.profile-products__item').data('description');
            $('#remain input[name="product_id"]').val(product_id);
            $('#remain input[name="product_name"]').val(product_name);
            $('#remain input[name="type"]').val(type);
            $('#remain input[name="col_id"]').val(col_id);
            $('#remain input[name="remains"]').val(remains);
            $('#remain input[name="price"]').val(price);
            $('#remain textarea[name="description"]').val(description);
            var remainModal = new bootstrap.Modal(document.getElementById('remain'));
            remainModal.show();
        });
    },
    genRegExpString: function (str) {
        var str_new = str;

        var words = {};
        words['0-9'] = '0123456789';
        words['a-z'] = 'qwertyuiopasdfghjklzxcvbnm';
        words['A-Z'] = 'QWERTYUIOPASDFGHJKLZXCVBNM';

        var match = /\/((\(?\[[^\]]+\](\{[0-9-]+\})*?\)?[^\[\(]*?)+)\//.exec(str);
        if (match != null) {
            str_new = match[1].replace(/\(?(\[[^\]]+\])(\{[0-9-]+\})\)?/g, regexpReplace1);
            str_new = str.replace(match[0], str_new);
        }

        return str_new;

        function rand(min, max) {
            if (max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            else {
                return Math.floor(Math.random() * (min + 1));
            }
        }

        function regexpReplace1(match, symbs, count) {
            symbs = (symbs + count).replace(/\[([0-9a-zA-Z-]+)\]\{([0-9]+-[0-9]+|[0-9]+)\}/g, regexpReplace2);

            return symbs;
        }

        function regexpReplace2(match, symbs, count) {
            var r = match;
            var arr_symbs = symbs.match(/[0-9a-zA-Z]-[0-9a-zA-Z]/g);

            if (arr_symbs.length > 0) {
                var maxcount = 1;

                if (typeof count != 'undefined') {
                    nums = count.split('-');

                    if (typeof nums[1] == 'undefined') {
                        maxcount = +nums[0];
                    }
                    else {
                        min = +nums[0];
                        max = +nums[1];

                        maxcount = rand(min, max);
                        maxcount = maxcount < min ? min : maxcount;
                    }
                }

                for (var i = 0; i < arr_symbs.length; i++) {
                    symbs = symbs.replace(arr_symbs[i], words[arr_symbs[i]]);
                }

                var maxpos = symbs.length - 1,
                    pos,
                    r = '';

                for (var i = 0; i < maxcount; i++) {
                    pos = Math.floor(Math.random() * maxpos);
                    r += symbs[pos];
                }
            }

            return r;
        }
    },
    send: function(data){
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                if(data_r.data.apikey){
                    $("#apikey_"+data_r.data.type+"_"+data_r.data.id).val(data_r.data.apikey);
                }
                if(data_r.topdo){
                    $('#pdopage .rows').html(data_r.data);
                    //$('#pdopage .pagination').addClass('manual_pagi');
                    $('#pdopage .pagination').html(data_r.pagination);
                    $('#pdopage span.total').html(data_r.total);
                }
            }
        });
    },
}

$(document).ready(function(){
    if($(sl_delivery.options.wrapper).length){
        sl_delivery.initialize();
    }
    sl_marketplace.initialize();
    // QUANTITY
    $('.sl-quantity button.btn-count').click(function(e){
        e.preventDefault();
        var elem = $(this).closest('.sl-quantity').find('input.counter');
        var krat = $(this).closest('.sl-quantity').find('input.counter').data('krat');
        var min = $(this).closest('.sl-quantity').find('input.counter').data('min');
        var currentQty = elem.val();

        if( $(this).hasClass('minus') && currentQty>min){
            elem.val(parseInt(currentQty, 10) - krat);
            elem.trigger("change");
        }else{
            if( $(this).hasClass('plus')){
                elem.val(parseInt(currentQty, 10) + krat);
                elem.trigger("change");
            }
        }
    });
    // ms2 pseudo submit
    $(".pseudo_submit").click(function(e) {
        e.preventDefault();
        $(this).attr("disabled");
        $("#msOrder .ms2_link").trigger("click");
    });
})