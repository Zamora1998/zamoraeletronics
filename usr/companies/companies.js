var COMPANIES_CTRL = "/" + chrLocale + "/controller/companies";
var columns = [];
var TableCompanies;
var DateTime = luxon.DateTime;
let input;
let tagsContainer;

$(document).ready(function () {
    TableCompanies = $("#tableCompanies").DataTable({
        ajax: {
            url: COMPANIES_CTRL,
            type: "POST",
            dataSrc: "data",
            data: {
                action: "R",
                part: "A",
            },
        },
        language: {
            url: TABLELANG,
        },
        columns: [
            {
                title: labels.lblName,
                data: "Nombre",
                orderable: false,
                className: "dt-left dt-nowrap",
            }, // 0
            {
                title: labels.lblidentificationcard,
                data: "CedulaJuridica",
                orderable: false,
                className: "dt-left dt-nowrap",
            }, // 0
            {
                title: labels.lblIdateofentry,
                data: "Fecha_Ingreso",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    if (!data) {
                        return ''
                    } else {
                        return dateSqlToShort(data, chrLocale)
                    } // Usar la función existente
                },
            },
            {
                title: labels.tblPhone,
                data: "Telefono",
                orderable: false,
                className: "dt-left dt-nowrap",
                render: function (data, type, row) {
                    data = data.toString();
                    if (data && /^\d{8}$/.test(data)) {
                        return data.slice(0, 4) + '-' + data.slice(4);
                    }
                    return data;
                }
            },
            {
                title: labels.lblEmail,
                data: "Correo",
                orderable: false,
                className: "dt-left dt-nowrap",
            }, // 0
            {
                title: 'Imagen',
                data: "image",
                orderable: false,
                className: "dt-center dt-nowrap",
                render: function (data, type, row) {
                    if (data) {
                        return '<img src="' + data + '" style="max-height: 40px; border-radius: 4px;">';
                    }
                    return '';
                }
            }, // 0
            {
                title: labels.lblEnabled + '- ' + labels.navCompanies,
                data: "Estado",
                orderable: true,
                className: "dt-center dt-nowrap",
                render: function (data, type, row) {
                    return cellRender({
                        type: "check",
                        data,
                    });
                }
            }, // 2
            {
                title: labels.tblActions,
                data: "id",
                className: "dt-right",
                render: function (data, type, row) {
                    return cellRender({
                        type: "dropdown",
                        data,
                        text: labels.tblActions,
                        dataTags: {
                            id: data,
                        },
                        listItems: [
                            {
                                id: "CompanyEdit_" + data,
                                text: labels.btnEdit,
                                icon: "far fa-edit",
                                dataTags: {
                                    id: data,
                                },
                            },
                            {
                                id: "CompanyDelete_" + data,
                                text: labels.btnDelete,
                                icon: "far fa-trash-alt",
                                dataTags: {
                                    id: data
                                },
                            },
                        ],
                    });
                },
            }
        ],
        dom: '<"row"<"#ctrlConfCustom.col-6"><"col-6"f>>ti', //"W<'clear'>lfrtip",
        responsive: true,
        fixedHeader: true,
        paging: false,
        scrollX: false,
        scrollY: "calc(100vh - 355px)",
        drawCallback: function () {
            var html =
                '<button id="CompanyCreate" type="button" class="btn btn-sm btn-success me-1" data-id="0">' +
                labels.btnNew +
                "</button>";
            $("#ctrlConfCustom").html(html);
            // Nombre
            if (!document.getElementById('idInputNombre')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'text';
                input.id = 'idInputNombre';
                input.name = 'nombre';
                input.placeholder = labels.lblOrganizationName;
                input.required = true;
                input.maxLength = 100;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Cédula Jurídica
            if (!document.getElementById('idInputCedula')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'text';
                input.id = 'idInputCedula';
                input.name = 'cedulaJuridica';
                input.placeholder = labels.lblidentificationcard;
                input.required = true;
                input.maxLength = 20;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Tipo de Sociedad
            if (!document.getElementById('idInputSociedad')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'text';
                input.id = 'idInputSociedad';
                input.name = 'tipoSociedad';
                input.placeholder = labels.lblTypeCompany;
                input.required = true;
                input.maxLength = 50;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Dirección
            if (!document.getElementById('idInputDireccion')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'text';
                input.id = 'idInputDireccion';
                input.name = 'direccion';
                input.placeholder = labels.lblAddress;
                input.required = true;
                input.maxLength = 150;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Teléfono
            if (!document.getElementById('idInputTelefono')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'tel';
                input.id = 'idInputTelefono';
                input.name = 'telefono';
                input.placeholder = labels.tblPhone;
                input.required = true;
                input.pattern = '[0-9]{4}-[0-9]{4}'; // 0000-0000
                input.maxLength = 9;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Correo electrónico
            if (!document.getElementById('idInputCorreo')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'email';
                input.id = 'idInputCorreo';
                input.name = 'correo';
                input.placeholder = labels.tblEmail;
                input.required = true;
                input.maxLength = 100;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Sitio Web
            if (!document.getElementById('idInputSitioWeb')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let input = document.createElement('input');
                input.type = 'url';
                input.id = 'idInputSitioWeb';
                input.name = 'sitioWeb';
                input.placeholder = labels.socWebPage;
                input.required = true;
                input.maxLength = 100;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                wrapper.appendChild(input);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);
            }

            // Fecha de Ingreso con datepicker
            if (!document.getElementById('idInputFechaIngreso')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let dateGroup = document.createElement('div');
                dateGroup.classList.add('input-group', 'date');
                dateGroup.id = 'datepickerFechaIngreso';

                let input = document.createElement('input');
                input.type = 'text'; // Necesario para datepicker
                input.id = 'idInputFechaIngreso';
                input.readOnly = true; // Así se establece el atributo 'readonly'

                input.name = 'fechaIngreso';
                input.placeholder = labels.lblIdateofentry;
                input.required = true;
                input.classList.add('form-control', 'form-control-sm');

                let feedback = document.createElement('div');
                feedback.classList.add('invalid-feedback');
                feedback.textContent = labels.lblRequired;

                dateGroup.appendChild(input);
                wrapper.appendChild(dateGroup);
                wrapper.appendChild(feedback);
                document.getElementById('containerFields').appendChild(wrapper);

                // Inicializar datepicker
                $('#idInputFechaIngreso').datepicker({
                    language: chrLocale,
                    daysOfWeekHighlighted: "0,6",
                    clearBtn: true,
                    autoclose: true,
                    todayHighlight: true,
                    orientation: "bottom",
                    container: '#modalCompanies',
                    format: "dd-M-yyyy"
                });
            }

            // Imagen (Dropzone)
            if (!document.getElementById('dropzoneCompany')) {
                let wrapper = document.createElement('div');
                wrapper.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');

                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'idInputImage';
                hiddenInput.name = 'image';

                let dropzoneDiv = document.createElement('div');
                dropzoneDiv.id = 'dropzoneCompany';
                dropzoneDiv.classList.add('dropzone', 'border', 'rounded', 'mb-2');
                dropzoneDiv.style.minHeight = '100px';

                let label = document.createElement('label');
                label.classList.add('form-label', 'text-muted', 'small');
                label.innerHTML = 'Imagen (Opcional)';

                wrapper.appendChild(label);
                wrapper.appendChild(hiddenInput);
                wrapper.appendChild(dropzoneDiv);
                document.getElementById('containerFields').appendChild(wrapper);

                Dropzone.autoDiscover = false;
                var myDropzone = new Dropzone("#dropzoneCompany", {
                    url: COMPANIES_CTRL + "?action=U&part=I",
                    maxFiles: 1,
                    acceptedFiles: "image/*",
                    dictDefaultMessage: "Arrastra la imagen aquí o haz clic para subir",
                    addRemoveLinks: true,
                    dictRemoveFile: "Eliminar archivo",
                    dictCancelUpload: "Cancelar",

                    // Forzar que Dropzone interprete la respuesta como JSON
                    forceFallback: false,
                    headers: { 'Accept': 'application/json' },

                    success: function (file, response) {
                        try {
                            // Parsear si viene como string
                            const res = typeof response === 'string' ? JSON.parse(response) : response;

                            if (res.result && res.path) {
                                document.getElementById('idInputImage').value = res.path;
                                console.log('Imagen subida:', res.path);
                            } else {
                                alertNotify({ type: 'warning', text: 'Error al subir la imagen: ' + (res.error || '') });
                                this.removeFile(file);
                            }
                        } catch (e) {
                            // Mostrar qué devolvió el servidor para depurar
                            console.error('Respuesta inválida del servidor:', response);
                            alertNotify({ type: 'danger', text: 'Respuesta inválida del servidor al subir imagen.' });
                            this.removeFile(file);
                        }
                    },

                    error: function (file, errorMessage) {
                        console.error('Dropzone error:', errorMessage);
                        alertNotify({ type: 'danger', text: 'Error al subir: ' + (errorMessage.error || errorMessage) });
                        this.removeFile(file);
                    },

                    removedfile: function (file) {
                        if (file.previewElement?.parentNode) {
                            file.previewElement.parentNode.removeChild(file.previewElement);
                        }
                        document.getElementById('idInputImage').value = '';
                    }
                });
            }

            // Estado - Checkbox
            if (!document.getElementById('idStateConf')) {
                let calStateConf = document.createElement('div');
                calStateConf.classList.add('col-md-6', 'col-lg-9', 'mx-auto', 'mb-3');
                calStateConf.id = 'idStateConf';

                let checkboxWrapper = document.createElement('div');
                checkboxWrapper.classList.add('form-check');

                let checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.classList.add('form-check-input');
                checkbox.id = 'stateCheckbox';
                checkbox.name = 'stateCheckbox';
                checkbox.value = '1';

                let label = document.createElement('label');
                label.classList.add('form-check-label');
                label.setAttribute('for', 'stateCheckbox');
                label.textContent = labels.navCompanies + ' - ' + labels.lblEnabled;

                checkboxWrapper.appendChild(checkbox);
                checkboxWrapper.appendChild(label);
                calStateConf.appendChild(checkboxWrapper);
                document.getElementById('containerFields').appendChild(calStateConf);
            }
        }
    });
});

$(document).on('click', '[id^="CompanyEdit_"],#CompanyCreate', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    clearInputsEmpresa();
    $('#modalCompanies').data('id', id);
    $('#h1confIRFS').text((id ? labels.btnEdit + ' - ' + labels.navCompanies : labels.lblNewSettings));
    if (id) {
        $.ajax({
            type: "POST",
            url: COMPANIES_CTRL,
            data: {
                action: 'R',
                part: 'S',
                id
            },
            dataType: "json",
            success: function (response) {
                let data = response.data[0] || {};

                $('#idInputNombre').val(data.event_name || '');
                $('#idInputCedula').val(data.CedulaJuridica || '');
                $('#idInputSociedad').val(data.TipoSociedad || '');
                $('#idInputDireccion').val(data.Direccion || '');
                $('#idInputTelefono').val(data.Telefono || '');
                $('#idInputCorreo').val(data.Correo || '');
                $('#idInputSitioWeb').val(data.SitioWeb || '');
                $('#idInputImage').val(data.image || '');

                const dzElement = document.getElementById('dropzoneCompany');
                if (dzElement && dzElement.dropzone) {
                    const dz = dzElement.dropzone;
                    dz.removeAllFiles(true);
                    if (data.image) {
                        let mockFile = { name: "Imagen actual", size: 12345, accepted: true };
                        dz.emit("addedfile", mockFile);
                        dz.emit("thumbnail", mockFile, data.image);
                        dz.emit("complete", mockFile);
                        dz.files.push(mockFile);
                    }
                }

                if (data.Fecha_Ingreso) {
                    $('#idInputFechaIngreso').val(dateSqlToShort(data.Fecha_Ingreso, chrLocale));
                } else {
                    $('#idInputFechaIngreso').val('');
                }
                $('#stateCheckbox').prop('checked', data.Estado === 1);
            }
        });
    }
    $('#modalCompanies').modal('show');
});

$(document).on('click', '#saveCompany', function (e) {
    e.preventDefault();

    // Recolectar datos del formulario
    var data = $("#modalCompanies select, #modalCompanies input").serializeObject();
    data.fechaIngreso = dateShortToSql(data.fechaIngreso, chrLocale);
    data.stateCheckbox = $('#stateCheckbox').prop('checked') ? 1 : 0;

    // ID del modal
    data.id = $('#modalCompanies').data('id');
    data.part = data.id ? 'U' : 'C';
    data.action = data.id ? 'U' : 'C';

    // Validar campos requeridos
    let isValid = true;

    // Campos requeridos por ID y clave en el objeto
    const requiredFields = [
        { id: '#idInputNombre', key: 'nombre' },
        { id: '#idInputCedula', key: 'cedulaJuridica' },
        { id: '#idInputCorreo', key: 'correo' },
        { id: '#idInputFechaIngreso', key: 'fechaIngreso' },
        { id: '#idInputTelefono', key: 'telefono' }
    ];

    // Validar uno por uno
    requiredFields.forEach(field => {
        const $input = $(field.id);
        const value = data[field.key] ? data[field.key].trim() : '';

        // Limpiar errores previos
        $input.removeClass('is-invalid');
        $input.next('.invalid-feedback').remove();

        if (value === '') {
            $input.addClass('is-invalid');
            $input.after(`<div class="invalid-feedback">Este campo es obligatorio.</div>`);
            isValid = false;
        } else {
            data[field.key] = value; // Guardar valor limpio
        }
    });

    // Mostrar advertencia si hay errores
    if (!isValid) {
        alertNotify({
            type: 'warning',
            text: labels.lblInputsRequired,
            icon: 'fas fa-exclamation-triangle',
            timeout: 3000,
        });
        return;
    }

    // Enviar datos vía AJAX
    $.ajax({
        type: "POST",
        url: COMPANIES_CTRL,
        data: data,
        dataType: "json",
        success: function (response) {
            if (response.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteCreateSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
                $('#modalCompanies').modal('hide');
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteCreateError + '<br>' + response.error,
                    icon: 'fas fa-exclamation-triangle',
                    timeout: 3000,
                });
            }
            TableCompanies.ajax.reload();
        }
    });
});



$(document).on('click', '[id^=CompanyDelete_]', function (e) {
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
                    DeleteCompany(id);
                }
            },
            {
                type: 'danger',
                text: labels.btnNo,
                icon: 'fas fa-times',
            },
        ]
    });
});

function DeleteCompany(id) {
    $.ajax({
        type: "POST",
        url: COMPANIES_CTRL,
        data: {
            action: 'D',
            part: 'C',
            id: id
        },
        dataType: "json",
        success: function (response) {
            var data = response;
            if (data.result) {
                alertNotify({
                    type: 'success',
                    text: labels.nteDeleteSuccess,
                    icon: 'fas fa-check',
                    timeout: 3000,
                });
            } else {
                alertNotify({
                    type: 'warning',
                    text: labels.nteDeleteError,
                    icon: 'fas fa-times',
                    timeout: 3000,
                });
            }
            TableCompanies.ajax.reload();
        }
    });
}


function clearInputsEmpresa() {
    const fieldIds = [
        'idInputNombre',
        'idInputCedula',
        'idInputSociedad',
        'idInputFechaIngreso',
        'idInputDireccion',
        'idInputTelefono',
        'idInputCorreo',
        'idInputSitioWeb',
        'idInputImage'
    ];

    fieldIds.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.value = '';
            input.classList.remove('is-invalid');
            input.disabled = false;
        }
    });

    const checkbox = document.getElementById('stateCheckbox');
    if (checkbox) {
        checkbox.checked = true; // ✅ Siempre marcado por defecto
    }

    const dzElement = document.getElementById('dropzoneCompany');
    if (dzElement && dzElement.dropzone) {
        dzElement.dropzone.removeAllFiles(true);
    }
}

$('.input-group.date').datepicker({
    language: chrLocale,
    daysOfWeekHighlighted: "0.6",
    clearBtn: true,
    autoclose: true,
    todayHighlight: true,
    orientation: "bottom", //'auto', 'top', 'bottom', 'left', 'right',
    container: '#modalCompanies',
    format: "dd-M-yyyy" // Formato personalizado: día-mes abreviado-año
})
