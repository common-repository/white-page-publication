<?php
/*
Plugin Name: WhitePage
Plugin URI: https://www.white.page
Description: Ce plugin permet à la plateforme white.page de publier sur votre site web wordpress
Author: ZAKSTAN
Version: 1.1.5
Author URI: https://www.myback.link
Licence: GPLv2 or later
*/

if( !defined('ABSPATH') )
    die('-1');

	
	
	
	add_action( 'rest_api_init', 'ZWHP_add_wp_whitepage_api');
	function ZWHP_add_wp_whitepage_api(){
          register_rest_route( 'whitep/v1', '/rest', array(
            'methods' => 'POST',
            'callback' => 'ZWHP_rest_callback',
        ));
    }
	
	add_action( 'rest_api_init', 'ZWHP_get_wp_whitepage_cat');
	function ZWHP_get_wp_whitepage_cat(){
          register_rest_route( 'whitep/v1', '/rest/cat', array(
            'methods' => 'POST',
            'callback' => 'ZWHP_whitep_rest_cat_callback',
        ));
    }
	
	add_action( 'rest_api_init', 'ZWHP_verify_api_whitepage');
	function ZWHP_verify_api_whitepage(){
          register_rest_route( 'whitep/v1', '/rest/verify', array(
            'methods' => 'POST',
            'callback' => 'ZWHP_whitep_verify_api_callback',
        ));
    }
	
	function ZWHP_whitep_verify_api_callback(WP_REST_Request $request){
		$zwhp_objson = $request->get_body_params();
		global $wpdb;
		$zwhp_retour = array(); 
		if(!empty($zwhp_objson)){
			$ZWHP_table_name = $wpdb->prefix.'whitepage';
			$rs = $wpdb->get_row( "SELECT * FROM {$ZWHP_table_name} WHERE id = 1", ARRAY_A );
			if(!empty($rs['cleapipublique']) AND !empty($rs['cleapiprivee'])){
				if(sanitize_text_field($zwhp_objson['cleapipublique']) == sanitize_text_field($rs['cleapipublique']) AND sanitize_text_field($zwhp_objson['cleapiprivee']) == sanitize_text_field($rs['cleapiprivee'])){
					$zwhp_retour["retour"] = 'succes';
					$zwhp_retour["message"] = sanitize_text_field('OK');
					echo json_encode($zwhp_retour);
					exit();
				}else{
					$zwhp_retour["retour"] = sanitize_text_field('echec');
					$zwhp_retour["message"] = sanitize_text_field('Clés API incorrectes');
					echo json_encode($zwhp_retour);
					exit();
				}
			}else{
				$zwhp_retour["retour"] = sanitize_text_field('echec');
				$zwhp_retour["message"] = sanitize_text_field('Clés API non renseignées sur le site WP');
				echo json_encode($zwhp_retour);
				exit();
			}
		}else{
			$zwhp_retour["retour"] = sanitize_text_field('echec');
			$zwhp_retour["message"] = sanitize_text_field('Données envoyées vides');
			echo json_encode($zwhp_retour);
			exit();
		}
		
		
	}
	
	function ZWHP_whitep_rest_cat_callback(WP_REST_Request $request){ 
		$zwhp_objson = $request->get_body_params();
		global $wpdb;
		$zwhp_retour = array();   
		
		if(!empty($zwhp_objson)){
			$whitepage_table_name = $wpdb->prefix.'whitepage';
			$rs = $wpdb->get_row( "SELECT * FROM {$whitepage_table_name} WHERE id = 1", ARRAY_A );
			if(!empty($rs['cleapipublique']) AND !empty($rs['cleapiprivee'])){
				if(($zwhp_objson['cleapipublique'] == $rs['cleapipublique']) AND ($zwhp_objson['cleapiprivee'] == $rs['cleapiprivee'])){
					if($zwhp_objson['typp'] == 'ListCategories'){
						$ZWHP_categories = get_categories( array(
							'orderby' => 'name',
							'hide_empty' => false,
							'order'   => 'ASC'
						) );
						
						$ttCat=count($ZWHP_categories);
						$ZWHP_listID='';
						$ZWHP_listName='';
						$td=';';
						$cpt=1;
						foreach( $ZWHP_categories as $ZWHP_category ) { 
							$ZWHP_listID .=$ZWHP_category->term_id; 
							$ZWHP_listName .=$ZWHP_category->name;
							
							if($cpt <$ttCat){
								$ZWHP_listID .=$td;
								$ZWHP_listName .=$td;
							}
							$cpt++;
						}  
						
						return array('Succes',$ZWHP_listID, $ZWHP_listName); 
					}else{ 
						return array(sanitize_text_field('Erreur connexion'),0, sanitize_text_field('Non classé'));
					}
				}else{ 
					return array(sanitize_text_field('Erreur : les clés de l\'API ne correspondent pas...'),0, sanitize_text_field('Non classé'));
				}
			}else{ 
				return array(sanitize_text_field('Erreur : Le site client ne dispose pas de clés API...'),0, sanitize_text_field('Non classé'));
			}
		}else{ 
			return array(sanitize_text_field('Erreur : Aucune données envoyées...'),0, sanitize_text_field('Non classé'));
		}
		 
		
		 
		
	}
	
	
	
					
function ZWHP_rest_callback(WP_REST_Request $request)	{
	$zwhp_objson = $request->get_body_params();
	global $wpdb;
	$zwhp_retour = array();  
	if(!empty($zwhp_objson)){
		$whitepage_table_name = $wpdb->prefix.'whitepage';
		$rs = $wpdb->get_row( "SELECT * FROM {$whitepage_table_name} WHERE id = 1", ARRAY_A );
		if(!empty($rs['cleapipublique']) AND !empty($rs['cleapiprivee'])){
			if(($zwhp_objson['cleapipublique'] == $rs['cleapipublique']) AND ($zwhp_objson['cleapiprivee'] == $rs['cleapiprivee'])){
				if($zwhp_objson['typp'] == 'img'){
					if(!empty($zwhp_objson['image'])){
						$zwhp_image_url        = $zwhp_objson['image'];
						$zwhp_image_name       = uniqid().'.jpeg';
						$zwhp_upload_dir       = wp_upload_dir(); // Set upload folder
						$zwhp_image_data       = file_get_contents($zwhp_image_url); // Get image data
						$zwhp_unique_file_name = wp_unique_filename( $zwhp_upload_dir['path'], $zwhp_image_name); // Generate unique name
						$zwhp_filename         = basename( $zwhp_unique_file_name); // Create image file name

						// Check folder permission and define file location
						if( wp_mkdir_p( $zwhp_upload_dir['path'] ) ) {
							$zwhp_file = $zwhp_upload_dir['path'] . '/' . $zwhp_filename;
						} else {
							$zwhp_file = $zwhp_upload_dir['basedir'] . '/' . $zwhp_filename;
						}

						// Create the image  file on the server
						file_put_contents( $zwhp_file, $zwhp_image_data);

						// Check image file type
						$zwhp_wp_filetype = wp_check_filetype( $zwhp_filename, null );

						// Set attachment data
						$zwhp_attachment = array(
							'post_mime_type' => $zwhp_wp_filetype['type'],
							'post_title'     => sanitize_file_name($zwhp_filename),
							'post_content'   => '',
							'post_status'    => 'inherit'
						);

						// Create the attachment
						$zwhp_attach_id = wp_insert_attachment( $zwhp_attachment, $zwhp_file, 1 );

						// Include image.php
						require_once(ABSPATH . 'wp-admin/includes/image.php');

						// Define attachment metadata
						$attach_data = wp_generate_attachment_metadata( $zwhp_attach_id, $zwhp_file );

						// Assign metadata to attachment
						wp_update_attachment_metadata( $zwhp_attach_id, $attach_data );

						$img_value = get_post_meta( $zwhp_attach_id, '', true );
						$src = wp_get_attachment_image_src( $zwhp_attach_id, 'full', false );
						
						if(isset($zwhp_objson['idPostwp']) && (!empty($zwhp_objson['idPostwp']))) {
							set_post_thumbnail( $zwhp_objson['idPostwp'], $zwhp_attach_id);
						}
						
						$zwhp_retour["retour"] = sanitize_text_field('succes');
						$zwhp_retour["idimg"] = $zwhp_attach_id;
						$zwhp_retour["urlimg"] = $src[0];
						$zwhp_retour["message"] = esc_html( __('Publication effectuée avec succès', 'whitepage' ) );

						echo json_encode($zwhp_retour);
						exit();

					}else{
						$zwhp_retour["retour"] = esc_html( __('echec', 'whitepage' ) );
						$zwhp_retour["message"] = esc_html( __('Image non envoyée', 'whitepage' ) );
						echo json_encode($zwhp_retour);
						exit();
					}
				}elseif($zwhp_objson['typp'] == 'postup'){
                                    $find = array( 
                                            '#<pre>#',
                                            '#</pre>#' 
                                    );

                                    $rempl = array(
                                            '',
                                            ''  
                                    );
				 
                                    $zwhp_post_id=$zwhp_objson['whppostid'];
                                    if(isset($zwhp_post_id) AND ($zwhp_post_id >0)){ 
                                        
                                        $my_post = array( 
                                            'ID' =>  $zwhp_post_id,
                                            'post_title'    => sanitize_text_field($zwhp_objson['title']),
                                            'post_content'  => preg_replace($find, $rempl, $zwhp_objson['content']),
                                            'post_status'   => 'publish', 
                                            'post_type' => 'post',
                                            'post_category' => array($zwhp_objson['category'])
                                         );

                                         wp_update_post( $my_post );
                                        
//                                        Modification des métas
                                        if(isset($zwhp_objson['whpmetatitle']) AND ($zwhp_objson['whpmetatitle']!="")){ 
                                            update_post_meta( $zwhp_post_id, '_yoast_wpseo_title',  $zwhp_objson['whpmetatitle'] );
                                            update_post_meta( $zwhp_post_id, '_yoast_wpseo_metadesc',  $zwhp_objson['whpmetadescription'] );

                                        } 
                                    } 
					
                                    $ran_already_flag = true; 
                                    $zwhp_retour["retour"] = 'succes';
                                    $zwhp_retour["idpost"] = $zwhp_post_id; 
                                    $zwhp_retour["message"] = esc_html('Publication effectuée avec succès');

                                    echo json_encode($zwhp_retour); 
                                    exit();  
					 
                            }else{
										$find = array( 
											'#<pre>#',
											'#</pre>#' 
										);

										$rempl = array(
											'',
											''  
										);
                                        if(!empty($zwhp_objson['typp'])){
                                            $zwhp_posttyp=$zwhp_objson['typp'];
                                            $zwhp_post_id=$zwhp_objson['whppostid']; 

											if(isset($zwhp_objson['slug']) AND !empty($zwhp_objson['slug'])){
												$slug=$zwhp_objson['slug'];
											}else{
												$slug=$zwhp_objson['title'];
											}

                                            if(trim($zwhp_posttyp)=="page"){

                                                if(isset($zwhp_post_id) AND ($zwhp_post_id >0)){
                                                    $zwhp_new_post = array(
                                                        'ID' =>  $zwhp_post_id,
                                                        'post_title' => sanitize_text_field($slug),
                                                        'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
                                                        'post_status' => 'publish',
                                                        'post_date' => $zwhp_objson['publication'],
                                                        'post_author' => 1,
                                                        'post_type' => trim($zwhp_posttyp) 
                                                    );
                                                }else{
                                                    $zwhp_new_post = array(
                                                        'post_title' => sanitize_text_field($slug),
                                                        'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
                                                        'post_status' => 'publish',
                                                        'post_date' => $zwhp_objson['publication'],
                                                        'post_author' => 1,
                                                        'post_type' => trim($zwhp_posttyp) 
                                                    );
                                                }
                                                
                                            }else{
                                                if(isset($zwhp_post_id) AND ($zwhp_post_id >0)){
                                                    $zwhp_new_post = array(
                                                        'ID' =>  $zwhp_post_id,
                                                        'post_title' => sanitize_text_field($slug),
                                                        'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
                                                        'post_status' => 'publish',
                                                        'post_date' => $zwhp_objson['publication'],
                                                        'post_author' => 1,
                                                        'post_type' => trim($zwhp_posttyp),
                                                        'post_category' => array($zwhp_objson['category'])
                                                    );
                                                }else{
                                                    $zwhp_new_post = array(
                                                        'post_title' => sanitize_text_field($slug),
                                                        'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
                                                        'post_status' => 'publish',
                                                        'post_date' => $zwhp_objson['publication'],
                                                        'post_author' => 1,
                                                        'post_type' => trim($zwhp_posttyp),
                                                        'post_category' => array($zwhp_objson['category'])
                                                    );  
                                                }
                                                
                                            }
                                        }else{
                                            $zwhp_posttyp='post';
                                            $zwhp_new_post = array(
                                                'post_title' => sanitize_text_field($slug),
                                                'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
                                                'post_status' => 'publish',
                                                'post_date' => $zwhp_objson['publication'],
                                                'post_author' => 1,
                                                'post_type' => trim($zwhp_posttyp),
                                                'post_category' => array($zwhp_objson['category'])
                                            );
                                        } 
                                        
//					$zwhp_new_post = array(
//						'post_title' => sanitize_text_field($zwhp_objson['title']),
//						'post_content' => preg_replace($find, $rempl, $zwhp_objson['content']),
//						'post_status' => 'publish',
//						'post_date' => $zwhp_objson['publication'],
//						'post_author' => 1,
//						'post_type' => trim($zwhp_posttyp),
//						'post_category' => array($zwhp_objson['category'])
//					);  
                                        
                                        if(isset($zwhp_post_id) AND ($zwhp_post_id >0)){
                                            wp_update_post($zwhp_new_post);
                                            $zwhp_post_id = $zwhp_post_id;
                                        }else{
                                            $zwhp_post_id = wp_insert_post($zwhp_new_post); 
											$zwhp_new_post = array(
												'ID' =>  $zwhp_post_id,
												'post_title' => sanitize_text_field($zwhp_objson['title']),
												
											);
                                        } 
										$zwhp_title_post = array(
											'ID' =>  $zwhp_post_id,
											'post_title' => sanitize_text_field($zwhp_objson['title']),
											
										);
										wp_update_post($zwhp_title_post);
					
									$metameass="";
									if(isset($zwhp_objson['whpmetatitle']) AND ($zwhp_objson['whpmetatitle']!="")){ 
                                            update_post_meta( $zwhp_post_id, '_yoast_wpseo_title',  $zwhp_objson['whpmetatitle'] );
                                                update_post_meta( $zwhp_post_id, '_yoast_wpseo_metadesc',  $zwhp_objson['whpmetadescription'] );
//                                            if ( is_plugin_active( 'wp-seopress/seopress.php' ) ) {
//                                                            //plugin seopress is activated
                                                update_post_meta( $zwhp_post_id, '_seopress_titles_title',  $zwhp_objson['whpmetatitle'] );
                                                update_post_meta( $zwhp_post_id, '_seopress_titles_desc',  $zwhp_objson['whpmetadescription'] );
//                                            }elseif(is_plugin_active( 'wordpress-seo/wp-seo.php' )) {
//                                                //plugin seopress is activated
//                                                update_post_meta( $zwhp_post_id, '_yoast_wpseo_title',  $zwhp_objson['whpmetatitle'] );
//                                                update_post_meta( $zwhp_post_id, '_yoast_wpseo_metadesc',  $zwhp_objson['whpmetadescription'] );
//                                            }else{
//                                                
//                                                $metameass ="Veuillez installer et activer le plugin YOAST SEO afin que les ";
//                                                update_post_meta( $zwhp_post_id, '_yoast_wpseo_title',  $zwhp_objson['whpmetatitle'] );
//                                                update_post_meta( $zwhp_post_id, '_yoast_wpseo_metadesc',  $zwhp_objson['whpmetadescription'] );
//                                            }
                                            
						
					}
					
					 
					
                                        $ran_already_flag = true;

                                        set_post_thumbnail( $zwhp_post_id, $zwhp_objson['idimg'] );
					$zwhp_post_data = get_post($zwhp_post_id);

					$zwhp_retour["retour"] = 'succes';
                                        $zwhp_retour["Plgact"] = $metameass;
					$zwhp_retour["idpost"] = $zwhp_post_id;
					$zwhp_retour["titrepost"] = sanitize_text_field($zwhp_post_data->post_title);
					$zwhp_retour["urlpost"] = esc_url($zwhp_post_data->guid);
					$zwhp_retour["datepost"] = $zwhp_post_data->post_date;
					$zwhp_retour["message"] = esc_html('Publication effectuée avec succès');

					echo json_encode($zwhp_retour); 
					exit(); 
				
					 
				}
			}else{
				$zwhp_retour["retour"] = esc_html( __('echec', 'whitepage' ) );
				$zwhp_retour["message"] = esc_html( __('Clés API incorrectes', 'whitepage' ) );
				echo json_encode($zwhp_retour);
				exit();
			}
		}else{
			$zwhp_retour["retour"] = esc_html( __('echec', 'whitepage' ) );
			$zwhp_retour["message"] = esc_html( __('Clés API non renseignées sur le site WP', 'whitepage' ) );
			echo json_encode($zwhp_retour);
			exit();
		}
	}else{
		$zwhp_retour["retour"] = esc_html( __('echec', 'whitepage' ) );
		$zwhp_retour["message"] = esc_html( __('Données envoyées vides', 'whitepage' ) );
		echo json_encode($zwhp_retour);
		exit();
	}

}
 
	
function ZWHP_add_db(){
    global $wpdb;
    $ZWHP_charset_collate = $wpdb->get_charset_collate();
    $ZWHP_table_name = $wpdb->prefix.'whitepage';

    $ZWHP_sql = "CREATE TABLE IF NOT EXISTS $ZWHP_table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        cleapipublique varchar(60) DEFAULT NULL,
        cleapiprivee varchar(60) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $ZWHP_charset_collate;";

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    dbDelta($ZWHP_sql);

    $wpdb->insert( $ZWHP_table_name,
        array(
            'cleapipublique' => '',
            'cleapiprivee' => ''
        ),
        array(
            '%s',
            '%s'
        )
    );
}


function ZWHP_del_db(){
    global $wpdb;
    $ZWHP_table_name = $wpdb->prefix.'whitepage';
    $ZWHP_sql = "DROP TABLE IF EXISTS $ZWHP_table_name";
    $wpdb->query($ZWHP_sql);
}



function ZWHP_adminMenu() {
    add_menu_page( 'White Page', 'White Page', 'manage_options', 'whitepage_dashboard', 'ZWHP_params_page', 'dashicons-edit', null );
}


function ZWHP_params_page(){
    require_once __DIR__.'/params_api_form.php';
}



add_action( 'admin_menu', 'ZWHP_adminMenu' );
register_activation_hook( __FILE__, 'ZWHP_add_db' );
register_deactivation_hook( __FILE__, 'ZWHP_del_db' );
