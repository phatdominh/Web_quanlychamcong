/**
 * @Object data Global
 */
const data = {
  'monthCurrent': new Date().getMonth() + 1,
  'yearCurrent': new Date().getFullYear(),
  'apiReportAll': $('#reportAll').text(),
  'detailEmployee':$('#detailEmployee').text(),
}
/**
 * @function get number day of month
 * @param year and month
 */
const getNumberOfMonth = (year, month) => {
  return new Date(year, month, 0).getDate();
};
/**
 *@function Check Saturday and Sunday
 @param year month day
 */
function checkSatAndSun(year, month, day) {
  return new Date(`${year}-${month}-${day}`).getDay();
}
/**
 * @function Call API
 * @param year and month
 */
function getListProjectOfEmployeesAPI(year, month) {
  try {
    let yearAndMonth = `${year}-${month}`
    let html = ``, thead = ``, tbody = ``, tfoot = ``;
    let numberOfMonth = getNumberOfMonth(year, month);
    let total = [];
    $.ajax({
      type: "GET",
      url: `${data.apiReportAll}/${yearAndMonth}`,
      dataType: "json",
      success: function (response) {
        if (response.status === 200) {
          const arrayProjectEmployee = response.list;
          arrayProjectEmployee.forEach((key, value) => {
            html += `<table class="table table-primary" border="1px">
            <caption style="caption-side: top;"><a href="${data.detailEmployee}/${key.id}">${key.name}</a></caption>`
            thead = `<thead><tr><th class="align-middle">Dự án</th>`
            for (let i = 1; i <= numberOfMonth; i++) {
              let check = checkSatAndSun(year, month, i);
              thead += `<th class="align-middle ${check == 0 ? "bg-primary" : check == 6 ? "bg-info" : false
                }">${i}</th>`;
            }
            thead += `</tr></thead>`
            tbody = `<tbody>`
            let arrProject = key.name_day_hours
            if (key.name_day_hours != 0) {
              arrProject.forEach((subkey, subvalue) => {
                if (subkey.id == key.id) {
                  tbody += `<tr><th>${subkey.nameProject}</th>`
                  let days = [];
                  let dayWorks = subkey.days;
                  dayWorks.forEach(function (dayWork) {
                    days.push(dayWork.day_work);
                  });
                  for (let index = 1; index <= numberOfMonth; index++) {
                    if (!total.hasOwnProperty(`${index}-${key.id}`)) {
                      total[`${index}-${key.id}`] = 0;
                    }
                    let keyByDay = days.indexOf(index);
                    let check = checkSatAndSun(year, month, index);
                    if (keyByDay != -1) {
                      tbody += `<td class="align-middle
                      ${check == 0
                          ? "bg-primary"
                          : check == 6
                            ? "bg-info"
                            : false}
                      ">${dayWorks[keyByDay].hours}</td>`
                      total[`${index}-${key.id}`] += dayWorks[keyByDay].hours;
                    } else {
                      tbody += `<td class="align-middle  ${check == 0
                        ? "bg-primary"
                        : check == 6
                          ? "bg-info"
                          : false}"></td>`
                    }
                  }
                  tbody += `</tr>`
                }
              })
            }
            tbody += `</tbody>`
            tfoot = `<tfoot><tr><td>Total</td>`
            for (let index = 1; index <= numberOfMonth; index++) {
              let check = checkSatAndSun(year, month, index);
              tfoot += `<td class="align-middle
              ${check == 0
                  ? "bg-primary"
                  : check == 6
                    ? "bg-info"
                    : false}
              ">${(total[`${index}-${key.id}`] != 0) ? total[`${index}-${key.id}`] : ""}</td>`
            }
            tfoot += ` </tr > </tfoot > `
            html += `${thead}${tbody}${tfoot}</table > `
          });
          $('#reportAllofEmployee').html(html);
        } else {
          let caption = `<h2>Không có dữ liệu</h2>`
          $('#reportAllofEmployee').html(caption);
        }
      }
    });
  } catch (error) {
    console.error(error);
  }
}
/**
 * @function showProjectofEmployees
 * @param year month
 */
function showProjectofEmployee(year, month) {
  getListProjectOfEmployeesAPI(year, month);
}
/**
 * @function showProjectofEmployeeCurrent
 */
function showProjectofEmployeeCurrent() {
  let year = data.yearCurrent;
  let month = data.monthCurrent;
  showProjectofEmployee(year, month);
}
/**
 * change action input month
 */
$("input[name=month]").change(function () {
  var getYear = $(this).val().slice(0, 4);
  var getMonth = $(this).val().slice(5, 8);
  showProjectofEmployee(getYear, getMonth)
})
/**Get Month current */
const getMonthCurrent = () => {
  flatpickr("#month_flatpickr", {
    // locale: "ja",
    maxDate: "today",
    plugins: [
      new monthSelectPlugin({
        shorthand: true, //defaults to false
        dateFormat: "Y/m", //defaults to "F Y"
        altFormat: "F Y", //defaults to "F Y"
        theme: "light", // defaults to "light"
      }),
    ],
  });
};
/**
 * Call all functions
 */
getMonthCurrent()
showProjectofEmployeeCurrent();
