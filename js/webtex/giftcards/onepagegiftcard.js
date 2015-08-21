var OnepageGiftcard = Class.create();
OnepageGiftcard.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.saveUrl = saveUrl;
        this.validator = new Validation(this.form);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    validate: function() {

    },

    save: function(){

        if (checkout.loadWaiting!=false) return;
            checkout.setLoadWaiting('payment');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
    },

    deactivate: function(id) {
        if (checkout.loadWaiting!=false) return;
        checkout.setLoadWaiting('payment');
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout),
                parameters: 'id=' + id
            }
        );
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            alert(response.error);
            return false;
        }

        //update gift card section
        if(response.giftcard_section) {
            $('giftcard-section').update(response.giftcard_section.html);
        }

        //update payment methods
        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }

        payment.initWhatIsCvvListeners();

        if (response.goto_section) {
            checkout.gotoSection(response.goto_section);
            checkout.reloadProgressBlock();
            return;
        }

        if (response.payment_methods_html) {
            $('checkout-payment-method-load').update(response.payment_methods_html);
        }

        //checkout.setShippingMethod();
    }
}