<?php
require_once __ROOT__ . '/pub/sync/models/modmailcron.php';
$json = [];

// Instancia del modelo
$objRegist = new modRegistration($_MYSQLI_);

// Obtiene participantes pendientes
$participants = $objRegist->selectPendingParticipants()['data'] ?? [];

if (!empty($participants)) {
    foreach ($participants as $participant) {

        // Enviar correo
        $result = $objRegist->sendEmail($participant);

        if ($result['result'] ?? false) {
            echo "✅ Correo enviado a: " . $participant['email'] . PHP_EOL;

            // Setear el ID actual
            $objRegist->setId($participant['participant_id']);

            // Marcar como enviado
            $resultupdate = $objRegist->markAsSent();

            if ($resultupdate['result'] ?? false) {
                echo "   -> Participante #" . $participant['participant_id'] . " marcado como enviado." . PHP_EOL;
            } else {
                echo "   ⚠️ Error al actualizar participante #" . $participant['participant_id'] . PHP_EOL;
            }
        } else {
            echo "❌ Error al enviar correo a: " . $participant['email'] . PHP_EOL;
        }
    }
} else {
    echo "No hay participantes pendientes." . PHP_EOL;
}
