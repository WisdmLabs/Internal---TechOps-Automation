jQuery(document).ready(function () {
  console.log("Purchase Complete");
  dataLayer.push({
    event: "purchase_event",
    ecommerce: {
      orderNumber: order_data["order_number"],
      orderTotal: order_data["order_total"],
      billingEmail: order_data["billing_email"],
      billingPhone: order_data["billing_phone"],
      paymentMethod: order_data["payment_method"],
      country: order_data["country"],
      customerId: order_data["customer_id"],
    },
  });
});
