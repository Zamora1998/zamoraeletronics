loadCategories(false);
var ArrBefore = [];
var ArrAfter = [];

$(document).on('click', '[id^="edit_"], [id^="add_"], #add, #categoryCreate', function (e) {
    e.preventDefault();
    clearCategoryForm();
    var id = $(this).data('id');
    var buttonId = $(this).attr('id');

    if (buttonId.startsWith('add_') || buttonId === 'add') {
        $('#modalCategories').data('id', id);
        $('#modalCategories').data('action', 'C');
        $('#modalCategories').data('part', 'C');
        $('#modalCategories h1').text(labels.lblNewSubCategory);
    } else if (buttonId.startsWith('edit_')) {
        $('#modalCategories').data('id', id);
        $('#modalCategories').data('action', 'C');
        $('#modalCategories').data('part', 'E');
        $('#modalCategories h1').text(labels.lblEditCategory);
        if (id) {
            $.ajax({
                type: 'post',
                url: ROUTES_CTRL,
                data: {
                    action: 'R',
                    part: 'O',
                    id: id,
                },
                dataType: 'json',
                success: function (response) {
                    var data = response.data[0];
                    $('#idCategoryName').val(data.name);
                    $('#idEnabled').prop('checked', data.enabled == 1);
                },
            });
        }
    }
    $('#modalCategories').modal('show');
});

$('#categorySave').click(function (e) {
    e.preventDefault();
    var validation = true;
    var data = $('#modalCategories input').serializeObject();
    data.id = $('#modalCategories').data('id');
    data.action = $('#modalCategories').data('action') || 'C';
    data.part = $('#modalCategories').data('part');

    $('#modalCategories input').removeClass('is-invalid');
    if (data.elCategoryName == '') {
        validation = false;
        $('#idCategoryName').addClass('is-invalid');
    }

    if (validation) {
        $.ajax({
            type: 'post',
            url: ROUTES_CTRL,
            data,
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteUpdateSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#modalCategories').modal('hide');
                } else {
                    alertNotify({
                        type: 'warning',
                        text: labels.nteError,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
                loadCategories();
            },
        });
    }
});

$(document).on('click', '[id^=delete_]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    alertNotify({
        type: 'warning',
        text: labels.nteDeleteWarn,
        icon: 'fas fa-exclamation',
        buttons: [
            {
                type: 'warning',
                text: labels.btnYes,
                icon: 'fas fa-check',
                callback: function () {
                    deleteCategory(id);
                },
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ],
    });
});

function deleteCategory(id) {
    $.ajax({
        type: 'post',
        url: ROUTES_CTRL,
        data: {
            action: 'D',
            part: 'C',
            id,
        },
        dataType: 'json',
        success: function (response) {
            var data = response;
            if (data.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteDeleteSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                loadCategories();
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteDeleteError,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
        },
    });
}
function UpdateElements(ArrAfter) {
    $.ajax({
        type: 'post',
        url: ROUTES_CTRL,
        data: {
            action: 'U',
            part: 'E',
            ArrAfter,
        },
        dataType: 'json',
        success: function (response) {
            var data = response;
            if (data.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteUpdateSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                loadCategories();
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteUpdateError,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            }
        },
    });
}

function loadCategories(exist = true) {
    $.ajax({
        type: 'post',
        url: ROUTES_CTRL,
        data: {
            action: 'R',
            part: 'C',
        },
        dataType: 'json',
        success: function (response) {
            if (response.result) {
                if (exist) {
                    $('#categories').nestable('destroy');
                    $('#categories').html('');
                }
                $('#categories').nestable({
                    group: 1,
                    json: response.data,
                    contentCallback: function (item) {
                        var span = document.createElement('span');
                        span.setAttribute('class', 'ms-2 d-flex flex-fill');
                        span.appendChild(document.createTextNode(item.content));
                        var content = span.outerHTML;

                        var span = document.createElement('span');
                        span.setAttribute('class', 'd-flex dd-nodrag');

                        var button = document.createElement('button');
                        button.setAttribute('class', 'btn btn-sm p-0 me-1');
                        button.setAttribute('id', 'add');
                        button.setAttribute('data-id', +item.id);
                        button.setAttribute('data-action', 'C');
                        var icon = document.createElement('i');
                        icon.setAttribute('class', 'fal fa-plus-circle');
                        button.appendChild(icon);
                        span.appendChild(button);

                        var button = document.createElement('button');
                        button.setAttribute('class', 'btn btn-sm p-0 me-1');
                        button.setAttribute('id', 'edit_' + item.id);
                        button.setAttribute('data-id', +item.id);
                        button.setAttribute('data-action', 'E');
                        var icon = document.createElement('i');
                        icon.setAttribute('class', 'fal fa-pencil');
                        button.appendChild(icon);
                        span.appendChild(button);

                        var button = document.createElement('button');
                        button.setAttribute('class', 'btn btn-sm p-0 me-1');
                        button.setAttribute('id', 'delete_' + item.id);
                        button.setAttribute('data-id', +item.id);
                        var icon = document.createElement('i');
                        icon.setAttribute('class', 'fal fa-trash-alt');
                        button.appendChild(icon);
                        span.appendChild(button);

                        content += span.outerHTML;
                        return content;
                    },
                    onDragStart: function (l, e) {
                        // l is the main container
                        // e is the element that was moved
                        //console.log(l, e, $('#categories').nestable('toArray'));
                        ArrBefore = $('#categories').nestable('toArray');
                    },
                    callback: function (l, e) {
                        // l is the main container
                        // e is the element that was moved
                        //console.log(l, e, $('#categories').nestable('toArray'));
                        ArrAfter = $('#categories').nestable('toArray');
                        if (JSON.stringify(ArrBefore) !== JSON.stringify(ArrAfter)) {
                            UpdateElements(ArrAfter);
                        } else {
                            console.log('No changes detected');
                        }
                    },
                });
                $('.dd-content').addClass('d-flex');
            }
        },
    });
}

function clearCategoryForm() {
    $('#modalCategories h1').text(labels.lblNewCategory);
    $('#modalCategories').data('id', 0);
    $('#idCategoryName').val('');
    $('#idEnabled').prop('checked', true);
    $('#modalCategories input').removeClass('is-invalid');
    $('#modalCategories').data('part', 'C');
}
