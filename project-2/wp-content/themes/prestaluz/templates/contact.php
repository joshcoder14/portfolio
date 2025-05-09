<section class="contact_page">
    <div class="container contact_container">
        <div class="wrapper contact_wrapper">
            <div class="heading">
                <h1 class="title">
                    <?php the_field('contact_heading'); ?>
                </h1>
                <div class="subtext">
                    <?php the_field('contact_subheading'); ?>
                </div>
            </div>

            <div class="form contact_form">
                <form action="">
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="form-group">
                                <!-- Seleccionar procedimiento * -->
                                <label for="procedure">Procedimiento *</label>
                                <select name="procedure" id="procedure" class="select_option">
                                    <option  value="0"selected>Seleccionar</option>
                                    <option value="1">Reembolso</option>
                                    <option value="2">Cancelar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <!-- Nombre de pila * -->
                                <label for="first_name">Nombre de pila *</label>
                                <input type="text" name="first_name" id="first_name">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <!-- Apellido  * -->
                                <label for="last_name">Apellido *</label>
                                <input type="text" name="last_name" id="last_name">
                            </div>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="form-group">
                                <!-- Correo electrónico  * -->
                                <label for="email">Correo electrónico *</label>
                                <input type="email" name="email" id="email">
                            </div>
                        </div>
                    </div>

                    <!-- Enviar Submit Button -->
                    <div class="row submit_btn">
                        <div class="col-12">
                            <div class="form-group">
                                <button type="submit" class="btn">Enviar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="wrapper contact_wrapper">
            <div class="contact_card">
                <div class="email">
                    <a href="mailto:<?php the_field('contact_email', 'options')?>" target="_blank" rel="noopener noreferrer"><?php the_field('contact_email', 'options')?></a>
                </div>
                <div class="address">
                    <div class="company_name">
                       <?php the_field('company_name','option')?>
                    </div>
                    <div class="company_address1">
                        <?php the_field('contact_street_address','option')?>
                    </div>
                </div>
            </div>

            <div class="accordion all hide-faq">
                <div class="accordion_container left">

                    <?php
                      //accordion for reembolso faq 
                        if( have_rows('contact_faq_reimburse') ):

                        while( have_rows('contact_faq_reimburse') ) : the_row();       
                                $question = get_sub_field('question');
                                $answer = get_sub_field('answer');
                        ?>
                            <div class="item-pergunta js-show-pergunta reimburse-faq">
                                <div class="title">
                                    <h3><?php echo  $question?></h3>
                                    <div class="icon"></div>
                                </div>
                                <p><?php echo $answer;?></p>
                            </div>
                        <?php
                        endwhile;
                        else :
                        endif;    
                    ?>
                     <?php
                     //accordion for cancelar faq 
                        if( have_rows('contact_faq_cancel') ):

                        while( have_rows('contact_faq_cancel') ) : the_row();       
                                $question = get_sub_field('question');
                                $answer = get_sub_field('answer');
                        ?>
                            <div class="item-pergunta js-show-pergunta cancel-faq">
                                <div class="title">
                                    <h3><?php echo  $question?></h3>
                                    <div class="icon"></div>
                                </div>
                                <p><?php echo $answer;?></p>
                            </div>
                        <?php
                        endwhile;
                        else :
                        endif;    
                    ?>
                </div>
            </div> 
        </div>
    </div>
</section>