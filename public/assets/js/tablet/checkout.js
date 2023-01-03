let count = 0;
const url = {
    listProject: $("#listProject").attr("data-listProject"),
    countProject: $("#countProject").attr("data-countProject"),
};
/**Get Day current */
function getDayCurrent() {
    flatpickr("#tablet_flatpickr", {
        // locale: "ja",
        maxDate: "today",
        dateFormat: "Y/m/d",
    });
}
/**Get API Projects */
function getProjectAPI(count, dayWork) {
    try {
        $.ajax({
            type: "GET",
            url: `${url.listProject}/${dayWork}`,
            dataType: "json",
            success: function (response) {
                let arrProject = response.employeeProject;
                let data = response.projects.map((project) => {
                    if (arrProject[count]) {
                        let checkProject =
                            arrProject[count].project_id == project.id
                                ? "selected"
                                : "";
                        let checkHours =
                            arrProject[count].project_id == project.id
                                ? arrProject[count].working_hours
                                : arrProject[count].working_hours;
                        $(`#hour-${count}`).val(checkHours);
                        return `<option value="${project.id}"${checkProject}>${project.name} </option>`;
                    }
                    if (arrProject.length > 1) {
                        $(`#hour-${count}`).val("");
                        return `<option value="${project.id}">${project.name}</option>`;
                    }
                    if (arrProject.length < 1) {
                        return `<option value="${project.id}">${project.name} </option>`;
                    }
                    return `<option value="${project.id}">${project.name} </option>`;
                });
                $(`#project-${count}`).append(data);
            },
        });
    } catch (error) {
        console.error(error);
    }
}
/**Show display */
function showDisplayItem(count) {
    let html = `<tr  style="display: flex; margin-bottom:20px;" id="remove-item">
    <td  style="display: flex;width: 95% !  important">
    <select name="projects[]" id='project-${count}' class="form-control"
    ><option value="" hidden>Vui lòng chọn</option>
                </select>
            </td>
            <td style="padding-left:15px;display: flex;">
    <input type="number" onkeypress='validate(event)'
     autofocus maxlength="4" class="form-control" id="hour-${count}"
     name="hours[]" step="any" placeholder="Số giờ" /></td>`;
    if (count === 0) {
        html += `<td style="padding-left:15px">
            <button style="width:38px;height:38px"  onclick="handleClickAdd()" type="button"
            class="btn btn-info d-inline"><i class="fa-solid fa-plus"></i></button></td>`;
    } else {
        html += `
        <td style="padding-left:15px">
        <button  style="width:38px;height:38px" id="remove" data-idRemove=${count}  class="btn btn-danger d-inline" type="button">
        <i class="fa-solid fa-remove"></i> </button></td>`;
    }
    html += `
        </td>
    </tr>`;
    return html;
}
/**Show Display Default */
const dateNow = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1; // Months start at 0!
    let dd = today.getDate();
    if (dd < 10) dd = "0" + dd;
    if (mm < 10) mm = "0" + mm;
    const formattedToday = yyyy + "-" + mm + "-" + dd;
    return formattedToday;
};
function showDefault() {
    try {
        $.ajax({
            type: "GET",
            url: `${url.countProject}/${dateNow()}`,
            dataType: "json",
            success: function (response) {
                let countProject = response.countProject;
                if (countProject == 0) {
                    getProjectAPI(0, dateNow());
                    $("thead").html(showDisplayItem(0));
                }
            },
        });
    } catch (error) {
        console.error(error);
    }
}
/**Show list projects of employee Current*/
function showCurrent() {
    try {
        $.ajax({
            type: "GET",
            url: `${url.countProject}/${dateNow()}`,
            dataType: "json",
            success: function (response) {
                let countProject = response.countProject;
                if (countProject > 0) {
                    for (let i = 0; i < countProject; i++) {
                        if (i == 0) {
                            getProjectAPI(i, dateNow());
                            $("thead").html(showDisplayItem(i));
                            continue;
                        }
                        getProjectAPI(i, dateNow());
                        $("tbody").append(showDisplayItem(i));
                    }
                    count = countProject;
                }
            },
        });
    } catch (error) {
        console.error(error);
    }
}
/**Change Day */
$("input[name=dayWork]").change(function () {
    try {
        let formatDate = $(this).val().replaceAll("/", "-");
        $.ajax({
            type: "GET",
            url: `${url.countProject}/${formatDate}`,
            dataType: "json",
            success: function (response) {
                let countProject = response.countProject;
                if (countProject > 0) {
                    $("thead").empty();
                    $("tbody").empty();
                    for (let i = 0; i < countProject; i++) {
                        if (i == 0) {
                            getProjectAPI(i, formatDate);
                            $("thead").html(showDisplayItem(i));
                            continue;
                        }
                        getProjectAPI(i, formatDate);
                        $("tbody").append(showDisplayItem(i));
                    }
                    count = countProject;
                } else {
                    getProjectAPI(0, formatDate);
                    $("thead").html(showDisplayItem(0));
                    $("tbody").empty();
                }
            },
        });
    } catch (error) {
        console.error(error);
    }
});
/**Add Item*/
const handleClickAdd = () => {
    count++;
    getProjectAPI(count, dateNow());
    $("tbody").append(showDisplayItem(count));
};
/**Remove Item*/
$(document).on("click", "#remove", function () {
    $(this).closest("#remove-item").remove();
});
function ShowHideMessage() {
    setTimeout(() => {
        $(".flashMessage").hide();
    }, 3000);
}
/** Xử lý không cho nhập chữ*/
function validate(evt) {
    var theEvent = evt || window.event;
    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
        // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\./;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
}
/**Call functions */
getDayCurrent();
showDefault();
showCurrent();
ShowHideMessage();
/**Call functions */
