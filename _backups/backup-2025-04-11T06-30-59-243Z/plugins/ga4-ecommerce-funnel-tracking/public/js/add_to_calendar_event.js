jQuery(document).ready(function () {
  console.log("Added to the calendar JS file loaded.");
  // console.log(cal_data);
  // cal_data.is_saved_in_calendar = "no";

  // jQuery(document).ajaxComplete(function (event, xhr, settings) {
  //   if (settings.url.indexOf("sync.sync") !== -1 && xhr.status === 200) {
  //     // If 'sync.sync' AJAX request is successful, update data.is_saved_in_calendar to true
  //     cal_data.is_saved_in_calendar = "yes";
  //   }
  // });

  cal_data.dates = jQuery(".tribe-events-schedule__datetime").value()
    ? jQuery(".tribe-events-schedule__datetime").value()
    : "NULL";

  console.log(cal_data.is_saved_in_calendar);

  jQuery(".tribe-events-c-subscribe-dropdown__list-item-link").click(
    function () {
      console.log("Add to calendar link is Clicked.");
      cal_data.is_saved_in_calendar = "yes";
      dataLayer.push({
        event: "add_to_calendar",
        ecommerce: {
          event_title: cal_data.text,
          event_dates: cal_data.dates,
          save_status: cal_data.is_saved_in_calendar,
        },
      });
    }
  );
});
