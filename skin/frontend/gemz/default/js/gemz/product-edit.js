(function($){
    $(document).ready(function(){
        $('select#style').on('click', function(){
            console.log($('select#category').val());
            if ($('select#category').val() == "" || $('select#category').val() == null) {
                alert('Please select categories first');
            }
        });
        changeStyleSelect();
        $('select#category').on('change', function(e){
            changeStyleSelect();
        });
    });

    function changeStyleSelect()
    {
        var catIds = $('select#category').val();
        if (catIds) {
            $.get('/marketplace/product/getstyles?cat_ids=' + catIds.join(), function($data){
                console.log($data);
                $('select#style option:not(:first-child)').each(function(){
                    $(this).prop('disabled', true);
                    if ($data.indexOf($(this).text()) != -1 || $(this).attr('value') == null) {
                        $(this).prop('disabled', false);
                        console.log('Show', $(this).text());
                    }
                    $('select#style').trigger('change');
                });
            });
        } else {
            $('select#style option').prop('disabled', true);
        }
    }
})(jQuery)
