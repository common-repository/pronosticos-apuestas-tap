<?php
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;

if(isset($_POST['promocion'])){
    $promocion = $_POST['promocion'];
    do_action('pronostico_apuestas_save_promocion', $promocion);
}

if(isset($_POST['metodo_pago'])){
    $metodos_pago = $_POST['metodo_pago'];
    do_action('pronostico_apuestas_metodos_pago', $metodos_pago);
}

?>
<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?> &raquo; Ajustes</h2>

    <?php settings_errors('pronostico-apuestas-promocion'); ?>
    <?php settings_errors('pronostico-apuestas-metodos-pago'); ?>
    <div class="card">
        <h3><?php _e('Promocion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
        <?php $promocion = get_option('PRONOSTICO_APUESTAS_PROMOCION'); ?>
        <form id="form-pronostico-apuestas-promocion" role="form" method="post" action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."&settings-updated=1" ) ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="promocion_codigo"><?php _e('Codigo', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                        </th>
                        <td>
                            <input type="text" id="promocion_codigo" name="promocion[codigo]" value="<?php echo isset($promocion['codigo']) ? $promocion['codigo'] : ''; ?>" required="required">
                            <p class="description"><?php _e('Escriba el codigo de la promocion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="promocion_descuento"><?php _e('Descuento', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                        </th>
                        <td>
                            <input type="text" id="promocion_descuento" name="promocion[descuento]" value="<?php echo isset($promocion['descuento']) ? $promocion['descuento'] : ''; ?>" required="required">
                            <p class="description"><?php _e('Escriba la cantidad a descontar en por ciento (sin %)', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="promocion_fecha_fin"><?php _e('Fecha fin', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                        </th>
                        <td>
                            <input type="text" id="promocion_fecha_fin" name="promocion[fecha_fin]" value="<?php echo isset($promocion['fecha_fin']) ? $promocion['fecha_fin'] : ''; ?>" required="required">
                            <p class="description"><?php _e('Seleccionar/escribir fin de la promocion.<br>Indicar utilizando el formato dd/mm/yyyy', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <?php wp_nonce_field( 'form_pronostico_apuestas_promocion_save', '__crsf_token_promocion' ); ?>
                            <button type="submit" class="button button-primary">
                                <?php _e('Guardar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>

    <div class="card">
        <h3><?php _e('Metodos de Pago', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
        <?php $metodos_pago = get_option('PRONOSTICO_APUESTAS_METODOS_PAGO'); ?>
        <form id="form-pronostico-apuestas-metodos-pago" method="post" action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."&settings-updated=1" ) ?>">
            <fieldset>
                <h4>Paysafecard</h4>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paysafecard_activado"><?php _e('Habilitar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="metodo_pago_paysafecard_activado" name="metodo_pago[paysafecard][activado]" value="1" <?php if($metodos_pago['paysafecard']['activado']):?>checked="checked"<?php endif;?>>
                                <p class="description"><?php _e('Seleccione esta opcion si requiere este metodo de pago activado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paysafecard_email_contacto"><?php _e('Email de contacto', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <input type="text" id="metodo_pago_paysafecard_email_contacto" name="metodo_pago[paysafecard][email_contacto]" class="text-regular" value="<?php echo $metodos_pago['paysafecard']['email_contacto']; ?>">
                                <p class="description"><?php _e('Escribir la direccion de email a donde los usuarios enviaran sus mensajes', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <button type="submit" class="button button-primary">
                                    <?php _e('Guardar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </fieldset>
            <hr>
            <fieldset>
                <h4>Paypal</h4>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_activado"><?php _e('Habilitar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="metodo_pago_paypal_activado" name="metodo_pago[paypal][activado]" value="1" <?php if($metodos_pago['paypal']['activado']):?>checked="checked"<?php endif;?>>
                                <p class="description"><?php _e('Seleccione esta opcion si requiere este metodo de pago activado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_language"><?php _e('Idioma', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <select id="metodo_pago_paypal_language" name="metodo_pago[paypal][language]" >
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'da_DK'):?>selected="selected"<?php endif;?> value="da_DK">Danish</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'nl_BE'):?>selected="selected"<?php endif;?> value="nl_BE">Dutch</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'EN_US'):?>selected="selected"<?php endif;?> value="EN_US">English</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'fr_CA'):?>selected="selected"<?php endif;?> value="fr_CA">French</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'de_DE'):?>selected="selected"<?php endif;?> value="de_DE">German</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'he_IL'):?>selected="selected"<?php endif;?> value="he_IL">Hebrew</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'it_IT'):?>selected="selected"<?php endif;?> value="it_IT">Italian</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'ja_JP'):?>selected="selected"<?php endif;?> value="ja_JP">Japanese</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'no_NO'):?>selected="selected"<?php endif;?> value="no_NO">Norwgian</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'pl_PL'):?>selected="selected"<?php endif;?> value="pl_PL">Polish</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'pt_BR'):?>selected="selected"<?php endif;?> value="pt_BR">Portuguese</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'ru_RU'):?>selected="selected"<?php endif;?> value="ru_RU">Russian</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'es_ES'):?>selected="selected"<?php endif;?> value="es_ES">Espa&ntilde;ol</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'sv_SE'):?>selected="selected"<?php endif;?> value="sv_SE">Swedish</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'zh_CN'):?>selected="selected"<?php endif;?> value="zh_CN">Simplified Chinese -China only</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'zh_HK'):?>selected="selected"<?php endif;?> value="zh_HK">Traditional Chinese - Hong Kong only</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'zh_TW'):?>selected="selected"<?php endif;?> value="zh_TW">Traditional Chinese - Taiwan only</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'tr_TR'):?>selected="selected"<?php endif;?> value="tr_TR">Turkish</option>
                                    <option <?php if ($metodos_pago['paypal']['language'] == 'th_TH'):?>selected="selected"<?php endif;?> value="th_TH">Thai</option>
                                </select>
                                <p class="description"><?php _e('Paypal actualmente soporta 18 idiomas', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_currency"><?php _e('Moneda', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <select id="metodo_pago_paypal_currency" name="metodo_pago[paypal][currency]" >
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'AUD'):?>selected="selected"<?php endif;?> value="AUD">Australian Dollar - AUD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'BRL'):?>selected="selected"<?php endif;?> value="BRL">Brazilian Real - BRL</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'CAD'):?>selected="selected"<?php endif;?> value="CAD">Canadian Dollar - CAD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'CZK'):?>selected="selected"<?php endif;?> value="CZK">Czech Koruna - CZK</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'DKK'):?>selected="selected"<?php endif;?> value="DKK">Danish Krone - DKK</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'EUR'):?>selected="selected"<?php endif;?> value="EUR">Euro - EUR</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'HKD'):?>selected="selected"<?php endif;?> value="HKD">Hong Kong Dollar - HKD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'HUF'):?>selected="selected"<?php endif;?> value="HUF">Hungarian Forint - HUF</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'ILS'):?>selected="selected"<?php endif;?> value="ILS">Israeli New Sheqel - ILS</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'JPY'):?>selected="selected"<?php endif;?> value="JPY">Japanese Yen - JPY</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'MYR'):?>selected="selected"<?php endif;?> value="MYR">Malaysian Ringgit - MYR</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'MXN'):?>selected="selected"<?php endif;?> value="MXN">Mexican Peso - MXN</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'NOK'):?>selected="selected"<?php endif;?> value="NOK">Norwegian Krone - NOK</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'NZD'):?>selected="selected"<?php endif;?> value="NZD">New Zealand Dollar - NZD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'PHP'):?>selected="selected"<?php endif;?> value="PHP">Philippine Peso - PHP</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'PLN'):?>selected="selected"<?php endif;?> value="PLN">Polish Zloty - PLN</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'GBP'):?>selected="selected"<?php endif;?> value="GBP">Pound Sterling - GBP</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'RUB'):?>selected="selected"<?php endif;?> value="RUB">Russian Ruble - RUB</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'SGD'):?>selected="selected"<?php endif;?> value="SGD">Singapore Dollar - SGD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'SEK'):?>selected="selected"<?php endif;?> value="SEK">Swedish Krona - SEK</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'CHF'):?>selected="selected"<?php endif;?> value="CHF">Swiss Franc - CHF</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'TWD'):?>selected="selected"<?php endif;?> value="TWD">Taiwan New Dollar - TWD</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'THB'):?>selected="selected"<?php endif;?> value="THB">Thai Baht - THB</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'TRY'):?>selected="selected"<?php endif;?> value="TRY">Turkish Lira - TRY</option>
                                    <option <?php if ($metodos_pago['paypal']['currency'] == 'USD'):?>selected="selected"<?php endif;?> value="USD">U.S. Dollar - USD</option>
                                </select>
                                <p class="description"><?php _e('Paypal actualmente soporta 25 tipos de monedas', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_live_account"><?php _e('PayPal Live Account', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <input type="text" id="metodo_pago_paypal_live_account" name="metodo_pago[paypal][live][account]" value="<?php echo $metodos_pago['paypal']['live']['account']?>"> <span>(Requerido)</span>
                                <p class="description"><?php _e('Enter a valid Merchant account ID (strongly recommend) or PayPal account email address. All payments will go to this account. You can find your Merchant account ID in your PayPal account under Profile -> My business info -> Merchant account ID. If you don\'t have a PayPal account, you can sign up for free at <a target="_blank" href="https://paypal.com">PayPal</a>.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                                <input type="hidden" name="metodo_pago[paypal][live][path]" value="paypal">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_sandbox_account"><?php _e('PayPal Sandbox Account', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <input type="text" id="metodo_pago_paypal_sandbox_account" name="metodo_pago[paypal][sandbox][account]" value="<?php echo $metodos_pago['paypal']['sandbox']['account']?>"> <span>(Opcional)</span>
                                <p class="description"><?php _e('Enter a valid sandbox PayPal account email address. A Sandbox account is a PayPal accont with fake money used for testing. This is useful to make sure your PayPal account and settings are working properly being going live. To create a Sandbox account, you first need a Developer Account. You can sign up for free at the <a target="_blank" href="https://www.paypal.com/webapps/merchantboarding/webflow/unifiedflow?execution=e1s2">PayPal Developer</a> site. Once you have made an account, create a Sandbox Business and Personal Account <a target="_blank" href="https://developer.paypal.com/webapps/developer/applications/accounts">here</a>. Enter the Business acount email on this page and use the Personal account username and password to buy something on your site as a customer.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></p>
                                <input type="hidden" name="metodo_pago[paypal][sandbox][path]" value="sandbox.paypal">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="metodo_pago_paypal_mode_live"><?php _e('PayPal Execution Mode', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                            </th>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="metodo_pago[paypal][mode]" id="metodo_pago_paypal_mode_live" value="live" <?php if($metodos_pago['paypal']['mode'] === "live"): ?>checked="checked"<?php endif;?>> Live mode
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="metodo_pago[paypal][mode]" id="metodo_pago_paypal_mode_sandbox" value="sandbox" <?php if($metodos_pago['paypal']['mode'] === "sandbox"): ?>checked="checked"<?php endif;?>> Sandbox mode
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2">
                                <input type="hidden" name="metodo_pago[paypal][payment_action]" value="sale">
                                <input type="hidden" name="metodo_pago[paypal][target]" value="_self">
                                <input type="hidden" name="metodo_pago[paypal][url][cancel]" value="<?php echo esc_url(get_post_permalink(intval(get_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_CANCELADO'))))?>">
                                <input type="hidden" name="metodo_pago[paypal][url][return]" value="<?php echo esc_url(get_post_permalink(intval(get_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_ACEPTADO'))))?>">
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <button type="submit" class="button button-primary">
                                    <?php _e('Guardar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </fieldset>
<!--            <hr>-->
<!--            <fieldset>-->
<!--                <h4>Skrill</h4>-->
<!--                <table class="form-table">-->
<!--                    <tbody>-->
<!--                        <tr>-->
<!--                            <th scope="row">-->
<!--                                <label for="metodo_pago_skrill_activado">--><?php //_e('Habilitar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?><!--</label>-->
<!--                            </th>-->
<!--                            <td>-->
<!--                                <input type="checkbox" id="metodo_pago_skrill_activado" name="metodo_pago[skrill][activado]" value="1" --><?php //if($metodos_pago['skrill']['activado']):?><!--checked="checked"--><?php //endif;?><!-->
<!--                                <p class="description">--><?php //_e('Seleccione esta opcion si requiere este metodo de pago activado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?><!--</p>-->
<!--                            </td>-->
<!--                        </tr>-->
<!--                    </tbody>-->
<!--                    <tfoot>-->
<!--                        <tr>-->
<!--                            <td colspan="2">-->
<!--                                <button type="submit" class="button button-primary">-->
<!--                                    --><?php //_e('Guardar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
<!--                                </button>-->
<!--                            </td>-->
<!--                        </tr>-->
<!--                    </tfoot>-->
<!--                </table>-->
<!--            </fieldset>-->
        </form>
    </div>

</div>