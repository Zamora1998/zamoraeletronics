
const successSound = new Audio("/assets/images/sounds/success.mp3");
const errorSound   = new Audio("/assets/images/sounds/error.mp3");

successSound.preload = "auto";
errorSound.preload   = "auto";

successSound.load();
errorSound.load();


function playSuccess() {
    if ("vibrate" in navigator) navigator.vibrate(150);
    successSound.currentTime = 0;
    successSound.play().catch(err => {
        console.warn("Error al reproducir success:", err);
    });
}

function playError() {
    if ("vibrate" in navigator) navigator.vibrate([300, 100, 300]);
    errorSound.currentTime = 0;
    errorSound.play().catch(err => {
        console.warn("Error al reproducir error:", err);
    });
}

let clearTimer = null;

function clearNfcResult(delay = 1000) {
    if (clearTimer) clearTimeout(clearTimer);

    clearTimer = setTimeout(() => {
        $("#nfcResult").text("📡 Esperando un chip NFC...");
    }, delay);
}

$("#startNFC").on("click", async function () {

    [successSound, errorSound].forEach(sound => {
        sound.volume = 0;
        sound.play().then(() => {
            sound.pause();
            sound.currentTime = 0;
            sound.volume = 1;
        }).catch(err => {
            console.warn("No se pudo desbloquear audio:", err);
        });
    });

    let uuid = $("#main").data("uuid");
    console.log("UUID leído:", uuid);

    if (!uuid) {
        $("#nfcResult").text("❌ UUID no encontrado");
        playError();
        clearNfcResult();
        return;
    }

    if (!("NDEFReader" in window)) {
        $("#nfcResult").text("❌ NFC no soportado");
        playError();
        clearNfcResult();
        return;
    }

    try {
        const ndef = new NDEFReader();
        await ndef.scan();

        $("#nfcResult").text("📡 Esperando un chip NFC...");

        ndef.onreading = event => {
            const decoder = new TextDecoder();
            let cedula = "";

            event.message.records.forEach(record => {
                cedula += decoder.decode(record.data) + " ";
            });

            cedula = cedula.trim();

            let match = cedula.match(/\{(\d+)\}/);
            if (match) cedula = match[1];

            $.ajax({
                url: "/controller/ctrlPostsEntries",
                method: "POST",
                dataType: "json",
                data: {
                    uuid: uuid,
                    cedula: cedula
                },
                success: function (response) {

                    if (response) {
                        $("#nfcResult").text("✅");
                        playSuccess();
                    } else {
                        $("#nfcResult").text("❌");
                        playError();
                    }

                    clearNfcResult();
                },
                error: function (xhr) {
                    console.error("Error AJAX:", xhr.responseText);
                    $("#nfcResult").text("❌");
                    playError();
                    clearNfcResult();
                }
            });
        };

    } catch (error) {
        console.error(error);
        $("#nfcResult").text("❌ Error NFC");
        playError();
        clearNfcResult();
    }
});
