<?php

function theme_styles(){

		wp_enqueue_style('normalize', get_stylesheet_directory_uri().'/css/normalize.css');
		wp_enqueue_style('Bootstrap CSS', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
		wp_enqueue_style('Raleway', "https://fonts.googleapis.com/css?family=Raleway:500,500i,700");
		wp_enqueue_style('Open Sans', "https://fonts.googleapis.com/css?family=Open+Sans:300i,400,700i");
        wp_enqueue_style('Roboto', "https://fonts.googleapis.com/css?family=Roboto:300,400,700");
		wp_enqueue_style('fontawesome', get_stylesheet_directory_uri().'/css/font-awesome.min.css');
		wp_enqueue_style('style', get_stylesheet_uri());
		wp_enqueue_script('jquery');
        wp_enqueue_script('effects', get_stylesheet_directory_uri().'/js/effects.js', array('jquery') );
	}

// compatibilidad con Woocommerce declarada
/*
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
add_theme_support( 'woocommerce' );
}
*/


add_action('wp_enqueue_scripts', 'theme_styles');
add_theme_support('post-thumbnails');/*añadir una imagen destacada*/

function prefix_add_footer_styles() {
    wp_enqueue_style( 'Style Custom', get_template_directory_uri() . '/css/custom-css-footer.css' );
    wp_enqueue_script('Script Custom', get_stylesheet_directory_uri().'/js/custom-script.js', array('jquery') );
};
add_action( 'get_footer', 'prefix_add_footer_styles' );

add_post_type_support( 'post_type', 'woosidebars' );

/*navegacion-menu*/
register_nav_menus( array(
'menu_main' => 'Main Menu',
'menu_left' => 'Menu footer',
'sidebar-products' => 'Sidebar Productos'
));


function theme_widgets() {
	register_sidebar( array(
            'name'          => __( 'Menu' ),
            'id'            => 'main-menu',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'     => '</div>',
            'before_title'     => '<h2 class="widgettitle">',
            'after_title'     => '</h2>',
        ) );
    register_sidebar( array(
            'name'          => __( 'Menu footer' ),
            'id'            => 'menu-footer',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'     => '</div>',
            'before_title'     => '<h2 class="widgettitle">',
            'after_title'     => '</h2>',
        ) );

	register_sidebar( array(
            'name'          => __( 'Logo' ),
            'id'            => 'widget-logo',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'     => '</div>',
            'before_title'     => '<h2 class="widgettitle">',
            'after_title'     => '</h2>',
        ) );

    register_sidebar( array(
            'name'          => __( 'Redes' ),
            'id'            => 'widget-redes',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'     => '</div>',
            'before_title'     => '<h2 class="widgettitle">',
            'after_title'     => '</h2>',
        ) );

    register_sidebar( array(
            'name'          => __( 'Footer contactenos' ),
            'id'            => 'widget-contactenos',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'     => '</div>',
            'before_title'     => '<h2 class="widgettitle">',
            'after_title'     => '</h2>',
        ) );

    register_sidebar( array(
    	'name'          => __( 'Footer nosotros' ),
    	'id'            => 'widget-nosotros',
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget'     => '</div>',
    	'before_title'     => '<h2 class="widgettitle">',
    	'after_title'     => '</h2>',
    ) );

    register_sidebar( array(
    	'name'          => __( 'Widget facebook' ),
    	'id'            => 'widget-facebook',
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget'     => '</div>',
    	'before_title'     => '<h2 class="widgettitle">',
    	'after_title'     => '</h2>',
    ) );

     register_sidebar( array(
        'name'          => __( 'Menu Productos' ),
        'id'            => 'nenu-woocommerce',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'     => '</div>',
        'before_title'     => '<h2 class="widgettitle">',
        'after_title'     => '</h2>',
    ) );

}
add_action('widgets_init', 'theme_widgets');
add_theme_support( 'post-thumbnails' );


// filters Woocommerce
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 4 );
//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

// Changing Add to Cart button text to custom text in individual product pages
function woo_custom_cart_button_text(){
	return __('Comprar', 'woocommerce');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
add_filter( 'woocommerce_product_add_to_cart_text', 'woo_custom_cart_button_text' );

add_filter ('woocommerce_order_button_text', function () {
     return 'Realizar el pago';
});

add_action( 'woocommerce_before_shop_loop', 'woocommerce_title', 15 );
function woocommerce_title() {
    $category = get_the_category();
    if(is_product_category()||is_product_tag()) {
        if(is_product_category()){ 
            global $wp_query;
            $cat = $wp_query->get_queried_object();
            $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
            $image = wp_get_attachment_url( $thumbnail_id );
            if ( $image ) {
            ?>
        <style type="text/css">
            .header-shop-products{
                background-image: url(<?php echo "$image"; ?>);
                background-attachment: fixed;
            }
        </style>
     <?php  } }
     else{
        if(is_product_tag()){ 
            if (function_exists('z_taxonomy_image_url')){
                $img_tag = z_taxonomy_image_url();
                if (strlen($img_tag)>0){
                //echo "<script>console.log(".$cat.")</script>";
                ?>
            <style type="text/css">
                .header-shop-products{
                    background-image: url(<?php echo $img_tag; ?>);
                    background-attachment: fixed;
                }
                .col-md-12.header-shop-products:before {
                    background: transparent !important;
                }
            </style>
         <?php
            }
            }
        }
     }?>
        <!--<div class="col-md-12 cat-name"><h1><?php woocommerce_page_title(); ?></h1></div>-->
        <script type="text/javascript">
          var catName = "<?php woocommerce_page_title(); ?>";
          jQuery(document).ready(function($){
              $( ".content-page" ).before( "<div class='col-md-12 header-shop-products'><h2>"+catName+"</h2></div>" );
          });
        </script>
        <?php
    }
}

add_action( 'woocommerce_before_single_product', 'woocommerce_sidebar', 6 );
function woocommerce_sidebar() {
    //if(is_product()) {
        ?>
        <div class="sidebar-content-post">
            <?php dynamic_sidebar('nenu-woocommerce'); ?>

            <style type="text/css">
                .sidebar-content-post {
                    float: left;
                    width: 20%;
                    clear: none;
                }
                .woocommerce div.product {
                    float: left !important;
                    width: 80% !important;
                    clear: none !important;
                }
            </style>

            <script type="text/javascript">
                
                    jQuery(document).ready(function($){
                        $('#woocommerce_product_categories-2').click(function() {
                            //$( "#woocommerce_product_tag_cloud-3 .tagcloud" ).slideUp();
                            //$( "#nav_menu-4 .menu-sidebar-productos-container" ).slideUp();
                          if ( $( "#woocommerce_product_categories-2 .product-categories" ).is( ":hidden" ) ) {
                            $( "#woocommerce_product_categories-2 .product-categories" ).slideDown( "slow" );
                          } else {
                            $( "#woocommerce_product_categories-2 .product-categories" ).slideUp();
                          }
                        });

                       $('#woocommerce_product_tag_cloud-3').click(function() {
                            //$( "#woocommerce_product_categories-2 .product-categories" ).slideUp();
                            //$( "#nav_menu-4 .menu-sidebar-productos-container" ).slideUp();
                          if ( $( "#woocommerce_product_tag_cloud-3 .tagcloud" ).is( ":hidden" ) ) {
                            $( "#woocommerce_product_tag_cloud-3 .tagcloud" ).slideDown( "slow" );
                          } else {
                            $( "#woocommerce_product_tag_cloud-3 .tagcloud" ).slideUp();
                          }
                        });

                       $('#nav_menu-4').click(function() {
                            //$("ul.sub-menu").slideUp();
                            //$( "#woocommerce_product_categories-2 .product-categories" ).slideUp();
                            //$( "#woocommerce_product_tag_cloud-3 .tagcloud" ).slideUp();
                          if ( $( "#nav_menu-4 .menu-sidebar-productos-container" ).is( ":hidden" ) ) {
                            $( "#nav_menu-4 .menu-sidebar-productos-container" ).slideDown( "slow" );
                          } else {
                            $( "#nav_menu-4 .menu-sidebar-productos-container" ).slideUp();
                          }
                        });

                       $('#nav_menu-4 ul#menu-sidebar-productos li').mouseover(function() {
                            $("ul.sub-menu").slideUp(0);
                            $(this).find("ul.sub-menu").slideDown(0);
                        });
                       $('#nav_menu-4 ul#menu-sidebar-productos').mouseout(function() {
                            //$("ul.sub-menu").slideUp(0);
                        });

                       $('.wrps_related_products_area_title').html('<span>Productos Relacionados</span>');
                    });
            </script>
        </div>
    <?php
   // }
}

function wc_empty_cart_redirect_url() {
    return '/productos';
}
add_filter( 'woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url' );

//REMOVE FILTER SHOP
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

//Price
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );

add_filter( 'woocommerce_loop_product_link', 'bbloomer_change_product_permalink_shop', 99, 2 );
function bbloomer_change_product_permalink_shop( $link, $product ) {
$this_product_id = $product->get_id();
// E.G. CHANGE LINK FOR TAG
if ( $_GET["term"] == "tag" ) $link = get_the_permalink().'?term=tag';
return $link;
}

add_filter( 'woocommerce_return_to_shop_redirect', 'bbloomer_change_return_shop_url' );
 
function bbloomer_change_return_shop_url() {
return home_url('/productos');
}

add_action( 'woocommerce_before_cart_table', 'woo_add_continue_shopping_button_to_cart' );
function woo_add_continue_shopping_button_to_cart() {
 $shop_page_url = '/productos';
 
 echo '<div class="woocommerce-message">';
 echo ' <a href="'.$shop_page_url.'" class="button">Seguir Comprando</a> ¿Quieres más productos?';
 echo '</div>';
}


//CONTRASEÑA DEBIL - HABILITAR
function wc_ninja_remove_password_strength() {
	if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_print_scripts', 'wc_ninja_remove_password_strength', 100 );

 


//add_filter( 'woocommerce_product_related_posts_relate_by_tag', function() {return false;});
//add_filter( 'woocommerce_product_related_posts_relate_by_tag', '__return_false' );
//add_filter( 'woocommerce_product_related_posts_relate_by_category', function() {return false;});

add_action( 'woocommerce_review_order_before_submit', 'add_privacy_checkbox', 9 );
function add_privacy_checkbox() {
woocommerce_form_field( 'privacy_policy', array(
'type' => 'checkbox',
'class' => array('form-row privacy'),
'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
'required' => true,
'label' => 'He leído y acepto la <a href="/politica-y-privacidad/">política de privacidad</a>.',
));
}
add_action( 'woocommerce_checkout_process', 'privacy_checkbox_error_message' );
function privacy_checkbox_error_message() {
if ( ! (int) isset( $_POST['privacy_policy'] ) ) {
wc_add_notice( __( 'Necesitas aceptar la política de privacidad para seguir con el proceso.' ), 'error' );
}
}


add_action( 'woocommerce_review_order_before_cart_contents', 'custom_table_order_3', 10 );
function custom_table_order_3() { ?>

    <style type="text/css">
        #order_review strong.product-quantity {
            float: right;
            margin-right: 5%;
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function( $ ) {
            $('#order_review table th.product-name').append('<strong class="product-quantity">Cantidad</strong>');
        });
    </script>

<?php
}

// After registration, redirect
function custom_registration_redirect() {
    return home_url('/mi-cuenta/edit-address/facturacion');
}
add_filter('woocommerce_registration_redirect', 'custom_registration_redirect', 20);


//Redirect logout
add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
         wp_redirect( home_url() );
         exit();
}


/**
* add fields in the register form.
*/
function wooc_extra_register_fields() {?>
       <!-- <p class="form-row form-row-wide">
       <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
       <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
       </p> -->
       <p class="form-row form-row-first">
           <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
           <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
       </p>
       <p class="form-row form-row-last">
           <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
           <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
       </p>
       <p class="form-row form-row-wide">
           <label for="reg_billing_tipo_documento"><?php _e( 'Tipo de documento', 'woocommerce' ); ?><span class="required">*</span></label>
           <select type="number" class="input-text" name="billing_tipo_documento" id="reg_billing_tipo_documento">
                <option></option>
                <option value="C.C.">C.C.</option>
                <option value="C.E.">C.E.</option>
                <option value="Pasaporte">Pasaporte</option>
           </select>
       </p>
       <p class="form-row form-row-wide">
           <label for="reg_billing_numero_documento"><?php _e( 'Numero de documento', 'woocommerce' ); ?><span class="required">*</span></label>
           <input type="number" class="input-text" name="billing_numero_documento" id="reg_billing_numero_documento" value="<?php esc_attr_e( $_POST['billing_numero_documento'] ); ?>" />
       </p>
       <div class="clear"></div>
       <?php
 }
 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );

/**
* register fields Validating.
*/
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {

    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
        $validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: ¡El nombre es requerido!', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
        $validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: ¡El apellido es requerido!.', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_tipo_documento'] ) && empty( $_POST['billing_tipo_documento'] ) ) {
        $validation_errors->add( 'billing_tipo_documento_error', __( '<strong>Error</strong>: ¡El tipo de documento es requerido!.', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_numero_documento'] ) && empty( $_POST['billing_numero_documento'] ) ) {
        $validation_errors->add( 'billing_numero_documento_error', __( '<strong>Error</strong>: ¡El numero de documento es requerido!.', 'woocommerce' ) );
    }

    $users = get_users( array( 'fields' => array( 'ID' ) ) );
    foreach($users as $user_id){
        $get_document = get_user_meta ( $user_id->ID);
            // if(strlen($get_document['billing_numero_documento'][0]) > 0){
        if($get_document['billing_numero_documento'][0] == $_POST['billing_numero_documento']){
            $validation_errors->add( 'billing_numero_documento_error', __( '<strong>Error</strong>: ¡Este documento ya se encuentra registrado!.', 'woocommerce' ) );
        }
    }
    
    return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );

// save fields in database
function save_user_fields ($user_id) {
  if ( isset($_POST['billing_first_name']) ){
    update_user_meta($user_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
  }
 
  if ( isset($_POST['billing_last_name']) ){
    update_user_meta($user_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
  }

  if ( isset($_POST['billing_tipo_documento']) ){
    update_user_meta($user_id, 'billing_tipo_documento', sanitize_text_field($_POST['billing_tipo_documento']));
  }

  if ( isset($_POST['billing_numero_documento']) ){
    update_user_meta($user_id, 'billing_numero_documento', sanitize_text_field($_POST['billing_numero_documento']));
  }
 
}
add_action('user_register', 'save_user_fields');


// Cambiar titulo de formulario de inicion sesion
add_filter('gettext', 'translate_text_form');
function translate_text_form($translated) {
 $translated = str_ireplace('Acceder', 'Iniciar sesión', $translated);
 return $translated;
}

add_filter('gettext', 'translate_text_label');
function translate_text_label($translated) {
 $translated_label = str_ireplace('Nombre de usuario o correo electrónico', 'Correo electrónico', $translated);
 return $translated_label;
}

?>
