jQuery(document).ready(function () {
  console.log("cart page loaded");
  dataLayer.push({
    event: "view_cart",
    ecommerce: {
      currency: cart_data["currency"],
      items: cart_data["items"],
      //   couponCode: cart_data["coupon_code"],
    },
  });
});
