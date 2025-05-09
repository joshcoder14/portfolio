<section class="login">
    <div class="container login_container">
        <div class="wrapper login_wrapper">
            <div class="heading">
                <h1 class="title w-100 h-auto">
                    Iniciar Sesion
                </h1>
            </div>
            <div class="form login_form">
                <form action="">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <!-- Nombre de usuario / Correo electrónico -->
                                <label for="email">Nombre de usuario / Correo electrónico</label>
                                <input type="email" name="email" id="email">
                            </div>
                        </div>
                    </div>
                    <!-- Contraseña -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" name="password" id="password">
                            </div>
                        </div>
                    </div>
                    <div class="row submit_btn">
                        <div class="col-12">
                            <div class="form-group">
                                <button type="submit" class="btn">Iniciar Sesion</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- forgot password and create account -->
            <div class="forgot_and_create_account">
                <div class="forgot">
                    <a href="<?php echo get_home_url() . '/forgot-password/'; ?>">Olvidaste tu contraseña ?</a>
                </div>
                <div class="separator"></div>
                <div class="create_account">
                    <a href="">Crea tu cuenta de Prestaluz</a>
                </div>
            </div>
        </div>
    </div>
</section>