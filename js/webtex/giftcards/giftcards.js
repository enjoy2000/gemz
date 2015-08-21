function generateGiftCards(url, product){
    cards_count = $('create_count').value;
    
    new Ajax.Request(url, {
        method: 'get',
        parameters: {count: cards_count, product_id: product},
        onSuccess: function(transport){
            document.location.reload();
        }
    });
}

function importGiftCards(url, product){
    params = $('product_edit_form').serialize();
    alert($('import_file').value);
    params['product_id'] = product;
    new Ajax.Request(url, {
        method: 'get',
        parameters: params,
        onSuccess: function(transport){
            //document.location.reload();
        }
    });
}

