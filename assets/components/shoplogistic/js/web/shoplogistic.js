var sl_delivery = {
    options: {
        wrapper: '.sl_order',
        deliveries: '.sl_deliveries'
    },
    initialize: function(){
        // handlers event
    }
}

$(document).ready(function(){
    sl_delivery.initialize();
    // QUANTITY
    $('.sl-quantity button.btn-count').click(function(e){
        e.preventDefault();
        var elem = $(this).closest('.sl-quantity').find('input.counter');
        var krat = $(this).closest('.sl-quantity').find('input.counter').data('krat');
        var min = $(this).closest('.sl-quantity').find('input.counter').data('min');
        var currentQty= elem.val();

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