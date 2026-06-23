<?
require_once __ROOT__ . '/components/pub/pubhead.php';

$objLabel = new labels($_MYSQLI_);
$labelSels = array(
  'txtHeroTitle',
  'txtHeroContent',
  'txtServices',
  'txtCollectionsCallCenter',
  'txtLegalServices',
  'txtFinancialServices',
  'txtAdministrativeServices',
  'txtServicepackages',

  'txtColletionsParagrah',
  'txtLegalServicesParagrah',
  'txtAdministrativeParagrah',
  'txtFinancialParagrah',
  'txtServiceparagraph'

);
$labels = $objLabel->getLabels($labelSels, $chrLang);
?>
<!-- home section -->
<div class="container pb-3 mt-3" id="home">
  <div class="row p-4 align-items-center rounded-3 border shadow-lg">
    <div class="col-lg-7 p-0 ps-4">
      <h2 class="fw-normal lh-1 text-eventos"><?= $labels['txtHeroTitle'] ?></h2>
      <p class="lead"><?= nl2br($labels['txtHeroContent']) ?></p>
      <!-- <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4 mb-lg-3">
                        <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold">Primary</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4">Default</button>
                    </div> -->
    </div>
    <div class="col-lg-4 offset-lg-1 p-0 overflow-hidden shadow-lg">
      <!--<img class="d-block mx-lg-auto img-fluid" src="/assets/images/hero.png" alt="" width="720">-->
    </div>
  </div>
</div>

<!-- features section -->
<div class="container pt-4" id="features">
  <div class="row p-4 align-items-center rounded-3 border shadow-lg">
    <h2 class="display-6 text-center mb-4 ps-4 pb-2 border-bottom fw-normal text-eventos"><?= $labels['txtServices'] ?></h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 mt-4 ps-4 py-0">
      <div class="col d-flex align-items-start">
        <i class="bi text-muted flex-shrink-0 me-3 mt-2 fa fa-tachometer-alt" width="1.75em" height="1.75em"> </i>
        <div>
          <h3 class="fw-bold mb-0 fs-4"><?= $labels['txtCollectionsCallCenter'] ?></h3>
          <p><?= $labels['txtColletionsParagrah'] ?></p>
        </div>
      </div>
      <div class=" col d-flex align-items-start">
        <i class="bi text-muted flex-shrink-0 me-3 mt-2 fa fa-sitemap" width="1.75em" height="1.75em"> </i>
        <div>
          <h3 class="fw-bold mb-0 fs-4"><?= $labels['txtLegalServices'] ?></h3>
          <p><?= $labels['txtLegalServicesParagrah'] ?></p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <i class="bi text-muted flex-shrink-0 me-3 mt-2 fa fa-stars" width="1.75em" height="1.75em"> </i>
        <div>
          <h3 class="fw-bold mb-0 fs-4"><?= $labels['txtFinancialServices'] ?></h3>
          <p><?= $labels['txtFinancialParagrah'] ?></p>
        </div>
      </div>
      <div class="col d-flex align-items-start">
        <i class="bi text-muted flex-shrink-0 me-3 mt-2 fa fa-money-bill" width="1.75em" height="1.75em"> </i>
        <div>
          <h3 class="fw-bold mb-0 fs-4"><?= $labels['txtAdministrativeServices'] ?></h3>
          <p><?= $labels['txtAdministrativeParagrah'] ?></p>
        </div>
      </div>
    </div>
    <div class="row justify-content-center mt-4 ps-4 py-0">
      <div class="col-12 col-sm-8 col-md-6 col-lg-4 d-flex align-items-start justify-content-center">
        <i class="fa fa-calendar-star text-muted me-3 mt-1 flex-shrink-0" style="font-size:1.75rem;"></i>
        <div>
          <h3 class="fw-bold mb-0 fs-4"><?= $labels['txtServicepackages'] ?></h3>
          <p class="mb-0"><?= $labels['txtServiceparagraph'] ?></p>
        </div>
      </div>
    </div>

  </div>
</div>

<? require_once __ROOT__ . '/components/pub/pubfoot.php' ?>
<script src="<?= autoVer('/pub/main/index.js'); ?>"></script>
</body>

</html>