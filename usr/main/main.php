<?php
require_once __ROOT__ . '/components/usr/usrhead.php';
$objLabel = new labels($_MYSQLI_);
$labelSels = array(
    'tblType',
    'tblLastSynch',
    'tblAge',
    'tblLastAttempt',
    'tblResult',
    'Mision',
    'Vision'
);
$labels = $objLabel->getLabels($labelSels, $chrLang);

?>
<link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/lib/datatables/DataTables-1.13.2/css/dataTables.bootstrap5.min.css" />
<!-- Evento Seleccionado Card -->

<div class="container my-5">
    <div class="row p-5 align-items-start rounded-4 border shadow-lg bg-light">

        <!-- Misión -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="d-flex align-items-start">
                <i class="bi bi-bullseye display-5 text-primary me-3 flex-shrink-0"></i>
                <div>
                    <h3 class="fw-bold mb-3 border-bottom pb-2">Misión</h3>
                    <p class="fs-5 mb-0">
                        <?= $labels['Mision'] ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Visión -->
        <div class="col-md-6">
            <div class="d-flex align-items-start">
                <i class="bi bi-eye-fill display-5 text-success me-3 flex-shrink-0"></i>
                <div>
                    <h3 class="fw-bold mb-3 border-bottom pb-2">Visión</h3>
                    <p class="fs-5 mb-0">
                        <?= $labels['Vision'] ?>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>



<?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>

<script>
    var labels = <?= json_encode($labels); ?>;
</script>
<script src="/lib/select2/select2.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/jquery.dataTables.min.js"></script>
<script src="/lib/datatables/DataTables-1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script>
</body>

</html>