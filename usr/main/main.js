var DateTime = luxon.DateTime;
var MAIN_CTRL = '/' + chrLocale + '/controller/main';
var timer = null;

var tableDeliveries = jQuery('#tableVisitors').DataTable({
    responsive: true,
    fixedHeader: true,
    paging: false,
    scrollY: 'calc(100vh - 410px)',
    language: {
        'url': TABLELANG
    },
    order: [[0, 'desc']],
    //ordering: false,
    searchPanes: {
        initCollapsed: true
    },
    columnDefs: [
        /*{
            searchPanes: {
                show: false
            },
            targets: [0, 1, 2, 3, 4, 5, 6],
        }*/
    ],
    'ajax': {
        'url': MAIN_CTRL,
        'type': 'POST',
        'dataSrc': 'data',
        'data': function (d) {
            d.action = 'R',
                d.part = 'D'
        }
    },
    dom: '<"row"<"#ctrlCustom.col-sm-12 col-md-6"><"col-sm-12 col-md-6"f>>ti',
    columns: [
        /*{
            title: labels.tblChecked,
            data: 'checked',
            render: {
                display: function (data) {
                    let offset = - (new Date().getTimezoneOffset() / 60);
                    return DateTime.fromSQL(data).setLocale(chrLocale).plus({ hours: offset }).toLocaleString(DateTime.DATETIME_SHORT); //moment(data).format('L');
                },
                sort: function (data) {
                    return data;
                }
            }

        }, */ 
        {
            title: labels.tblName,
            data: 'event_name'
        }, // 1
        /*{
            title: labels.tblPost,
            data: 'post'
        }, // 1*/
    ],
    drawCallback: function () {
        var state = ''
        if (timer !== null) {
            state = ' checked';
        }
        var html = '<div class="form-check form-switch">' +
            '<input class="form-check-input" type="checkbox" role="switch" id="deSwitch"' + state + '>' +
            '<label class="form-check-label" for="deSwitch">' + labels.lblAutoRefresh + '</label>' +
            '</div>'
        $('#ctrlCustom').html(html);
    }
});

$(document).on('change', '#deSwitch', function (e) {
    if ($(this).is(':checked')) {
        if (timer !== null) return;
        timer = setInterval(function () {
            tableDeliveries.ajax.reload(false);
        }, 10000);
    } else {
        clearInterval(timer);
        timer = null;
    }
});