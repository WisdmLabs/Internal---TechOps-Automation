jQuery(document).ready(function () {
  console.log("Checkout Page loaded");
  dataLayer.push({
    event: "checkout_event",
    ecommerce: {
      currency: cart_data["currency"],
      items: cart_data["items"],
      couponCode: cart_data["coupon_code"],
    },
  });
});
