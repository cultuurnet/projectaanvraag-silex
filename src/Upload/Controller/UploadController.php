<?php
namespace CultuurNet\ProjectAanvraag\Upload\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a controller to render widget pages and widgets.
 */
class UploadController
{

    /**
     */
    public function upload(Request $request)
    {
        // Required: anonymous function reference number as explained above.
        $funcNum = $_GET['CKEditorFuncNum'] ;
        // Optional: instance name (might be used to load a specific configuration file or anything else).
        $CKEditor = $_GET['CKEditor'] ;
        // Optional: might be used to provide localized messages.
        $langCode = $_GET['langCode'] ;
        // Optional: compare it with the value of `ckCsrfToken` sent in a cookie to protect your server side uploader against CSRF.
        // Available since CKEditor 4.5.6.
        //$token = $_POST['ckCsrfToken'] ;

        $upload = $_FILES['upload'];
        $filename = $upload['name'];
        $location = $upload['tmp_name'];

        $request->files->get('upload')->move('/var/www/projectaanvraag-api/web/assets/uploads/', $newName);
        $url = 'https://' . $_SERVER['HTTP_HOST']. '/assets/uploads/' . $newName;
        $message = '';

        return  "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";

    }
}
