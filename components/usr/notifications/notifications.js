//region general
var CTRL_NOTIFICATIONS = '/' + chrLocale + '/controller/notifications';
var DateTime = luxon.DateTime;

$.ajax({
    type: 'post',
    url: CTRL_NOTIFICATIONS,
    data: {
        action: 'R',
    },
    dataType: 'json',
    success: function (response) {
        document.getElementById('ocNotificationsBody').replaceChildren(generateNotificationToasts(response.data));
        $('.notNew').text(response.new);
    },
});

$('#ocNotificationsBody time.timeago')
    .each(function () {
        dt = DateTime.fromISO($(this).attr('datetime'));
        $(this).html(
            dt.toFormat('dd-MMM-yyyy') +
                ' ' +
                dt.toLocaleString({
                    hour: 'numeric',
                    minute: 'numeric',
                })
        );
    })
    .timeago();
//region events
$('#ocNotificationsBody').on('click', '[id^="notRead_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
        type: 'post',
        url: CTRL_NOTIFICATIONS,
        data: {
            action: 'U',
            part: 'R',
            id,
        },
        dataType: 'json',
        success: function (response) {
            document.getElementById('ocNotificationsBody').replaceChildren(generateNotificationToasts(response.data));
            $('.notNew').text(response.new);
        },
    });
});

$('#ocNotificationsBody').on('click', '[id^="notUnread_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
        type: 'post',
        url: CTRL_NOTIFICATIONS,
        data: {
            action: 'U',
            part: 'N',
            id,
        },
        dataType: 'json',
        success: function (response) {
            document.getElementById('ocNotificationsBody').replaceChildren(generateNotificationToasts(response.data));
            $('.notNew').text(response.new);
        },
    });
});

$('#ocNotificationsBody').on('click', '[id^="notDelete_"]', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
        type: 'post',
        url: CTRL_NOTIFICATIONS,
        data: {
            action: 'U',
            part: 'D',
            id,
        },
        dataType: 'json',
        success: function (response) {
            document.getElementById('ocNotificationsBody').replaceChildren(generateNotificationToasts(response.data));
            $('.notNew').text(response.new);
        },
    });
});
//region functions
function generateNotificationToasts(notifications) {
    const container = document.createElement('div');
    container.className = 'toast-container position-static w-100';

    notifications.forEach((notification) => {
        // Create toast wrapper
        const toast = document.createElement('div');
        toast.className = `toast toast-${notification.type || ''} show mb-1 w-100`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        // Create toast header
        const toastHeader = document.createElement('div');
        toastHeader.className = 'toast-header align-items-start';

        // Create flex-grow div
        const flexGrowDiv = document.createElement('div');
        flexGrowDiv.className = 'flex-grow-1';

        // Create title element (strong if unviewed, span if viewed)
        const titleElement = document.createElement(notification.viewed == null ? 'strong' : 'span');
        titleElement.className = 'd-block';
        titleElement.innerHTML = `<i class="rounded me-2 ${notification.icon || ''}"></i>${notification.title || ''}`;

        // Create time element
        const timeContainer = document.createElement('small');
        timeContainer.className = 'd-block text-end';
        const timeElement = document.createElement('time');
        timeElement.className = 'timeago';
        timeElement.setAttribute('datetime', notification.created_iso || '');
        timeContainer.appendChild(timeElement);

        // Add title and time to flex div
        flexGrowDiv.appendChild(titleElement);
        flexGrowDiv.appendChild(timeContainer);

        // Create delete button
        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn-close btn-icon';
        deleteButton.setAttribute('aria-label', 'View');
        deleteButton.id = `notDelete_${notification.id || '0'}`;
        deleteButton.setAttribute('data-id', notification.id || '0');
        deleteButton.innerHTML = '<i class="fal fa-trash-alt"></i>';

        // Create view/unview button based on viewed status
        const viewButton = document.createElement('button');
        viewButton.type = 'button';
        viewButton.className = 'btn-close btn-icon';
        viewButton.setAttribute('aria-label', 'View');

        if (notification.viewed == null) {
            // Unviewed - show eye icon to mark as read
            viewButton.id = `notRead_${notification.id || '0'}`;
            viewButton.setAttribute('data-id', notification.id || '0');
            viewButton.innerHTML = '<i class="fal fa-eye"></i>';
        } else {
            // Viewed - show eye-slash icon to mark as unread
            viewButton.id = `notUnread_${notification.id || '0'}`;
            viewButton.setAttribute('data-id', notification.id || '0');
            viewButton.innerHTML = '<i class="far fa-eye-slash"></i>';
        }

        // Add elements to header
        toastHeader.appendChild(flexGrowDiv);
        toastHeader.appendChild(deleteButton);
        toastHeader.appendChild(viewButton);

        // Create toast body
        const toastBody = document.createElement('div');
        toastBody.className = 'toast-body';
        toastBody.textContent = notification.message || '';

        // Add link if present
        if (notification.link != null) {
            const linkContainer = document.createElement('div');
            linkContainer.className = 'text-end mt-1';
            const link = document.createElement('a');
            link.href = notification.linkurl || '';
            link.textContent = notification.link || '';
            linkContainer.appendChild(link);
            toastBody.appendChild(linkContainer);
        }

        // Assemble toast
        toast.appendChild(toastHeader);
        toast.appendChild(toastBody);

        // Add toast to container
        container.appendChild(toast);
    });

    return container;
}

// Usage example:
// const notifications = [
//     {
//         type: 'success',
//         viewed: null,
//         icon: 'fas fa-check-circle',
//         title: 'Success!',
//         created_iso: '2024-01-15T10:30:00Z',
//         id: 1,
//         message: 'Your action was completed successfully.',
//         link: 'View Details',
//         linkurl: '/details/1'
//     },
//     {
//         type: 'warning',
//         viewed: '2024-01-15T11:00:00Z',
//         icon: 'fas fa-exclamation-triangle',
//         title: 'Warning',
//         created_iso: '2024-01-15T09:15:00Z',
//         id: 2,
//         message: 'Please review your settings.',
//         link: null,
//         linkurl: null
//     }
// ];
//
// const toastContainer = generateNotificationToasts(notifications);
// document.body.appendChild(toastContainer);
