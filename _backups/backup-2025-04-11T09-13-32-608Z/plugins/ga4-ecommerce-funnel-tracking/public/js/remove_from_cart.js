jQuery(document).ready(function () {
  console.log("Removed from cart");
  //   dataLayer.push({ ecommerce: null });
  dataLayer.push({
    event: "remove_from_cart",
    ecommerce: {
      currency: item_data["currency"],
      value: parseFloat(item_data["value"]),
      items: item_data["item"],
    },
  });
});
