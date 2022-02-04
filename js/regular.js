var basket = [];
var value = 0;
var xd = 1;

window.onbeforeunload = function() { return 'Aby na pewno chcesz opuscić tą stronę?'; };

//SORT
$('table').on('click', '.sort_up', function(e) {e.preventDefault(); sort($(this).closest('tr').children().first().text()/1, 'up') });
$('table').on('click', '.sort_down', function(e) {e.preventDefault(); sort($(this).closest('tr').children().first().text()/1, 'down') });

//LISTA
$('table').on('change', '.custom-select', function() { change_product($(this).closest('td').prev().text()) });

//ZMIANA DANYCH
$('table').on('keyup', '.count', function(e) { keyup_count($(this).attr('id'), e.which) });
$('table').on('keyup', '.price', function(e) { keyup_price($(this).parent().parent().prev().children().children().attr('id'), $(this).val(), e.which) });

//PRZYCISKI
$('#print').click( function(e) {e.preventDefault(); print() } ); // DRUKUJ
$('#finish').click( function(e) {e.preventDefault(); finish() } ); // ZAKOŃCZ
$('#add_row').click( function(e) {e.preventDefault(); add_row(); } ); // DODAJ
$('table').on('click', '.delete_row', function(e) {e.preventDefault(); delete_row($(this).closest('tr').children().first().text()/1) });
$('table').on('click', '.last_price', function(e) {e.preventDefault(); insert_last_price($(this).closest('tr').children().eq(2).children().children().attr('id')) });

//KLAWISZE
$('table').on('keydown', 'input', function(e) { let k = e.which; if(k == 37 || k == 38 || k == 39 || k == 40 || k == 13) { e.preventDefault(); navigation(k, $(this)); } });

function add_row() {
    if(xd){
        xd = 0;
        var id = $('tbody').children().last().children().first().text()/1 + 1;

        if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                let new_id_product = this.responseText;

                let newTr = '<tr id="row_'+id+'">';
                    newTr += '<th scope="col">'+id+'</th>';
                    newTr += '<td>';
                        newTr += '<div class="input-group">';
                            newTr += '<select class="custom-select">';
                                newTr += $('#default_select').html();
                            newTr += '</select>';
                        newTr += '</div>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<div class="input-group"><input type="text" id="prod_'+new_id_product+'" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]{1,2}" class="form-control count" disabled></div>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<div class="input-group"><input type="text" id="price_'+new_id_product+'" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]{1,2}" class="form-control price" disabled></div>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<span>0</span>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<button type="button" class="btn btn-info last_price">0</button>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<span class="table-up"><a href="" class="indigo-text sort_up"><i class="fas fa-long-arrow-alt-up" aria-hidden="true"></i></a></span>';
                        newTr += '<span class="table-down"><a href="" class="indigo-text sort_down"><i class="fas fa-long-arrow-alt-down" aria-hidden="true"></i></a></span>';
                    newTr += '</td>';
                    newTr += '<td>';
                        newTr += '<button type="button" class="btn btn-danger btn-rounded btn-sm my-0 delete_row">Usuń</button>';
                    newTr += '</td>';
                newTr += '</tr>';

                if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() 
                {
                    if (this.readyState == 4 && this.status == 200) 
                    {
                        if(this.responseText == 1)
                        {
                            $('tbody').append(newTr);
                            xd = 1;
                        }
                        else
                            bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
                    }
                };
                xmlhttp.open("GET","include/actions.php?add_row_regular="+client+"&new_id_product="+new_id_product,true);
                xmlhttp.send();
            }
        }
        xmlhttp.open("GET","include/actions.php?last_prod_id="+client,true);
        xmlhttp.send();
    }
}


function change_product(id){
    let new_prod_id = $('#row_'+id).children().eq(1).children().children().val();
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText == 1)
            {
                let count_input = $('#row_'+id).children().eq(2).children().children();
                if(new_prod_id){
                    count_input.prop("disabled", false);
                    let prod_array_id = count_input.attr('id').slice(5);

                    for(let product of basket){
                        if(product[6] == prod_array_id){
                            product[0] = $('#row_'+id).children().eq(1).children().children().find('option:selected').text();
                            break;
                        }
                    }
            
                }
                else{
                    count_input.prop("disabled", true);
                    count_input.val('');
                    keyup_count(count_input.attr('id'));
                }
            }
            else
                bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
        }
    };
    xmlhttp.open("GET","include/actions.php?change_product_regular="+id+"&regular_client="+client+"&new_prod_id="+new_prod_id,true);
    xmlhttp.send();
}

function change_value(id){
    let prod_value = $('#price_'+id).parent().parent().next().children();
    let old_value = prod_value.text().replace(",", ".")/1;
    value -= old_value;

    let price_val = 0;
    let count_val = 0;

    if($('#prod_'+id)[0].checkValidity())
        count_val = $('#prod_'+id).val().replace(",", ".")/1;

    if($('#price_'+id)[0].checkValidity())
        price_val = $('#price_'+id).val().replace(",", ".")/1;

    let new_value = Math.round(count_val * price_val * 100)/100;
    value += new_value;
    value = Math.round(value * 100) / 100;

    prod_value.text(new_value.toString().replace(".", ","));
    $('#value').text(value.toString().replace(".", ","));
}

function delete_row(id){
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText == 1)
            {
                let count_input = $('#row_'+id).children().eq(2).children().children();
                count_input.val('');
                keyup_count(count_input.attr('id'));

                $("#row_"+id).remove();
                id++;

                while($('tbody').children().is('#row_'+id)){
                    let new_id = id-1;
                    $('#row_'+id).children().first().text(new_id);
                    $('#row_'+id).attr("id","row_"+new_id);
                    id++;
                }
            }
            else
                bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
        }
    };
    xmlhttp.open("GET","include/actions.php?delete_row_regular="+id+"&delete_row_client="+client,true);
    xmlhttp.send();
}

function finish(){
    if(xd){
        xd = 0;
        let step = 1;
        if(basket.length < 1){
            bootbox.alert({ message: 'W koszyku nic nie ma!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
            xd = 1;
            return;
        }

        for(let product of basket){
            let id = product[6];
            let price_input = $('#price_'+id);
            let prod_input = $('#prod_'+id);
            let price_value = price_input.val().replace(',', '.')/1;

            if(!price_value){
                price_input[0].setCustomValidity("Pole nie moze być puste!");
                step = 0;
            }

            if(!price_input[0].checkValidity() || !prod_input[0].checkValidity())
                step = 0;

            if(product[2] <= 0 || product[3] <= 0)
                step = 0;
        }
        
        if(!step){
            bootbox.alert({ message: 'Uzupełnij wszystkie pola poprawnie!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
            xd = 1;
            return;
        }
        
        if(window.XMLHttpRequest) xmlhttp = new XMLHttpRequest()
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                if(this.responseText)
                {
                    bootbox.confirm({
                        message: 'Zamówienie zostanie wydrukowane! Co chcesz zrobić?',
                        buttons: {
                            confirm: {
                                label: 'Zapisz',
                                className: 'btn-outline-success'
                            },
                            cancel: {
                                label: 'Edytuj',
                                className: 'btn-outline-warning'
                            }
                        },
                        callback: function (result) {
                            if(result){
                                open(location, '_self').close();
                                return false;  
                            }
                        }
                    });
                    xd = 1;
                }
                else
                    bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
            }
        };
        xmlhttp.open("GET","include/actions.php?finish_regular="+JSON.stringify(basket)+"&value="+value,true);
        xmlhttp.send();
    }
}

function insert_last_price(id){
    let prod_array_id = id.slice(5);
    let price_input = $('#price_'+prod_array_id);
    let button_value = $('#prod_'+prod_array_id).parent().parent().parent().children().eq(5).text();

    if(price_input.is(":not(:disabled)")){
        price_input.val(button_value);
        keyup_price(id,button_value)
    }
}

function keyup_count(id, key){
    if(key == 37 || key == 38 || key == 39 || key == 40 || key == 13)
        return;

    let prod_array_id = id.slice(5)/1;
    let price_input = $('#price_'+prod_array_id);
    let count_val = $('#prod_'+prod_array_id).val().replace(',', '.')/1;

    //Włącznie inputa ceny
    if(!$('#prod_'+prod_array_id)[0].checkValidity()){
        price_input.val('');
        price_input.prop("disabled", true);
        count_val = 0;
    }
    else{
        price_input.prop("disabled", false);
        price_input[0].setCustomValidity("");
    }

    if(count_val > 0){
        let step = 'New';
        let step_index = -1;

        let new_product = [];
        let prod = $('#prod_'+prod_array_id).parent().parent().prev().children().children();
        let prod_name = prod.find('option:selected').text();
        let prod_id = prod.val()/1;
        let prod_quantity = count_val;
        let prod_price = price_input.val()/1;
        let last_price = $('#prod_'+prod_array_id).parent().parent().parent().children().eq(5).text()/1;

        for(let product of basket){
            if(product[6] == prod_array_id){
                step = 'Update';
                step_index = basket.indexOf(product);
                break;
            }
        }

        if(step == 'New'){
            new_product.push(prod_name, prod_id, prod_quantity, prod_price, 1, 1, prod_array_id, last_price);
            basket.push(new_product);
        }
        else if(step == 'Update'){
            new_product.push(prod_name, prod_id, prod_quantity, prod_price, 1, 1, prod_array_id, last_price);
            if(step_index > -1)
                basket[step_index] = new_product;
        }
    }
    else{
        let delete_index = -1;
        for(product of basket){
            if(product[6] == prod_array_id){
                delete_index = basket.indexOf(product);
                break;
            }
        }
        if(delete_index > -1)
            basket.splice(delete_index, 1);

        price_input.val('');
        price_input.prop("disabled", true);
    }

    change_value(prod_array_id);
}

function keyup_price(id, price_val, key){
    if(key == 37 || key == 38 || key == 39 || key == 40 || key == 13) 
        return;

    let prod_array_id = id.slice(5)/1;
    let count_val = $('#prod_'+prod_array_id).val().replace(',', '.')/1;

    price_val = price_val.replace(',', '.')/1;

    if(price_val){
        $('#price_'+prod_array_id)[0].setCustomValidity("");
    }

    if(!$('#price_'+prod_array_id)[0].checkValidity()){
        price_val = 0;
    }

    if(count_val > 0 && count_val >= 0){
        let product_index = -1;
        for(let product of basket){
            if(product[6] == prod_array_id){
                product_index = basket.indexOf(product);
                break;
            }
        }

        if(product_index > -1)
            basket[product_index][3] = price_val;
        else
            bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
    }

    change_value(prod_array_id);
}

function navigation(key, input){
    let index = input.parent().parent().index();
    let index_tr = input.parent().parent().parent().index();
    let last_index = $('tbody').children().last().children().first().text()/1 - 1;
    switch(key) {
        case 37: // left
            if(!input.parent().parent().prev().children().children().is("input"))
                return;
            input.parent().parent().prev().children().children().focus();
            break;

        case 38: // up
            for(let i = index_tr; i>=1; i--){
                if(!input.parent().parent().parent().parent().children().eq(i-1).children().eq(index).children().children().is("input:not(:disabled)"))
                    continue;
                else{
                    input.parent().parent().parent().parent().children().eq(i-1).children().eq(index).children().children().focus();
                    break;
                }      
            }
            break;

        case 39: // right
            if(!input.parent().parent().next().children().children().is("input"))
                return;
            input.parent().parent().next().children().children().focus();
            break;

        case 40: // down
            for(let i = index_tr; i<=last_index; i++){
                if(!input.parent().parent().parent().parent().children().eq(i+1).children().eq(index).children().children().is("input:not(:disabled)"))
                    continue;
                else{
                    input.parent().parent().parent().parent().children().eq(i+1).children().eq(index).children().children().focus();
                    break;
                }      
            }
            break;
    
        default: return;
    }
}

function print(){
    console.log(JSON.stringify(basket));
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest()
    xmlhttp.open("GET","include/actions.php?print_regular="+JSON.stringify(basket),true);
    xmlhttp.send();
}

function sort(id, set) {
    switch(set) {
        case 'up':
            if(id == 1)
            {
                bootbox.alert({ message: 'Nie można przenieść wyżej!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
                return;
            }
            var new_id = id - 1;
            break;
        case 'down':
            if(id == $('tbody').children().last().children().first().text())
            {
                bootbox.alert({ message: 'Nie można przenieść niżej!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
                return;
            }
            var new_id = id + 1;
            break;
    }

    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText == 1)
            {
                $("#row_"+id).attr("id", "temp1");
                $("#row_"+new_id).attr("id", "temp2");

                $('#temp1').children().first().text(new_id);
                $('#temp2').children().first().text(id);
                switch(set) {
                    case 'up':
                        $('#temp2').insertAfter('#temp1');
                        break;
                    case 'down':
                        $('#temp2').insertBefore('#temp1');
                        break;
                }
                $('#temp1').attr("id", "row_"+new_id);
                $('#temp2').attr("id", "row_"+id);
            }
            else
                bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
        }
    };
        xmlhttp.open("GET","include/actions.php?sort_regular="+id+"&sort_regular_set="+set+"&sort_regular_client="+client,true);
        xmlhttp.send();
}
