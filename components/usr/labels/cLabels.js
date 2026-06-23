var CTRL_COMPLABELS = "/" + chrLocale + "/controller/cLabels";

$('#labelSaveComponent').click(function (e) {
    e.preventDefault();
    var validation = true;
    $('#laNameLabel').removeClass('is-invalid');
    var id = $('#modallabelsComponent').attr('data-id');
    var values = $('#modallabelsComponent input, #modallabelsComponent textarea').serializeObject();
    if (values.laName == '') {
        $('#laNameLabel').addClass('is-invalid');
        validation = false;
    }
    values.id = id;
    values.action = 'C';
    values.part = 'L';
    if (validation) {
        $.ajax({
            type: "POST",
            url: CTRL_COMPLABELS,
            data: values,
            dataType: "json",
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteCreateSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    $('#modallabelsComponent').modal('hide');
                } else {
                    alertNotify({
                        type: 'danger',
                        text: labels.nteCreateError,
                        icon: 'fas fa-exclamation-triangle',
                        timeout: 3000,
                    });
                }
            }
        });
    }
});

function clearFormLabels() {
    $('#modallabelsComponent').data('id', 0);
    $('#laNameLabel').val('');
    $('[id^="Compdescription_"]').each(function (index, element) {
        $(element).val('');
    });
}

$('#modallabelsComponent').on('hidden.bs.modal', function () {
    $(this).attr('data-id', '');
    label = '';
});

$('#modallabelsComponent').on('hidden.bs.modal', function () {
    $(this).attr('data-id', '');
    label = '';
    $('#h1modalLabelCom').text(labels.lblNewLabel); // Restaurar el h1 por si acaso
});
