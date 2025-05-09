<?php
    $monthNames = array(
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    );
?>
<div class="heading">
    <div class="logo">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/logo.svg" alt="logo">
    </div>
    <div class="title">
        <h1>
            <span>Mejores Créditos rápidos</span>
            en <?php echo $monthNames[wp_date('n')]; ?> <sup><?php echo wp_date('Y'); ?></sup>
        </h1>
    </div>
</div>