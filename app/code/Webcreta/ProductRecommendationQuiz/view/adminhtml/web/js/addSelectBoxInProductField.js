require(['jquery','select2'], function($, Select2) {
    function observeChanges() {  
        const targetDiv = $('#smb_product_id');
        if (targetDiv.length > 0) {
            $('[name="product_id"]').select2();
        } else {
            setTimeout(observeChanges, 500);
        }
        
    }
    observeChanges();
});
