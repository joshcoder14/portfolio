<?php
    $currentStep = 1;
?>

<div class="form_steps form_step-<?php echo $currentStep; ?>">
    <div class="container">
        <div class="wrapper">
            <!-- Step 1 -->
            <?php get_template_part('templates/application-form/step-1'); ?>

            <!-- Step 2 -->
            <?php get_template_part('templates/application-form/step-2'); ?>

            <!-- Step 3 -->
            <?php get_template_part('templates/application-form/step-3'); ?>

            <!-- Step 4 -->
            <?php get_template_part('templates/application-form/step-4'); ?>

            <!-- Step 5 -->
            <?php get_template_part('templates/application-form/step-5'); ?>

            
            <div class="form-buttons-container">
                <a class="form-previous-button " id="previous-button-step-1">
                    <div class="form-previous-button-content" id="prev-btn-content-step">
                        <div class="text">Anterior</div> 
                    </div>
                </a>
                <a class="form-next-button" id="submit-button-step-1">
                    <div class="form-next-button-content" id="next-btn-content-step">
                        <div class="text">Siguiente</div>
                    </div>
                </a>
            </div>

            <div class="steps_container">
                <div class="steps_wrapper">
                    
                    <!-- Informacion -->
                    <div class="steps form_step-1 active" data-step="1">
                        <div class="check_icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/check-black.svg" alt="" class="check">
                        </div>
                        <div class="steps_label">
                            Informacion
                        </div>
                    </div>

                    <!-- Registro -->
                    <div class="steps form_step-2" data-step="2">
                        <div class="check_icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/check-black.svg" alt="" class="check">
                        </div>
                        <div class="steps_label">
                            Registro
                        </div>
                    </div>

                    <!-- Verificación -->
                    <div class="steps form_step-3" data-step="3">
                        <div class="check_icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/check-black.svg" alt="" class="check">
                        </div>
                        <div class="steps_label">
                            Verificación
                        </div>
                    </div>

                    <!-- Consigue tu préstamo -->
                    <div class="steps form_step-4" data-step="4">
                        <div class="check_icon">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/check-black.svg" alt="" class="check">
                        </div>
                        <div class="steps_label">
                            Consigue tu préstamo 
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="circle_bg">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/application-right-top.svg" alt="" class="top_right">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/application-left-bottom.svg" alt="" class="bottom_left">
            </div>
        </div>
    </div>
</div>