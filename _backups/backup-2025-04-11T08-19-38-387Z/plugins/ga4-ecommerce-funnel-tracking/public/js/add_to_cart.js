jQuery(document).ready(function () {
  console.log("Connected to cart");
  //   dataLayer.push({ ecommerce: null });
  dataLayer.push({
    event: "add_to_cart",
    ecommerce: {
      currency: item_data["currency"],
      value: parseFloat(item_data["value"]),
      items: item_data["item"],
    },
  });
});
