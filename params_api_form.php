<?php
global $wpdb;
$whitepage_table_name = $wpdb->prefix.'whitepage';
$rs = $wpdb->get_row( "SELECT * FROM {$whitepage_table_name} WHERE id = 1", ARRAY_A );

if(isset($_POST['zwhp_cleapipublique'], $_POST['zwhp_cleapiprivee'])){
    $zwhp_cleapipublique= sanitize_text_field($_POST['zwhp_cleapipublique']);
    $zwhp_cleapiprivee= sanitize_text_field($_POST['zwhp_cleapiprivee']);

    if(!empty($_POST['zwhp_cleapipublique']) AND !empty($_POST['zwhp_cleapiprivee'])){
        $wpdb->update( $whitepage_table_name,
            array(
                'cleapipublique' => sanitize_text_field(htmlspecialchars($_POST['zwhp_cleapipublique'])),
                'cleapiprivee' => sanitize_text_field(htmlspecialchars($_POST['zwhp_cleapiprivee']))
            ),
            array( 'ID' => 1 ),
            array(
                '%s',
                '%s'
            ),
            array( '%d' )
        );
        echo '<div class="updated notice is-dismissible" style="padding:15px;">'.esc_html( __( 'Clés API enregistrées avec succès', 'whitepage' ) ).'</div>';
    }else{
        echo '<div class="error notice is-dismissible" style="padding:15px;">'.esc_html( __('Renseignez les deux champs', 'whitepage' ) ).'</div>';
    }
}else{
    $zwhp_cleapipublique= sanitize_text_field($rs['cleapipublique']);
    $zwhp_cleapiprivee= sanitize_text_field($rs['cleapiprivee']);
}
?>

<style>
    .formapi label{display: block;}
</style>

<div class="wrap">
    <h1><?= esc_html( __('White Page - Renseignez vos paramètres API', 'whitepage' ) ); ?></h1>
    <h2><?= esc_html( __('Il s\'agit de vos clés API sur la plateforme', 'whitepage' ) ); ?> <a href="<?= esc_url('https://www.white.page'); ?>" target="_blank">www.white.page</a></h2>
    <form class="formapi" method="post">
        <p><label><?= esc_html( __('Clé API publique', 'whitepage' ) ); ?></label><input type="text" name="zwhp_cleapipublique" id="zwhp_cleapipublique" value="<?= $zwhp_cleapipublique; ?>" class="regular-text" /></p>
        <p><label><?= esc_html( __('Clé API privée', 'whitepage' ) ); ?></label><input type="text" name="zwhp_cleapiprivee" id="zwhp_cleapiprivee" value="<?= $zwhp_cleapiprivee; ?>" class="regular-text" /></p>
        <p><button class="button button-primary"><?= esc_html( __('Envoyer', 'whitepage' ) ); ?></button></p>
    </form>
</div>
