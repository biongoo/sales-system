var value = 0;
var basket = [];
basket[1] = [];

$('.add_invoice').on('change', '#client', function() { change_client(this.value) });
$('.transfers').on('click', 'tbody > tr', function() { add_transfer($(this)) });
$('.navbar').on('click', '#delete', function() { delete_tran() });
$('.navbar').on('click', '#settle', function() { settle_tran() });
$('#accordionp').on('click', '.checkout-step', function() { show_history($(this)) });
$('#accordionp').on('click', '.delete_whole', function() { delete_whole($(this).parent().children().last().val()) });
$('#accordionp').on('click', '.back', function() { back($(this).parent().children().last().val()) });
$('form').on('keydown', 'input[type=number]', function(e) { let k = e.which; if(k == 38 || k == 40) { e.preventDefault(); navigation(k, $(this)); } });


$('#value_of_all').text(value_of_all);

jQuery('.accordion-toggle').click(function(){
    var has = jQuery(this);
    if(has.hasClass('collapsed')){
        jQuery(this).find('#sec_i').css("transform","rotate(90deg)");
        jQuery(this).find('#sec_i').css("transition","all linear 0.25s");
    }
    else
        jQuery(this).find('#sec_i').css("transform","rotate(0deg)");
});



$(document).on("wheel", "input[type=number]", function (e) {
    $(this).blur();
});



function add_transfer(trans){
    let client = trans.closest('.collapse').prev().children().children().children().first().text();
    let symbol = trans.closest('.collapse').prev().children().children().children().eq(1).text();

    if(basket[0] == null)
    {
        basket[0] = client;
        basket[2] = symbol;
    }
    else{
        if(symbol != basket[2] || client != basket[0]){
            bootbox.alert({ message: 'Wystąpił błąd! Nie można rozliczyć różnych klientów lub klienta o różnych symbolach!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
            return;
        }
    }

    let id = trans.children().eq(2).text().replace('FS ', '')/1;
    let val = trans.children().eq(3).text()/1;
    let isset = basket[1].includes(id);

    if(!isset){
        value += val;
        basket[1].push(id);
        trans.addClass('table-success');

    }
    else{
        let index = basket[1].indexOf(id);

        if (index > -1){
            value -= val;
            basket[1].splice(index,1);
            trans.removeClass('table-success');

        }
    }

    value = Math.round(value * 100)/100;
    basket[3] = value;
    $('#value-tran').text(value);

    if(basket[1].length)
        $('.navbar').show();
    else{
        $('.navbar').hide();
        basket[0] = null;
        basket[2] = null;
        basket[3] = null;
    }
}

function back(id){
    $("input[name=back]").val(id);
    $('#back_form').submit();
}

function change_client(id_client){
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText)
            {
                let sb = '<div class="form-group" id="symbols">';
                    sb += '<h6>Symbol:</h6>';
                    sb += '<div class="d-flex justify-content-center">';
                        sb += '<div class="btn-group-toggle" data-toggle="buttons">';
                            sb += '<div class="btn-group btn-group-justified" style="display:block;">';
                            let i = 1;
                            for (let symbol of JSON.parse(this.responseText)){
                                sb += '<label class="btn btn-primary mb-1">';
                                    sb += '<input type="radio" name="symbols" id="option'+i+'" value="'+symbol+'" autocomplete="off" required> '+symbol;
                                sb += '</label>';
                                i++;
                            }
                            sb += '</div>';
                        sb += '</div>';
                    sb += '</div>';
                sb += '</div>';

                $("#client_form").after(sb);
            }
            else
                $("#symbols").remove();
        }
    };
    xmlhttp.open("GET","include/actions.php?change_client="+id_client,true);
    xmlhttp.send();
}

function delete_tran(){
    let ids_to_delete = JSON.stringify(basket[1]);

    $('#ids_del').val(ids_to_delete);
    $('#delete_tran').submit();
}

function delete_whole(id){
    $("input[name=delete_whole]").val(id);
    $('#delete_whole_form').submit();
}

function navigation(key, input){
    switch(key) {
        case 38: // up
            if(input.parent().prev().children().eq(1).is("input[type=number]"))
                input.parent().prev().children().eq(1).focus();
            break;
        case 40: // down
            if(input.parent().next().children().eq(1).is("input[type=number]"))
                input.parent().next().children().eq(1).focus();
            else if(input.parent().next().next().is("button"))
                input.parent().next().next().focus();
            break;
    
        default: return;
    }
}

function settle_tran(){
    let ids_to_settle = JSON.stringify(basket);

    $('#ids_sett').val(ids_to_settle);
    $('#settle_tran').submit();
}

function show_history(row){
    if(row.children().eq(1).children().eq(0).children().length == 5)
    {
        let id_tran = row.children().eq(1).children().children().last().val();
        if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                if(this.responseText)
                {
                    let sb = '<table class="table table-striped table-bordered">';
                        sb += '<thead class="thead-dark">';
                            sb += '<tr>';
                                sb += '<th scope="col">#</th>';
                                sb += '<th scope="col">Data</th>';
                                sb += '<th scope="col">Nr FS</th>';
                                sb += '<th scope="col">Wartość</th>';
                            sb += '</tr>';
                        sb += '</thead>';
                        sb += '<tbody>';
                            sb += this.responseText;
                        sb += '</tbody>';
                    sb += '</table>';

                    row.children().eq(1).children().prepend(sb);
                }
                else
                    bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
            }
        };
        xmlhttp.open("GET","include/actions.php?show_history="+id_tran,true);
        xmlhttp.send();
    }
}
