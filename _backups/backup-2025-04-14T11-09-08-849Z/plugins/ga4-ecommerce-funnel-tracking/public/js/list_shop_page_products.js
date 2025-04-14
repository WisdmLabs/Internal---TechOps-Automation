jQuery(document).ready(function () {
  console.log("shop page loaded");
  //   dataLayer.push({ ecommerce: null });
  //   window.dataLayer = [];
  dataLayer.push({
    event: "list_shop_page_products",
    ecommerce: {
      currency: item_list_data["currency"],
      items: item_list_data["items"],
    },
  });
});
