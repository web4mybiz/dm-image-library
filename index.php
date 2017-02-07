<?php
/*
Plugin Name: DM Image Library
Description: Divorced Moms image library 
*/

register_activation_hook( __FILE__, 'extract_dm_images' );
 
//add_action( 'init', 'extract_dm_images' );

// add the tab
add_filter('media_upload_tabs', 'my_upload_tab', 10, 1 );
function my_upload_tab($tabs) {
    $tabs['dmtab'] = "DM Images";
    //$tabs['library'] = "DM Library";
    return $tabs;
}

// call the new tab with wp_iframe
add_action('media_upload_dmtab', 'add_my_new_form');
function add_my_new_form() {
    wp_iframe( 'my_new_form' );
}

// the tab content
function my_new_form() {
    echo media_upload_header(); // This function is used for print media uploader headers etc.
    //echo extract_dm_images();
}


//if ( ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) ) || 0 === strpos( $content_func, 'media' ) )
        //wp_enqueue_style( 'media' );



function extract_dm_images(){
       
$dm_library_url=file_get_contents('http://divorcedmoms.com/Handlers/ImageApi.ashx?action=search&terms=1');

$dm_library_data = json_decode($dm_library_url);
$total_results =$dm_library_data ->TotalResults;
$total_pages =$dm_library_data ->TotalPages;

        for($i=0; $i<$total_pages;$i++){

             $dm_page_data=file_get_contents('http://divorcedmoms.com/Handlers/ImageApi.ashx?action=search&terms=1&page='.$i);
             //$dm_page_data=file_get_contents('http://divorcedmoms.com/Handlers/ImageApi.ashx?action=search&terms=1&page=0');
             $dm_data = json_decode($dm_page_data);

             for($j=0; $j<10;$j++){
              $img_src =$dm_data->Results[$j]->Src;
              //$dm_library.='<img src="'.$img_src.'" width="100" height="100">';
              import_dm_images($img_src);
             }

           }
//return $dm_library;
  }


 
function import_dm_images($image_url){

//get the image, and store it in your upload-directory
$filename=basename($image_url);
$uploaddir = wp_upload_dir();
$uploadfile = $uploaddir['path'] . '/' . $filename;

$contents= file_get_contents($image_url);
$savefile = fopen($uploadfile, 'w');
fwrite($savefile, $contents);
fclose($savefile);

// insert the image into the media library

$wp_filetype = wp_check_filetype(basename($filename), null );

$attachment = array(
    'post_mime_type' => $wp_filetype['type'],
    'post_title' => $filename,
    'post_content' => '',
    'post_status' => 'inherit'
);

$attach_id = wp_insert_attachment( $attachment, $uploadfile );

$imagenew = get_post( $attach_id );
$fullsizepath = get_attached_file( $imagenew->ID );
$attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
wp_update_attachment_metadata( $attach_id, $attach_data );


}


?>