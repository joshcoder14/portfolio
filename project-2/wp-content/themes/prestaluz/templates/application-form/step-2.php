<div class="step-2" id="step-2">
    <div class="heading">
        <h1 class="title">
            Registro
        </h1>
    </div>
    <div class="form">
        <div class="steps form_step-2">
            <form action="">
                <div class="row">
                    <!-- ¿Cuánto dinero necesitas? -->
                    <div class="col-6">
                        <div class="form-group">
                            <label for="amount">¿Cuánto dinero necesitas?</label>
                            <!-- Selection -->
                            <select name="amount" id="amount_loan" class="select_option">
                                <option value="0" selected disabled>Vali</option>
                                <option value="100">100€</option>
                                <option value="200">200€</option>
                                <option value="300">300€</option>
                                <option value="400">400€</option>
                                <option value="500">500€</option>
                                <option value="600">600€</option>
                                <option value="700">700€</option>
                                <option value="800">800€</option>
                                <option value="900">900€</option>
                                <option value="1000">1000€</option>
                            </select>
                            <!-- <img class="select_arrow" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow_down.svg" alt="arrow"> -->
                        </div>
                    </div>
                    <!-- ¿Para cuánto tiempo? -->
                    <div class="col-6">
                        <div class="form-group">
                            <label for="time">¿Para cuánto tiempo?</label>
                            <!-- Selection -->
                            <select name="time" id="date_return" class="select_option">
                                <option value="0" selected disabled>Vali</option>
                                <option value="1">1 meses</option>
                                <option value="2">2 meses</option>
                                <option value="3">3 meses</option>
                                <option value="4">4 meses</option>
                                <option value="5">5 meses</option>
                                <option value="6">6 meses</option>
                                <option value="7">7 meses</option>
                                <option value="8">8 meses</option>
                                <option value="9">9 meses</option>
                                <option value="10">10 meses</option>
                            </select>
                            <!-- <img class="select_arrow" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow_down.svg" alt="arrow"> -->
                        </div>
                    </div>
                </div>
                <div class="row form-row">
                    <div class="column-12">
                        <div class="total-amount">
                            <div class="amount">?</div>
                            <div class="text">Total a devolver</div>
                        </div>
                        <div class="total-fee">
                            <div class="amount">?</div>
                            <div class="text">Honorarios</div>
                        </div>
                        <div class="return-date">
                            <div class="date">?</div>
                            <div class="text">Fecha de devolución</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>