jQuery(document).ready(function () {
  console.log("Product page loaded");
  //   dataLayer.push({ ecommerce: null });
  //   window.dataLayer = [];
  dataLayer.push({
    event: "single_product_view",
    ecommerce: {
      item_id: data["item_id"],
      item_name: data["item_name"],
      discount: data["discount"],
      item_brand: data["item_brand"],
      item_category: data["item_category"],
      price: data["price"],
    },
  });
});