wp.hooks.addAction(
  "experimental__woocommerce_blocks-checkout-set-selected-shipping-rate",
  "class-wc-tripay-blocks",
  function (shipping) {
    console.log(
      document.querySelector(
        'input[name="radio-control-wc-payment-method-options"]:checked'
      ).value
    );
    var update_cart = extensionCartUpdate({
      namespace: "class-wc-tripay-blocks",
      data: {
        shipping_method: shipping.shippingRateId,
        payment_method: document.querySelector(
          'input[name="radio-control-wc-payment-method-options"]:checked'
        ).value,
      },
    });
  }
);
