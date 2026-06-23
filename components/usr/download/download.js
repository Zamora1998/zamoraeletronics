const modalElement = document.getElementById('modaldownload');
const icono = document.getElementById('icondownload');
const mensaje = document.getElementById('messagedownload');

const modalBootstrap = new bootstrap.Modal(modalElement, {
    backdrop: true,
    keyboard: false
});

const DownloadConfig = {
    fadeOutTime: 400,
    successDuration: 2000,
    elements: {
        modal: modalBootstrap,
        icon: icono,
        message: mensaje
    },
    labelsDownload: labelsDownload
};

function resetDownloadModal() {
    icono.className = 'spinner-download';
    icono.innerHTML = '';
    mensaje.innerText = labelsDownload.lblDownloading;
}

function hideDownloadModal() {
    modalBootstrap.hide();
    setTimeout(() => {
        resetDownloadModal();
        // Eliminar clase especial del backdrop para evitar acumulación
        document.querySelectorAll('.modal-backdrop.download-backdrop')
            .forEach(el => el.classList.remove('download-backdrop'));
    }, DownloadConfig.fadeOutTime);
}

const showDownloadModal = function () {
    icono.className = 'spinner-download';
    icono.innerHTML = '';
    mensaje.innerText = labelsDownload.lblGeneratedownload;
    // Detectar si hay otro modal abierto (excepto el de descarga)
    const otherModalsOpen = Array.from(document.querySelectorAll('.modal.show'))
        .filter(modal => modal !== modalElement);

    const hasOtherModal = otherModalsOpen.length > 0;

    if (hasOtherModal) {
        modalElement.classList.add('modal-overlay-top');
    } else {
        modalElement.classList.remove('modal-overlay-top');
    }
    modalBootstrap.show();
    // Solo añadir clase especial al backdrop si hay otro modal abierto
    setTimeout(() => {
        if (!hasOtherModal) return;

        const backdrops = document.querySelectorAll('.modal-backdrop');
        const latestBackdrop = backdrops[backdrops.length - 1];
        if (latestBackdrop) {
            latestBackdrop.classList.add('download-backdrop');
        }
    }, 10);
};

const downloadSuccess = function () {
    icono.className = '';
    icono.innerHTML = `
        <svg class="check-mark-download" viewBox="0 0 24 24">
            <path d="M4 12l6 6L20 6" />
        </svg>`;
    mensaje.innerText = labelsDownload.lblDownloadSuccess;

    setTimeout(() => {
        hideDownloadModal();
    }, DownloadConfig.successDuration);
};

const downloadError = function () {
    icono.className = '';
    icono.innerHTML = '';
    mensaje.innerText = labelsDownload.lblDownloadError;

    setTimeout(() => {
        hideDownloadModal();
    }, DownloadConfig.successDuration);
};

// Exponer funciones en un espacio de nombres limpio
window.DownloadModal = {
    show: showDownloadModal,
    success: downloadSuccess,
    error: downloadError
};
