var CTRL_GENERATOR = "/" + chrLocale + "/controller/generator";
var moduleNameInput;
var basePathSelect;
var customPathInput;
var customPathGroup;
var structurePreview;
var resultSection;

$(document).ready(function () {

    moduleNameInput = $("#moduleName");
    basePathSelect = $("#basePath");
    customPathInput = $("#customPath");
    customPathGroup = $("#customPathGroup");
    structurePreview = $("#structurePreview");
    resultSection = $("#resultSection");

    $("#generatorForm").on("submit", handleSubmit);
    $("#basePath").on("change", toggleCustomPath);
    $("#resetBtn").on("click", resetForm);
    $("#closeResult").on("click", closeResult);

    $("#moduleName, #basePath, #customPath").on("input change", updatePreview);

    $("input[type='checkbox']").on("change", updatePreview);

    updatePreview();
});

function toggleCustomPath() {
    if (basePathSelect.val() === "custom") {
        customPathGroup.show();
        customPathInput.prop("required", true);
    } else {
        customPathGroup.hide();
        customPathInput.prop("required", false);
    }
}

function updatePreview() {
    var moduleName = moduleNameInput.val().trim();
    var basePath = basePathSelect.val();
    var options = getSelectedOptions();

    if (!moduleName || !basePath) {
        structurePreview.html(`
            <div class="empty-state">
                <i class="fas fa-info-circle"></i>
                <p>` + labels.lblRequiredFields + `</p>
            </div>
        `);
        return;
    }

    if (basePath === "custom") {
        basePath = customPathInput.val().trim();
        if (!basePath) {
            structurePreview.html(`
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>`+ labels.lblCustomRoute +` </p>
                </div>
            `);
            return;
        }
    }

    var fullPath = basePath === "." ? moduleName : basePath + "/" + moduleName;
    var tree = `<div class="tree-folder">${fullPath}/</div>`;

    if (options.createView) {
        tree += `
            <div class="tree-folder" style="margin-left:3rem;">view/</div>
            <div class="tree-file" style="margin-left:4.5rem;">${moduleName}.php</div>
        `;
    }

    if (options.createModel) {
        var modeName = "mod" + capitalize(moduleName) + ".php";

        tree += `
            <div class="tree-folder" style="margin-left:3rem;">model/</div>
            <div class="tree-file" style="margin-left:4.5rem;">${modeName}</div>
        `;
    }

    if (options.createController) {
        var ctrlName = "ctrl" + capitalize(moduleName) + ".php";
        tree += `
            <div class="tree-folder" style="margin-left:3rem;">controller/</div>
            <div class="tree-file" style="margin-left:4.5rem;">${ctrlName}</div>
        `;
    }

    if (options.createAssets) {
        tree += `
            <div class="tree-folder" style="margin-left:3rem;">assets/</div>
            <div class="tree-folder" style="margin-left:4.5rem;">css/</div>
            <div class="tree-file" style="margin-left:6rem;">${moduleName}.css</div>
            <div class="tree-folder" style="margin-left:4.5rem;">js/</div>
            <div class="tree-file" style="margin-left:6rem;">${moduleName}.js</div>
        `;
    }

    structurePreview.html(tree);
}

function getSelectedOptions() {
    return {
        createView: $("input[name='createView']").prop("checked"),
        createModel: $("input[name='createModel']").prop("checked"),
        createController: $("input[name='createController']").prop("checked"),
        createAssets: $("input[name='createAssets']").prop("checked")
    };
}

function handleSubmit(e) {
    e.preventDefault();

    var moduleName = moduleNameInput.val().trim();
    var basePath = basePathSelect.val();

    if (basePath === "custom") {
        basePath = customPathInput.val().trim();

        // Solo letras, slash y guion bajo
        var rutaValida = /^[a-zA-Z\/_]+$/.test(basePath);

        if (!rutaValida) {
            // Mostrar mensaje en el invalid-feedback
            $("#customPath").addClass("is-invalid").removeClass("is-valid");
            $("#customPath").siblings(".invalid-feedback").text(
                labels.lblCustomAlert
            );
            return;
        } else {
            $("#customPath").removeClass("is-invalid").addClass("is-valid");
        }
    }


    var options = getSelectedOptions();

    var isValid = true;

    if (!moduleName) {
        $("#moduleName").addClass('is-invalid').removeClass('is-valid');
        isValid = false;
    } else {
        $("#moduleName").removeClass('is-invalid').addClass('is-valid');
    }

    // Validar basePath
    if (!basePath) {
        $("#basePath").addClass('is-invalid').removeClass('is-valid');
        isValid = false;
    } else {
        $("#basePath").removeClass('is-invalid').addClass('is-valid');
    }

    if (!isValid) return;

    
    var btn = $("#generateBtn");
    var originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Generando...').prop("disabled", true);

    $.ajax({
            type: "POST",
            url: CTRL_GENERATOR,
            dataType: "json",
            data: {
                action: "C",
                part: "G",
                moduleName: moduleName,
                basePath: basePath,
                options: JSON.stringify(options),
        },
        success: function (result) {
            if (result || result === false) {
                showResult(result);
            }
            resetForm();
        },
        
        complete: function () {
            btn.html(originalText).prop("disabled", false);
        }
    });
}

$(document).ready(function () {
    validateField("#moduleName", /[^a-zA-Z_\/]/g);
    validateField("#customPath", /[^a-zA-Z_\/]/g);

    $("#basePath").on("change input", function () {
        if ($(this).val().trim() !== "") {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else {
            $(this).removeClass("is-valid").addClass("is-invalid");
        }
    });
});

const validateField = (selector, regex) => {
    $(selector).on("input", function () {
        const value = $(this).val().replace(regex, "");
        $(this).val(value);

        if (value.trim() === "") {
            $(this).addClass("is-invalid").removeClass("is-valid");
        } else {
            $(this).removeClass("is-invalid").addClass("is-valid");
        }
    });
};

function showResult(result) {

    var allSuccess =
        result.data?.files &&
        result.restview?.result === true &&
        result.restctrl?.result === true &&
        result.result === true;

    // Cambiar encabezado del modal
    if (allSuccess) {
        $("#modalHeader").removeClass().addClass("modal-header bg-success text-white");
        $("#modalIcon").removeClass().addClass("fas fa-check-circle");
        $("#modalTitle").text(labels.lblSuccessfullOperation);
    } else {
        $("#modalHeader").removeClass().addClass("modal-header bg-warning text-dark");
        $("#modalIcon").removeClass().addClass("fas fa-exclamation-triangle");
        $("#modalTitle").text(labels.lblWarning);
    }

    //$("#resultMessage").text(result.data?.message || "Sin mensaje");

    var list = $("#resultFiles");
    list.empty();
    if (result.data?.files) {
        result.data.files.forEach(f => {
            list.append('<li class="list-group-item"><code>' + f + '</code></li>');
        });
    }

    var access = $("#accessStatus");
    access.empty();

    if (result.restview?.result === true) {
        access.append('<li class="list-group-item text-success"><b>' + labels.lblView + ':</b> creada correctamente</li>');
    } else {
        access.append('<li class="list-group-item text-danger"><b>' + labels.lblView + ':</b> error → ' + (result.restview?.error || 'Desconocido') + '</li>');
    }

    if (result.restctrl?.result === true) {
        access.append('<li class="list-group-item text-success"><b>' + labels.lblController + ':</b> creado correctamente</li>');
    } else {
        access.append('<li class="list-group-item text-danger"><b>' + labels.lblController + ':</b> error → ' + (result.restctrl?.error || 'Desconocido') + '</li>');
    }

    $("#resultModal").modal("show");

    setTimeout(function () {
        $("#resultModal").modal("hide");
    }, 5000);
}

function resetForm() {
    $("#generatorForm")[0].reset();
    customPathGroup.hide();
    customPathInput.prop("required", false);
        $("#generatorForm input, #generatorForm select").removeClass("is-valid is-invalid");
    updatePreview();
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
