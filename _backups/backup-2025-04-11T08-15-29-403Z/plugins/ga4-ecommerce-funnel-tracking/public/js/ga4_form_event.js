console.log("gform-tracking-file");
alert("form tracking js enqueued.");
dataLayer.push({
  event: form_data.form_title ? form_data.form_title : "form_tracking",
  ecommerce: {
    form_title: form_data.form_title,
    form_fields: form_data.form_fields,
  },
});
