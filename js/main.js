var basket = [];
var value = 0;
var del = 0;
var edit = 0;
var exit = 0;
var client = null;
var prod_to_change = null;
var checked_products = [];
var credit_value = 0;

$(window).bind('beforeunload', function(){ if(exit) return 'Aby na pewno chcesz opuscić tą stronę?'; });

function add_to_basket(prod_name, prod_id, prod_quantity, prod_price, prod_amount, prod_unit) {
    exit = 1;
    if(client == null || client == '') client = 48;
    $("#basket_button").css("visibility", "visible");

    if(prod_unit == 'op') prod_unit = 1;
    else if(prod_unit == 'szt') prod_unit = 2;
    else prod_unit = 3;

    let new_product = [];
    new_product.push(prod_name, prod_id, prod_quantity, prod_price, prod_amount, prod_unit);
    basket.push(new_product);

    value += prod_quantity * prod_price;
    change_value(value);

    insert_temp();
}

function change_price() {
    let new_unit_price_form = $("#new_unit_price").val();
    if($("#unit_input").val() == 'op') 
        $("#price").val(new_unit_price_form*amount);
    else
        $("#price").val(new_unit_price_form);
}

function change_product() {
    let id_prod_to_change = prod_to_change;
    let name_change = basket[id_prod_to_change-1][0];
    let quantity_change = basket[id_prod_to_change-1][2];
    let price_change = basket[id_prod_to_change-1][3];
    let amount_change = basket[id_prod_to_change-1][4];
    let unit_change = basket[id_prod_to_change-1][5];

    if(unit_change == 1) unit_change = 'op';
    else if(unit_change == 2) unit_change = 'szt';
    else unit_change = 'kg';

    if(unit_change == 'szt') amount_change = 1;

    amount = amount_change;
    unit_price = price_change/amount_change;
    price = price_change;

    let mess_change = '<form id="change">';
            mess_change += '<div class="form-group">';
                mess_change += '<script>$("input[type=\'number\']").inputSpinner();</script>';
                mess_change += '<label for="quantity">Ilość:</label>';
                mess_change += '<input type="number" class="form-control" id="quantity" value="' + quantity_change + '" min="0.1" max="10000" step="1" data-decimals="2" data-suffix="'+unit_change+'">';
                mess_change += '<input type="hidden" id="hidden_quantity" value="' + quantity_change + '">';
            mess_change += '</div>';
            mess_change += '<div class="form-group">';
                mess_change += '<label for="price">Cena:</label>';
                mess_change += '<input type="number" id="price" class="form-control" value="'+price_change+'" min="0.1" max="1000" step="1" data-decimals="2" data-suffix-pln="zł">';
                mess_change += '<input type="hidden" id="hidden_price" value="' + price_change + '">';
            mess_change += '</div>';
            mess_change += '<div class="form-group">';
                mess_change += '<label for="new_unit_price">Cena kg/szt:</label>';
                mess_change += '<input type="number" id="new_unit_price" class="form-control" value="'+price_change/amount_change+'" min="0.1" max="1000" step="0.1" data-decimals="2" data-suffix-pln="zł">';
            mess_change += '</div>';
            mess_change += '<script>var $inputPrice = $("#price"); var $inputNewUnitPrice = $("#new_unit_price"); $inputPrice.on("change", change_unit_price); $inputNewUnitPrice.on("change", change_price)</script>'
        mess_change += '</form>';

    bootbox.dialog({ title: 'Zmiana dla: ' + name_change, message: mess_change, size: 'large',
        buttons: {
            cancel: { label: 'Anuluj', className: 'btn-outline-danger',
                callback: function() {
                    prod_to_change = null;
                    open('show_basket');
                }
            },
            ok: { label: 'Zmień', className: 'btn-outline-success',
                callback: function() {
                    exit = 1;
                    let id_prod_to_change = prod_to_change;
                    let price_form = $("#price").val();
                    let quantity = $("#quantity").val();
                    let new_unit_price_form = $("#new_unit_price").val();
                    let amount = basket[id_prod_to_change-1][4];
                    let unit = $("#unit_of_p").text();
                    if(unit == 'kg')
                        amount = price_form / new_unit_price_form;

                    if(unit == 'op') unit = 1;
                    else if(unit == 'szt') unit = 2;
                    else unit = 3;

                    let old_price = $("#hidden_price").val();
                    let old_quantity = $("#hidden_quantity").val();
                    value -= old_price * old_quantity;
                    value += price_form * quantity;
                    change_value(value);

                    basket[id_prod_to_change-1][2] = quantity;
                    basket[id_prod_to_change-1][3] = price_form;
                    basket[id_prod_to_change-1][4] = amount;
                    basket[id_prod_to_change-1][5] = unit;
                    prod_to_change = null;

                    insert_temp();
                    open('show_basket');
                }
            }
        }
    });
}

function change_unit() {
    let unit = $("#unit_of_p").text();
    if(unit == 'op') {
        $("#unit_of_p").text('szt');
        $("#unit_input").val('szt');
        $('#price')[0].setValue(unit_price);
        $('#new_unit_price')[0].setValue(unit_price);
    }
    else if(unit == 'szt') {
        $("#unit_of_p").text('kg');
        $("#unit_input").val('kg');
        $('#price')[0].setValue(unit_price);
        $('#new_unit_price')[0].setValue(unit_price);
    }
    else if(unit == 'kg') {
        $("#unit_of_p").text('op');
        $("#unit_input").val('op');
        $('#price')[0].setValue(price);
        $('#new_unit_price')[0].setValue(price/amount);
    }
}

function change_unit_price() {
    let price_form = $("#price").val();
    if($("#unit_input").val() == 'op')
        $("#new_unit_price").val(price_form/amount);
    else
        $("#new_unit_price").val(price_form);
}

function change_value(new_value) {
    let lenght = basket.length - del;
    if(typeof name_client == 'undefined')
        name_client = 'Nie podano';

    new_value = Math.round( new_value * 100) / 100;
    $("#elements").text( new_value + 'zł (' + lenght + ')');
    document.title = new_value + 'zł' + ' - ' + name_client;
}

function check(row_id) {
    let d = $('#row_table' + row_id);

    if(!d.hasClass('table-success') && !d.hasClass('table-danger')) {
        d.addClass('table-success');
        checked_products.push(row_id);
    }
    else if(d.hasClass('table-success')) {
        if(!prod_to_change) {
            d.removeClass('table-success');
            d.addClass('table-danger');
            prod_to_change = row_id;
            let index = checked_products.indexOf(row_id);
            if (index > -1)
                checked_products.splice(index, 1);
        }
        else
            open('show_basket', 'Możesz zaznaczyć na raz tylko 1 element!');
    }
    else if(d.hasClass('table-danger')) {
        d.removeClass('table-danger');
        prod_to_change = null;
    }
}

function finish() {
    if(typeof id_mysql == 'undefined'){
        my_alert('Nic nie zostało zmienione!');
        return;
    }
    if(window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if(this.responseText != 0) {
                delete window.id_mysql;
                basket = [];
                value = 0;
                checked_products = [];
                exit = 0;

                let today = new Date();
                let dd = String(today.getDate()).padStart(2, '0');
                let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                let yyyy = today.getFullYear();

                today = yyyy + '-' + mm + '-' + dd;

                if(typeof order_day != 'undefined')
                    today = order_day;

                let echo = JSON.parse(this.responseText); 
                let order_id = echo[3];
                let value_basket = Math.round(echo[2]);
                let id = echo[1];
                let encoded_products = echo[0];
                let products = JSON.parse(encoded_products);

                let finish_mess = '<table class="table table-striped table-bordered container-fluid" id="finish_table">';
                        finish_mess += '<thead>';
                            finish_mess += '<tr>';
                                finish_mess += '<th scope="col">Produkt</th>';
                                finish_mess += '<th scope="col">Ilość</th>';
                                finish_mess += '<th scope="col">Cena</th>';
                                finish_mess += '<th scope="col">Wart</th>';
                            finish_mess += '</tr>';
                        finish_mess += '</thead>';
                        finish_mess += '<tbody>';

                        for(let product of products) {
                            finish_mess += '<tr>';
                                finish_mess += '<td>' + product[0] + '</td>';
                                finish_mess += '<td>' + product[2] + '</td>';
                                finish_mess += '<td>' + product[3] + '</td>';
                                finish_mess += '<td>' + Math.round( product[3]*product[2] * 100) / 100 + '</td>';
                            finish_mess += '</tr>';
                        }
        
                        finish_mess += '</tbody>';
                    finish_mess += '</table>';
                    finish_mess += '<div class="float-right" id="value_basket">Wartość: ' + value_basket + ' zł</div>';

                bootbox.dialog({ title: 'Zamówienie nr ' + id + '/' + today, message: finish_mess, size: 'large', closeButton: false,
                    buttons: {
                        back: { label: 'Powrót', className: 'btn-outline-primary',
                            callback: function() { window.location = href_name; }
                        },
                        cancel: { label: 'Kredyt', className: 'btn-outline-info',
                            callback: function() { 
                                if(typeof credit == 'undefined') {
                                    credit = '<div class="form-group" id="div_credit">';
                                        credit += '<label for="credit_input">Wpłacono:</label>';
                                        credit += '<input type="number" id="credit_input" class="form-control" value="0" min="0" max="'+value_basket+'">';
                                        credit += '<script>credit_value = '+value_basket+' - $("#credit_input").val();';
                                        credit += '$("#credit_input").bind("keyup change", function(e) {';
                                            credit += 'credit_value = '+value_basket+' - $("#credit_input").val();';
                                            credit += 'last_value = '+value_basket+'-credit_value;';
                                            credit += 'if(credit_value>'+value_basket+') $("#value_basket").text("Wartość: 0 zł");'; 
                                            credit += 'else $("#value_basket").text("Wartość: " + last_value + " zł");'; 
                                            credit += '$("#credit_val_div").text("Kredyt: " + credit_value + " zł");});';
                                        credit += '$("#value_basket").text("Wartość: 0 zł");</script>';
                                    credit += '</div>';
                                    credit_val_div = '<div class="float-right" id="credit_val_div">Kredyt: ' + value_basket + ' zł</div><br>';
                                    $("#finish_table").after(credit);
                                    $("#div_credit").after(credit_val_div);
                                }
                                else {
                                    $("#div_credit").remove();
                                    $("#credit_val_div").next().remove();
                                    $("#credit_val_div").remove();
                                    $("#value_basket").text("Wartość: "+value_basket+" zł");
                                    delete credit;
                                    credit_value = 0;
                                }
                                
                                return false;
                            }
                        },
                        noclose: { label: 'Zmień', className: 'btn-outline-warning',
                            callback: function() { window.location = 'main.php?edit=' + order_id; }
                        },
                        ok: { label: 'Drukuj', className: 'btn-outline-success',
                            callback: function() {
                                if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest()
                                xmlhttp.open("GET","include/actions.php?print="+order_id+"&credit="+credit_value,true);
                                xmlhttp.send();
                                return false; 
                            }
                        }
                    }
                });
            }
            else
                my_alert('Wystąpił błąd!');
        }
    };
    xmlhttp.open("GET","include/actions.php?finish="+id_mysql,true);
    xmlhttp.send();
}

function get_client() {
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if(this.responseText != 0)
                name_client = this.responseText;
            else
                name_client = 'Nie podano';
        }
    };
    xmlhttp.open("GET","include/actions.php?get_name="+client,true);
    xmlhttp.send();
}

function insert_temp() {
    let basket_json = JSON.stringify(basket); 
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if(this.responseText != 0)
                id_mysql = this.responseText;
            else
                my_alert('Wystąpił błąd!');
        }
    };
    if (typeof id_mysql !== 'undefined')
        xmlhttp.open("GET","include/actions.php?insert_temp="+basket_json+"&val="+value+"&id_mysql="+id_mysql+"&edit_id="+edit,true);
    else
        xmlhttp.open("GET","include/actions.php?insert_temp="+basket_json+"&val="+value+"&client="+client+"&edit_id="+edit,true);
    xmlhttp.send();
}

function open(name, text) {
    bootbox.hideAll();

    let code = name + '()';

    if(text) {
        setTimeout(function() {  
            bootbox.alert({ message: text, backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-warning' } }, callback: function () {
                setTimeout(function() { eval(code); }, 400);
            } });
        }, 400);
    }
    else {
        setTimeout(function() { eval(code); }, 400);
    }
}

function my_alert(text) {
    bootbox.hideAll();
    setTimeout(function() { bootbox.alert({ message: text, backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } }); }, 400);
}

function product(id, unit, amount) {
    window.amount = amount;
    window.price = $('#row'+id).children().eq(2).text();    
    window.name = $('#row'+id).children().first().text();
    window.unit_price = $('#row'+id).children().eq(3).text();

    if(unit == 'szt') new_price = price/amount;
    else new_price = price;

    let mess = '<form id="add">';
            mess += '<div class="form-group">';
                mess += '<script>$("input[type=\'number\']").inputSpinner();</script>';
                mess += '<label for="quantity">Ilość:</label>';
                mess += '<input type="number" class="form-control" id="quantity" value="1" min="0.1" max="10000" step="1" data-decimals="2" data-suffix="'+unit+'">';
            mess += '</div>';
            mess += '<div class="form-group">';
                mess += '<label for="price">Cena:</label>';
                mess += '<input type="number" id="price" class="form-control" value="'+new_price+'" min="0.1" max="1000" step="1" data-decimals="2" data-suffix-pln="zł">';
            mess += '</div>';
            mess += '<div class="form-group">';
                mess += '<label for="new_unit_price">Cena kg/szt:</label>';
                mess += '<input type="number" id="new_unit_price" class="form-control" value="'+price/amount+'" min="0.1" max="1000" step="0.1" data-decimals="2" data-suffix-pln="zł">';
            mess += '</div>';
            mess += '<script>var $inputPrice = $("#price"); var $inputNewUnitPrice = $("#new_unit_price"); $inputPrice.on("change", change_unit_price); $inputNewUnitPrice.on("change", change_price)</script>'
        mess += '</form>';

    delete new_price;

    bootbox.dialog({ title: name, message: mess, onEscape: true, backdrop: true,
        buttons: {
            info: { label: "Info", className: 'btn-outline-info',
                callback: function () {
                    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            if(this.responseText)
                                my_alert(this.responseText);
                            else
                                my_alert('Wystąpił błąd!');
                        }
                    };
                    xmlhttp.open("GET","include/actions.php?info="+id,true);
                    xmlhttp.send();
                }
            },
            delete: { label: "Usuń", className: 'btn-outline-danger',
                callback: function() {
                    setTimeout(function() {
                        bootbox.confirm({ message: "Czy aby na pewno chcesz usunąć ten produkt?", onEscape: true, backdrop: true,
                            buttons: {
                                confirm: { label: 'Tak', className: 'btn-outline-success' },
                                cancel: { label: 'Nie', className: 'btn-outline-danger' }
                            },
                            callback: function (result) {
                                if(result) {
                                    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
                                    xmlhttp.onreadystatechange = function() {
                                        if (this.readyState == 4 && this.status == 200) {
                                            if(this.responseText == 1) {
                                                my_alert('Usunięto produkt pomyślnie!');
                                                $('#row'+id).remove();
                                            }
                                            else
                                                my_alert('Wystąpił błąd');
                                        }
                                    };
                                        xmlhttp.open("GET","include/actions.php?delete_product="+id,true);
                                        xmlhttp.send();
                                }
                            }
                        });
                    }, 400);
                }
            },
            change: { label: "Zmień", className: 'btn-outline-warning',
                callback: function() {
                    setTimeout(function() {
                        let mess_change = '<form id="change">';
                                mess_change += '<div class="form-group">';
                                    mess_change += '<script>$("input[type=\'number\']").inputSpinner()</script>';
                                    mess_change += '<label for="price_new">Nowa cena:</label>';
                                    mess_change += '<input type="number" class="form-control" id="price_new" value="'+price+'" min="0.1" max="1000" step="1" data-decimals="2">';
                                mess_change += '</div>';
                            mess_change += '</form>';
                        bootbox.dialog({ title: "Podaj nową cenę dla \""+name+"\":", message: mess_change, onEscape: true, backdrop: true,
                            buttons: {
                                cancel: { label: "Anuluj", className: 'btn-outline-danger' },
                                ok: { label: "Zmień", className: 'btn-outline-success',
                                    callback: function (result) {
                                        if(result) {
                                            let new_price = $("#price_new").val();
                                            if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
                                            xmlhttp.onreadystatechange = function() {
                                                if (this.readyState == 4 && this.status == 200) {
                                                    if(this.responseText) {
                                                        my_alert('Zmieniono cenę pomyślnie!');
                                                        $('#row'+id).children().eq(2).text(new_price);
                                                        $('#row'+id).children().eq(3).text(Math.round(new_price/this.responseText * 100) / 100);
                                                    }
                                                    else 
                                                        my_alert('Wystąpił błąd');
                                                }
                                            };
                                            xmlhttp.open("GET","include/actions.php?change_price="+new_price+"&id="+id,true);
                                            xmlhttp.send();
                                        }
                                    }
                                }
                            }
                        });
                    }, 400);
                }
            },
            add: { label: "Dodaj", className: 'btn-outline-success',
                callback: function () {
                    let price_form = $("#price").val();
                    let quantity = $("#quantity").val();
                    let new_unit_price_form = $("#new_unit_price").val();
                    let unit = $("#unit_of_p").text();
                    if(unit == 'kg') amount = price_form / new_unit_price_form;
                    add_to_basket(name, id, quantity, price_form, amount, unit);
                }
            }
        }
    });
}

function show_basket() {
    let basket_mess;
    let lenght = basket.length - del;
    prod_to_change = null;

    if(lenght>0) {
        count_op = 0;
        count_szt = 0;
        basket_mess = '<table class="table table-striped table-hover table-bordered container-fluid my-0" id="basket_table">';
            basket_mess += '<thead>';
                basket_mess += '<tr>';
                    basket_mess += '<th scope="col">Produkt</th>';
                    basket_mess += '<th scope="col">Ilość</th>';
                    basket_mess += '<th scope="col">Cena</th>';
                    basket_mess += '<th scope="col">Wart</th>';
                basket_mess += '</tr>';
            basket_mess += '</thead>';
            basket_mess += '<tbody>';

            for (let product of basket) {
                if(product == 'DEL') continue;

                let i = basket.indexOf(product) +1;

                if(checked_products.includes(i)) $class='class="table-success"';
                else $class='';

                if(product[5] == 1) { unit_basket = 'op'; count_op += Math.ceil(product[2]) }
                else if(product[5] == 2) { unit_basket = 'szt'; count_szt += Math.ceil(product[2]) }
                else unit_basket = 'kg';

                basket_mess += '<tr id="row_table'+i+'" onclick="check('+i+')"'+$class+'>';
                    basket_mess += '<td>' + product[0] + '</td>';
                    basket_mess += '<td>' + product[2] + ' ' + unit_basket + '</td>';
                    basket_mess += '<td>' + product[3] + '</td>';
                    basket_mess += '<td>' + Math.round( product[3]*product[2] * 100) / 100 + '</td>';
                basket_mess += '</tr>';
            }

            basket_mess += '</tbody>';
        basket_mess += '</table>';
        basket_mess += '<div class="mt-2 pl-1">';
            basket_mess += 'Opakowań: ' + count_op + ', Sztuk: ' + count_szt;
        basket_mess += '</div>';
    }
    else
        basket_mess = 'Brak produktów';

    bootbox.dialog({ title: 'Koszyk:', message: basket_mess, onEscape: true, backdrop: true, size: 'large',
        buttons: {
            delete: { label: 'Usuń', className: 'btn-outline-danger',
                callback: function() {
                    setTimeout(function() { 
                        if(!prod_to_change>0)
                            open('show_basket', 'Nie wybrano produktu!');
                        else
                        {
                            let index = prod_to_change-1;
                            if(index > -1) {
                                let quantity_del = basket[index][2];
                                let price_del = basket[index][3];
                                let val = quantity_del*price_del;
                                del++;

                                basket[index] = 'DEL';
                                prod_to_change = null;

                                value -= val;
                                change_value(value);
                                insert_temp();
                                open('show_basket');
                            }
                        }
                    }, 400);
                }
            },
            change: { label: 'Zmień', className: 'btn-outline-warning',
                callback: function() {
                    if(!prod_to_change>0)
                    {
                        open('show_basket', 'Nie wybrano produktu!');
                    }
                    else 
                        open('change_product');
                },
            },
            ok: { label: 'Zakończ', className: 'btn-outline-success',
                callback: function() {
                    open('finish');
                }
            }
        }
    });
}
