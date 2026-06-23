        <!-- Global Modals -->
        <div class="modal fade" id="login" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblSignIn'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fal fa-user"></i>
                                </span>
                                <input id="lguser" name="email" class="form-control form-control-sm" type="text" placeholder="<?= $headLabels['frmEmail'] ?>" required>
                            </div>
                        </div>
                        <div class="form-group py-1 pb-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fal fa-lock"></i>
                                </span>
                                <input id="lgpass" name="password" class="form-control form-control-sm" type="password" placeholder="<?= $headLabels['frmPassword'] ?>" required>
                                <button id="lgview" class="input-group-text ps-2">
                                    <i class="fal fa-eye"></i>
                                </button>
                            </div>
                        </div>
<?php
if ($hasCaptcha) {
?>
                        <div class="form-group py-2">
                            <div class="input-group has-validation d-none" id="divLgCaptcha">
                                <span class="input-group-text captcha-input"></span>
                                <input id="lgcaptcha" name="captcha" class="form-control form-control-sm" type="text" placeholder="Captcha" required>
                                <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                            </div>
                        </div>
<?
}
?>
                        <div class="form-inline">
                            <input type="checkbox" id="lgremember" name="remember">
                            <label for="lgremember" class="text-muted">Remember me</label>
                            <div class="text-center">
                                <a href="#" class="font-weight-bold" data-bs-target="#forgot" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblForgotPassword'] ?></a>
                            </div>
                        </div>
                        <button id="lglogin" class="btn btn-primary btn-block mt-3"><?= $headLabels['lblSignIn'] ?></button>
<?php
if ($hasSignup) {
?>
                        <div class="text-center pt-4 text-muted" data-bs-target="#signup" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblDontHaveAccount'] ?> <a href="#"><?= $headLabels['lblSignUp'] ?></a>
<?php
}
?>
                        </div>
                    </div>
                    <!--
                    <div class="modal-footer bordert">
                        <div class="mx-auto pt-1">
                            <a href="https://wwww.facebook.com" target="_blank" class="px-2 text-decoration-none">
                                <img class="sitelogin" src="https://www.dpreview.com/files/p/articles/4698742202/facebook.jpeg" alt="">
                            </a>
                            <a href="https://www.google.com" target="_blank" class="px-2 text-decoration-none">
                                <img class="sitelogin" src="https://www.freepnglogos.com/uploads/google-logo-png/google-logo-png-suite-everything-you-need-know-about-google-newest-0.png" alt="">
                            </a>
                            <a href="https://www.github.com" target="_blank" class="px-2 text-decoration-none">
                                <img class="sitelogin" src="https://www.freepnglogos.com/uploads/512x512-logo-png/512x512-logo-github-icon-35.png" alt="">
                            </a>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
        <div class="modal fade" id="forgot" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblForgotPassword'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fal fa-at"></i>
                                </span>
                                <input id="fgemail" class="form-control form-control-sm" type="text" placeholder="<?= $headLabels['frmEmail'] ?>" required>
                            </div>
                        </div>
<?php
if($hasCaptcha) {
?>
                        <div class="form-group py-2">
                            <div class="input-group">
                                <span class="input-group-text captcha-input"></span>
                                <input id="fgcaptcha" class="form-control form-control-sm" type="text" placeholder="Captcha" required>
                            </div>
                        </div>
<?php
}
?>
                        <div class="text-center">
                            <a href="#" class="font-weight-bold" data-bs-target="#login" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblBackToSignIn'] ?></a>
                        </div>
                        <button id="fgsend" class="btn btn-danger btn-block mt-3"><?= $headLabels['btnSendPasswordResetEmail'] ?></button>
<?php
if ($hasSignup) {
?>
                        <div class="text-center pt-4 text-muted"><?= $headLabels['lblDontHaveAccount'] ?> <a href="#" data-bs-target="#signup" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblSignUp'] ?></a>
<?php
}
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="forgotresult" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblForgotPassword'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-2">
                            <h6></h6>
                        </div>
                        <div class="text-center">
                            <a href="#" class="font-weight-bold" data-bs-target="#login" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblBackToSignIn'] ?></a>
                        </div>
<?php
if ($hasSignup) {
?>
                        <div class="text-center pt-4 text-muted"><?= $headLabels['lblDontHaveAccount'] ?> <a href="#" data-bs-target="#signup" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblSignUp'] ?></a>
<?php
}
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="reset" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblResetPassword'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-1">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-lock"></i>
                                </span>
                                <input id="repass1" name="password" class="form-control form-control-sm" type="password" placeholder="<?= $headLabels['frmPassword'] ?>" required>
                                <button id="review1" class="input-group-text ps-2">
                                    <i class="fal fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="repass1 invalid-feedback mb-1"><?= $headLabels['ntePWNotStrongEnough'] ?></div>
                        <div class="repass1 valid-feedback mb-1"><?= $headLabels['ntePWStrongEnough'] ?></div>
                        <div class="form-group py-1">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-lock"></i>
                                </span>
                                <input id="repass2" name="passwordc" class="form-control form-control-sm" type="password" placeholder="<?= $headLabels['frmConfirmPassword'] ?>" required>
                                <button id="review2" class="input-group-text ps-2">
                                    <i class="fal fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="repass2 invalid-feedback mb-1"><?= $headLabels['ntePWNotSame'] ?></div>
                        <div class="repass2 valid-feedback mb-1"><?= $headLabels['ntePWSame'] ?></div>
                        <button id="reupdate" class="btn btn-success btn-block mt-3" data-key="<?= $pkey ?? '' ?>"><?= $headLabels['btnResetPassword'] ?></button>
                        <div class="text-center pt-4 text-muted"><?= $headLabels['lblAlreadyHaveAccount'] ?> <a href="#" data-bs-target="#login" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblSignIn'] ?></a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="resetresult" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblForgotPassword'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-2">
                            <h6></h6>
                        </div>
                        <div class="text-center">
                            <a href="#" class="font-weight-bold" data-bs-target="#login" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblBackToSignIn'] ?></a>
                        </div>
<?php
if ($hasSignup) {
?>
                        <div class="text-center pt-4 text-muted"><?= $headLabels['lblDontHaveAccount'] ?> <a href="#" data-bs-target="#signup" data-bs-toggle="modal" data-bs-dismiss="modal"><?= $headLabels['lblSignUp'] ?></a>
<?php
}
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
if ($hasSignup) {
?>
        <div class="modal fade" id="signup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><?= $headLabels['lblSignUp'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group py-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-user"></i>
                                </span>
                                <input id="sufirst" name="firstname" class="form-control form-control-sm" type="text" placeholder="<?= $headLabels['frmFirstname'] ?>" required>
                                <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                            </div>
                        </div>
                        <div class="form-group py-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-user"></i>
                                </span>
                                <input id="sulast" name="lastname" class="form-control form-control-sm" type="text" placeholder="<?= $headLabels['frmLastname'] ?>" required>
                                <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                            </div>
                        </div>
                        <div class="form-group py-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-at"></i>
                                </span>
                                <input id="suuser" name="username" class="form-control form-control-sm" type="text" placeholder="<?= $headLabels['frmEmail'] ?>" required>
                                <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                            </div>
                        </div>
                        <div class="form-group py-1 pb-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-lock"></i>
                                </span>
                                <input id="supass1" name="password" class="form-control form-control-sm" type="password" placeholder="<?= $headLabels['frmPassword'] ?>" required>
                                <button id="suview1" class="input-group-text ps-2">
                                    <i class="fal fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                        </div>
                        <div class="form-group py-1 pb-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="fal fa-lock"></i>
                                </span>
                                <input id="supass2" name="passwordc" class="form-control form-control-sm" type="password" placeholder="<?= $headLabels['frmConfirmPassword'] ?>" required>
                                <button id="suview2" class="input-group-text ps-2">
                                    <i class="fal fa-eye"></i>
                                </button>
                                <div class="invalid-feedback"><?= $headLabels['nteSecurePassword'] ?></div>
                            </div>
                        </div>
<?php
    if ($hasCaptcha) {
?>
                        <div class="form-group py-2">
                            <div class="input-group has-validation">
                                <span class="input-group-text captcha-input"></span>
                                <input id="sucaptcha" name="captcha" class="form-control form-control-sm" type="text" placeholder="Captcha" required>
                                <div class="invalid-feedback"><?= $headLabels['nteFieldReqired'] ?></div>
                            </div>
                        </div>
<?php
    }
?>
                        <button id="susignup" class="btn btn-success btn-block mt-3"><?= $headLabels['lblSignUp'] ?></button>
                        <div class="text-center pt-4 text-muted"><?= $headLabels['lblAlreadyHaveAccount'] ?> <a href="#" data-bs-target="#login" data-bs-toggle="modal" data-bs-dismiss="modal">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
}
?>
        <!-- Back to top button -->
        <button type="button" class="btn btn-secondary btn-floating btn-lg" id="btn-back-to-top">
            <i class="fal fa-chevron-up"></i>
        </button>
    </main>
    <footer class="menu-bg footer mt-auto py-3 bg-eventos text-white">
        <div class="container ps-4">
            <span class="">© 2025 by EDMI - Soluciones Empresariales CR. All rights reserved.</span>
        </div>
    </footer>
    <script src="/lib/jquery/jquery-3.6.3.min.js"></script>
    <script src="<?= autoVer('/lib/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= autoVer('/lib/bootstrap-5-multi-level-dropdown/bootstrap5-dropdown-ml-hack.js'); ?>"></script>
    <script src="<?= autoVer('/lib/initialjs/initial.min.js'); ?>"></script>
    <script src="<?= autoVer('/lib/password-strength-meter/password.min.js'); ?>"></script>
    <script src="<?= autoVer('/assets/js/general.js') ?>"></script>
    <script>
        var chrLang = '<?= $chrLang ?>';
        var chrLocale = '<?= str_replace('_', '-', $chrLocale) ?>';
        var headLabels = <?= json_encode($headLabels); ?>;
        var modal = '<?= $modal ?>';
    </script>
    <script src="<?= autoVer('/pub/assets/pub.js') ?>"></script>
