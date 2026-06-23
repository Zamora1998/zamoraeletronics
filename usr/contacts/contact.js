function searchContact(objParams = {}) {
    /**
     * Opens the contact search / lookup form.
     *
     * @param {addNew: int} optional (default false)
     * @param {callBack: function} optional callback function to call after select.
     * @param {contactId: int} optional (default 0 means none)
     * @param {contactIds: array} optional (default [0])
     * @param {mode: text} optional view, edit or select (default view).
     * @param {multiple: int} optional (default false)
     * @return {modal} view in modal.
     */

    if (!('addNew' in objParams)) {
        objParams.addNew = 0;
    }
    if ('callBack' in objParams) {
        $('#ctctDialog').data('callback', objParams.callBack);
    }
    if (!('contactId' in objParams)) {
        objParams.contactId = 0;
    }
    if (!('contactIds' in objParams)) {
        objParams.contactIds = [0];
    }
    if (!('mode' in objParams)) {
        objParams.mode = 'view';
    }
    if (!('multiple' in objParams)) {
        objParams.multiple = 0;
    }

    $.ajax({
        type: "post",
        url: "/contacts/Search",
        data: objParams,
        dataType: "json",
        success: function (response) {
            data = response.data;
            $('#ctctHeader').html(data.header);
            $('#ctctBody').html(data.body).removeClass('ctctedit').addClass('ctctshow');
            $('#ctctFooter').html(data.footer);
            $('#ctctDialog').data('contactid', objParams.contactId);
            $('#ctctDialog').data('contactids', objParams.contactIds);
            $('#ctctDialog').data('mode', objParams.mode);
            $('#ctctDialog').data('multiple', objParams.multiple);
            $('#ctctDialog').data('reference', JSON.stringify(objParams));
            $('#ctctDialog').modal('show');
            $('.profileContact').initial();
            if (objParams.multiple) {
                $('#ctctList').selectable();
            }
        }
    });
}

function viewContact(objParams) {
    /**
     * Opens the contact view form.
     *
     * @param {callBack: function} optional callback function to call after select.
     * @param {contactId: int} optional (default 0 means none)
     * @param {contactIds: array} optional (default [0])
     * @param {select: int} optional select specific phones and or emails(default false)
     * @return {modal} view in modal.
     */

    $('#ctctDialog').data('phoneids', []);
    $('#ctctDialog').data('emailids', []);
    $('#ctctDialog').data('callback', '');
    $('#ctctDialog').data('reference', JSON.stringify(objParams));

    if ('contactId' in objParams) {
        if (!('select' in objParams)) {
            objParams.select = 0;
        }
        if ('phoneIds' in objParams) {
            $('#ctctDialog').data('phoneids', objParams.phoneIds);
        }

        if ('phoneIds' in objParams) {
            $('#ctctDialog').data('emailids', objParams.emailIds);
        }

        if ('callBack' in objParams) {
            $('#ctctDialog').data('callback', objParams.callBack);
        }

        $.ajax({
            type: "post",
            url: "/contacts/View",
            data: objParams,
            dataType: "json",
            success: function (response) {
                data = response.data;
                if (data.contactId) {
                    $('#ctctHeader').html(data.header);
                    $('#ctctBody').html(data.body).removeClass('ctctedit').addClass('ctctshow');
                    $('#ctctFooter').html(data.footer);
                    $('#ctctDialog').data('contactid', data.contactId);
                    $('#ctctDialog').modal('show');
                    $('.profileContact').initial();
                    var phoneIds = $('#ctctDialog').data('phoneids');
                    var emailIds = $('#ctctDialog').data('emailids');
                    phoneIds.forEach(element => {
                        $('#phone_' + element).prop("checked", true);
                    });
                    emailIds.forEach(element => {
                        $('#email_' + element).prop("checked", true);
                    });
                } else {
                    $('#ctctDialog').data('phoneids', []);
                    $('#ctctDialog').data('emailids', []);
                }
            }
        });
    }
}

function editContact(objParams) {
    /**
     * Opens the contact edit form.
     *
     * @param {callBack: function} optional callback function to call after select.
     * @param {contactId: int} optional (default 0 means new)
     * @return {modal} view in modal.
     */

    if (!('contactId' in objParams)) {
        objParams.contactId = 0;
    }
    if ('callBack' in objParams) {
        $('#ctctDialog').data('callback', objParams.callBack);
    } else {
        $('#ctctDialog').data('callback', '');
    }

    $.ajax({
        type: "post",
        url: "/contacts/EditForm",
        data: objParams,
        dataType: "json",
        success: function (response) {
            data = response.data;
            if (data) {
                $('#ctctHeader').html(data.header);
                $('#ctctBody').html(data.body).removeClass('ctctshow').addClass('ctctedit');
                $('#ctctFooter').html(data.footer);
                $('#ctctDialog').data('contactid', data.contactId);
                $('#ctctDialog').modal('show');
                $('.profileContact').initial();
            }
        }
    });
}

/* Global events */
$('#ctctDialog').on('hidden.bs.modal', function (e) {
    $.each($('#ctctDialog').data(), function (i) {
        if (i !== 'keyboard' && i !== 'backdrop') {
            $('#ctctDialog').removeData(i);
            $('#ctctDialog').removeAttr("data-" + i);
        }
    });
    $('#ctctHeader').html('');
    $('#ctctBody').html('');
});

/* Contact search / lookup events */
$('#ctctDialog').on('keyup', '#ctctsearch', function () {
    var term = $(this).val().toLowerCase();

    if (term.length > 3) {
        terms = term.split(' ');
        $('li[id^="ctctlistitem_"]').addClass('d-none').each(function () {
            if (matchWords($(this).data('search').split('|'), terms)) {
                $(this).removeClass('d-none')
            }
        });
    } else {
        $('li[id^="ctctlistitem_"]').removeClass('d-none')
    }
});

$('#ctctDialog').on('click', '#ctctsearchclear', function () {
    $('#ctctsearch').val('');
    $('#ctctsearch').trigger('keyup');
});

$('#ctctDialog').on('click', '#ctctcontactadd', function () {
    var callBack = $('#ctctDialog').data('callback');
    if (!callBack) { callBack = ''; }
    $('#ctctDialog').modal('hide');
    setTimeout(function () {
        editContact(
            {
                contactId: 0,
                callBack
            }
        );
    }, 500);
});

$('#ctctDialog').on('click', '#ctctList li', function () {
    if (!$('#ctctDialog').data('multiple')) {
        var contactId = $(this).data('id');
        if ($('#ctctDialog').data('mode') == 'view') {
            $('#ctctDialog').modal('hide');
            setTimeout(function () {
                viewContact({ contactId });
            }, 500);
        } else if ($('#ctctDialog').data('mode') == 'edit') {
            $('#ctctDialog').modal('hide');
            setTimeout(function () {
                editContact({ contactId });
            }, 500);
        } else if ($('#ctctDialog').data('mode') == 'select') {
            var callBack = $('#ctctDialog').data('callback');
            if (callBack != '') {
                var params = {
                    contactId,
                    reference: $('#ctctDialog').data('reference')
                }
                window[callBack](params);
            }
            $('#ctctDialog').modal('hide');
        }
    }
})

$('#ctctDialog').on('click', '#ctctSelectSearch', function () {
    var callBack = $('#ctctDialog').data('callback');

    if (callBack != '') {
        var params = {
            contactIds: $('#ctctDialog').data('contactids'),
            reference: $('#ctctDialog').data('reference')
        }
        window[callBack](params);
    }
    $('#ctctDialog').modal('hide');
});

$('#ctctDialog').on('selectableselected', '#ctctList', function (event, ui) {
    var selected = [];
    $('ul#ctctList li.ui-selected').each(function () {
        selected.push($(this).data('id'));
    })
    $('#ctctDialog').data('contactids', selected);
});

$('#ctctDialog').on('selectableunselected', '#ctctList', function (event, ui) {
    var selected = [];
    $('ul#ctctList li.ui-selected').each(function () {
        selected.push($(this).data('id'));
    })
    $('#ctctDialog').data('contactids', selected);
});

/* Contact viewer events */
$('#ctctDialog').on('change', '[id^=phone_]', function () {
    var phoneIds = $('#ctctDialog').data('phoneids');

    if ($(this).prop("checked")) {
        phoneIds.push($(this).data('id'));
    } else {
        phoneIds = phoneIds.filter((num) => num != $(this).data('id'));
    }
    $('#ctctDialog').data('phoneids', phoneIds);
});

$('#ctctDialog').on('change', '[id^=email_]', function () {
    var emailIds = $('#ctctDialog').data('emailids');

    if ($(this).prop("checked")) {
        emailIds.push($(this).data('id'));
    } else {
        emailIds = emailIds.filter((num) => num != $(this).data('id'));
    }
    $('#ctctDialog').data('emailids', emailIds);
});

$('#ctctDialog').on('click', '#ctctSelect', function () {
    var callBack = $('#ctctDialog').data('callback');

    if (callBack != '') {
        var params = {
            contactId: $('#ctctDialog').data('contactid'),
            phoneIds: $('#ctctDialog').data('phoneids'),
            emailIds: $('#ctctDialog').data('emailids'),
            reference: $('#ctctDialog').data('reference')
        }
        window[callBack](params);
    }
    $('#ctctDialog').modal('hide');
});

/* Contact editor events */
//Person
$('#ctctDialog').on('change', '#ctctfirstname,#ctctlastname', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var firstname = $('#ctctfirstname').val().trim();
    var lastname = $('#ctctlastname').val().trim();

    if (firstname !== "" || lastname !== "") {
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'P',
                contactId,
                firstname,
                lastname
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
            }
        });
    }
});

//company
$('#ctctDialog').on('change', '#ctctcompanyname', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var companyname = $('#ctctcompanyname').val().trim();

    if (companyname !== "") {
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'C',
                contactId,
                companyname
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
            }
        });
    }
});

//Phone
$('#ctctDialog').on('change', '[id^=ctctphonetype_],[id^=ctctphonenumber_],[id^=ctctdefphone_]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var phoneId = $(this).data('id');
    var listItem = $(this).parents('li');
    var contacttypeId = listItem.children().find('[id^=ctctphonetype_]').val();
    var number = listItem.children().find('[id^=ctctphonenumber_]').val().trim();
    var def = + listItem.children().find('[id^=ctctdefphone_]').is(':checked');

    if (contactId > 0 || number !== '') {
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'T',
                contactId,
                contacttypeId,
                phoneId,
                number,
                def
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
                listItem.data('id', data.phoneId);
                listItem.children().find('[id^=ctctphonetype_]').data('id', data.phoneId);
                listItem.children().find('[id^=ctctphonetype_]').attr('id', 'ctctphonetype_' + data.phoneId);
                listItem.children().find('[id^=ctctphonenumber_]').data('id', data.phoneId);
                listItem.children().find('[id^=ctctphonenumber_]').attr('id', 'ctctphonenumber_' + data.phoneId);
                listItem.children().find('[id^=ctctphonenumber_]').val(data.formatedPhoneNumber);
                listItem.children().find('[id^=ctctdefphone_]').data('id', data.phoneId);
                listItem.children().find('[id^=ctctdefphone_]').attr('id', 'ctctdefphone_' + data.phoneId);
                listItem.children().find('[id^=ctctdelphone_]').data('id', data.phoneId);
                listItem.children().find('[id^=ctctdelphone_]').attr('id', 'ctctdelphone_' + data.phoneId);
            }
        });
    }
});

$('#ctctDialog').on('click', '#ctcteditclose', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var callBack = $('#ctctDialog').data('callback');
    var reference = $('#ctctDialog').data('reference');
    if (callBack != '') {
        var params = {
            contactId,
            reference
        }
        window[callBack](params);
    }
    $('#ctctDialog').modal('hide');
});

$('#ctctDialog').on('click', '#ctctaddphone', function () {
    if ($('#ctctDialog').data('contactid')) {
        $('#ctctnewphone li').clone().insertBefore($('li#ctctaddphonesect'));
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.wrnContactAdd,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});

$('#ctctDialog').on('click', '[id^="ctctdelphone_"]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var phoneId = $(this).data('id');
    var listItem = $(this).parents('li');
    var number = listItem.children().find('[id^=ctctphonenumber_]').val().trim();

    if (phoneId == 0) {
        $(this).parents('li').remove();
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.lblDeleteConfirm + '<br>' + number,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes,
                    icon: 'fas fa-check',
                    callback: 'deletePhone(' + contactId + ', ' + phoneId + ');',
                },
                {
                    type: 'danger',
                    text: labels.btnNo,
                    icon: 'fas fa-times',
                },
            ]
        })
    }
});

//Email
$('#ctctDialog').on('change', '[id^=ctctemailtype_],[id^=ctctemailaddress_],[id^=ctctdefemail_]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var emailId = $(this).data('id');
    var listItem = $(this).parents('li');
    var contacttypeId = listItem.children().find('[id^=ctctemailtype_]').val();
    var email = listItem.children().find('[id^=ctctemailaddress_]').val().trim().toLowerCase();
    var def = + listItem.children().find('[id^=ctctdefemail_]').is(':checked');

    if (validateEmail(email)) {
        listItem.children().find('[id^=ctctemailaddress_]').removeClass('is-invalid')
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'E',
                contactId,
                contacttypeId,
                emailId,
                email,
                def
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
                listItem.data('id', data.emailId);
                listItem.children().find('[id^=ctctemailtype_]').data('id', data.emailId);
                listItem.children().find('[id^=ctctemailtype_]').attr('id', 'ctctemailtype_' + data.emailId);
                listItem.children().find('[id^=ctctemailaddress_]').data('id', data.emailId);
                listItem.children().find('[id^=ctctemailaddress_]').attr('id', 'ctctemailaddress_' + data.emailId);
                listItem.children().find('[id^=ctctdefemail_]').data('id', data.emailId);
                listItem.children().find('[id^=ctctdefemail_]').attr('id', 'ctctdefemail_' + data.emailId);
                listItem.children().find('[id^=ctctdelemail_]').data('id', data.emailId);
                listItem.children().find('[id^=ctctdelemail_]').attr('id', 'ctctdelemail_' + data.emailId);
            }
        });
    } else {
        listItem.children().find('[id^=ctctemailaddress_]').addClass('is-invalid')
    }
});

$('#ctctDialog').on('click', '#ctctaddemail', function () {
    if ($('#ctctDialog').data('contactid')) {
        $('#ctctnewemail li').clone().insertBefore($('li#ctctaddemailsect'));
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.wrnContactAdd,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});

$('#ctctDialog').on('click', '[id^="ctctdelemail_"]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var emailId = $(this).data('id');
    var listItem = $(this).parents('li');
    var address = listItem.children().find('[id^=ctctemailaddress_]').val().trim();

    if (emailId == 0) {
        $(this).parents('li').remove();
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.lblDeleteConfirm + '<br>' + number,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes,
                    icon: 'fas fa-check',
                    callback: 'deleteEmail(' + contactId + ', ' + emailId + ');',
                },
                {
                    type: 'danger',
                    text: labels.btnNo,
                    icon: 'fas fa-times',
                },
            ]
        })
    }
});

//Social
$('#ctctDialog').on('change', '[id^=socialmediatype_],[id^=ctctsocialname_]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var socialId = $(this).data('id');
    var listItem = $(this).parents('li');
    var socialtypeId = listItem.children().find('[id^=socialmediatype_]').val();
    var socialName = listItem.children().find('[id^=ctctsocialname_]').val().trim();

    if (socialId > 0 || socialName !== '') {
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'S',
                contactId,
                socialtypeId,
                socialId,
                socialName,
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
                listItem.data('id', data.socialId);
                listItem.children().find('[id^=socialmediatype_]').data('id', data.socialId);
                listItem.children().find('[id^=socialmediatype_]').attr('id', 'socialmediatype_' + data.socialId);
                listItem.children().find('[id^=ctctsocialname_]').data('id', data.socialId);
                listItem.children().find('[id^=ctctsocialname_]').attr('id', 'ctctsocialname_' + data.socialId);
                listItem.children().find('[id^=ctctdelsocial_]').data('id', data.socialId);
                listItem.children().find('[id^=ctctdelsocial_]').attr('id', 'ctctdelsocial_' + data.socialId);
            }
        });
    }
});

$('#ctctDialog').on('click', '#ctctaddsocial', function () {
    if ($('#ctctDialog').data('contactid')) {
        $('#ctctnewsocial li').clone().insertBefore($('li#ctctaddsocialsect'));
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.wrnContactAdd,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});

$('#ctctDialog').on('click', '[id^="ctctdelsocial_"]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var socialId = $(this).data('id');
    var listItem = $(this).parents('li');
    var name = listItem.children().find('[id^=ctctsocialname_]').val().trim();

    if (socialId == 0) {
        $(this).parents('li').remove();
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.lblDeleteConfirm + '<br>' + number,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes,
                    icon: 'fas fa-check',
                    callback: 'deleteSocial(' + contactId + ', ' + socialId + ');',
                },
                {
                    type: 'danger',
                    text: labels.btnNo,
                    icon: 'fas fa-times',
                },
            ]
        })
    }
});

//Address
$('#ctctDialog').on('change', '[id^=ctctaddresstype_],[id^=ctctaddressline1_],[id^=ctctaddressline2_],[id^=ctctaddresszip_],[id^=ctctaddresscity_],[id^=ctctaddresscountry_]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var addressId = $(this).data('id');
    var listItem = $(this).parents('li');
    var contacttypeId = listItem.children().find('[id^=ctctaddresstype_]').val();
    var addressLine1 = listItem.children().find('[id^=ctctaddressline1_]').val().trim();
    var addressLine2 = listItem.children().find('[id^=ctctaddressline2_]').val().trim();
    var addressZip = listItem.children().find('[id^=ctctaddresszip_]').val().trim();
    var addressCity = listItem.children().find('[id^=ctctaddresscity_]').val().trim();
    var addressCountry = listItem.children().find('[id^=ctctaddresscountry_]').val().trim();

    if (addressId > 0 || addressLine1 !== '' || addressLine2 !== '' || addressZip !== '' || addressCity !== '') {
        $.ajax({
            type: "post",
            url: "/contacts/Edit",
            data: {
                action: 'U',
                part: 'A',
                contactId,
                addressId,
                contacttypeId,
                addressLine1,
                addressLine2,
                addressZip,
                addressCity,
                addressCountry
            },
            dataType: "json",
            success: function (response) {
                var data = response.data;
                $('#ctctDialog').data('contactid', data.contactId);
                listItem.data('id', data.addressId);
                listItem.children().find('[id^=ctctaddresstype_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddresstype_]').attr('id', 'ctctaddresstype_' + data.addressId);
                listItem.children().find('[id^=ctctaddressline1_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddressline1_]').attr('id', 'ctctaddressline1_' + data.addressId);
                listItem.children().find('[id^=ctctaddressline2_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddressline2_]').attr('id', 'ctctaddressline2_' + data.addressId);
                listItem.children().find('[id^=ctctaddresszip_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddresszip_]').attr('id', 'ctctaddresszip_' + data.addressId);
                listItem.children().find('[id^=ctctaddresscity_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddresscity_]').attr('id', 'ctctaddresscity_' + data.addressId);
                listItem.children().find('[id^=ctctaddresscountry_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctaddresscountry_]').attr('id', 'ctctaddresscountry_' + data.addressId);
                listItem.children().find('[id^=ctctdeladdress_]').data('id', data.addressId);
                listItem.children().find('[id^=ctctdeladdress_]').attr('id', 'ctctdeladdress_' + data.addressId);
            }
        });
    }
});

$('#ctctDialog').on('click', '#ctctaddaddress', function () {
    if ($('#ctctDialog').data('contactid')) {
        $('#ctctnewaddress li').clone().insertBefore($('li#ctctaddaddresssect'));
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.wrnContactAdd,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});

$('#ctctDialog').on('click', '[id^="ctctdeladdress_"]', function () {
    var contactId = $('#ctctDialog').data('contactid');
    var addressId = $(this).data('id');
    var listItem = $(this).parents('li');
    var name = listItem.children().find('[id^=ctctaddressline1_]').val().trim();

    if (addressId == 0) {
        $(this).parents('li').remove();
    } else {
        alertNotify({
            type: 'warning',
            text: headLabels.lblDeleteConfirm + '<br>' + number,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes,
                    icon: 'fas fa-check',
                    callback: 'deleteAddress(' + contactId + ', ' + addressId + ');',
                },
                {
                    type: 'danger',
                    text: labels.btnNo,
                    icon: 'fas fa-times',
                },
            ]
        })
    }
});

$('#ctctDialog').on('click', '#ctctdelcontact', function () {
    var contactId = $('#ctctDialog').data('contactid');
    if (contactId) {
        alertNotify({
            type: 'warning',
            text: headLabels.lblDeleteConfirm + '<br>' + number,
            icon: 'fas fa-exclamation',
            buttons: [
                {
                    type: 'warning',
                    text: labels.btnYes,
                    icon: 'fas fa-check',
                    callback: 'deleteContact(' + contactId + ');',
                },
                {
                    type: 'danger',
                    text: labels.btnNo,
                    icon: 'fas fa-times',
                },
            ]
        })
    } else {
        $('#ctctDialog').modal('hide');
    }
});

/* Model and helper functions */
function deletePhone(contactId, phoneId) {
    $.ajax({
        type: "post",
        url: "/contacts/Edit",
        data: {
            action: 'D',
            part: 'T',
            contactId,
            phoneId,
        },
        dataType: "json",
        success: function (response) {
            var data = response.data;
            if (data.result) {
                $('#ctctdelphone_' + data.phoneId).parents('li').remove();
            }
        }
    });
}

function deleteEmail(contactId, emailId) {
    $.ajax({
        type: "post",
        url: "/contacts/Edit",
        data: {
            action: 'D',
            part: 'E',
            contactId,
            emailId,
        },
        dataType: "json",
        success: function (response) {
            var data = response.data;
            if (data.result) {
                $('#ctctdelemail_' + data.emailId).parents('li').remove();
            }
        }
    });
}

function deleteSocial(contactId, socialId) {
    $.ajax({
        type: "post",
        url: "/contacts/Edit",
        data: {
            action: 'D',
            part: 'S',
            contactId,
            socialId,
        },
        dataType: "json",
        success: function (response) {
            var data = response.data;
            if (data.result) {
                $('#ctctdelsocial_' + data.socialId).parents('li').remove();
            }
        }
    });
}

function deleteAddress(contactId, addressId) {
    $.ajax({
        type: "post",
        url: "/contacts/Edit",
        data: {
            action: 'D',
            part: 'A',
            contactId,
            addressId,
        },
        dataType: "json",
        success: function (response) {
            var data = response.data;
            if (data.result) {
                $('#ctctdeladdress_' + data.addressId).parents('li').remove();
            }
        }
    });
}

function deleteContact(contactId) {
    $.ajax({
        type: "post",
        url: "/contacts/Edit",
        data: {
            action: 'D',
            part: 'C',
            contactId,
        },
        dataType: "json",
        success: function (response) {
            var contactId = $('#ctctDialog').data('contactid');
            var data = response.data;
            if (data.result) {
                var contactId = data.contactId;
                var callBack = $('#ctctDialog').data('callback');
                var reference = $('#ctctDialog').data('reference');
                if (callBack != '') {
                    var params = {
                        contactId,
                        reference
                    }
                    window[callBack](params);
                }

                $('#ctctDialog').modal('hide');
            }
        }
    });
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email));
}

function matchWords(arrSubject, arrWords) {
    var result = arrWords.filter((s) => !arrSubject.some((str) => str.includes(s)));
    return !(result.length);
}