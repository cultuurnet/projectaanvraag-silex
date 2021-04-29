<?php

namespace CultuurNet\ProjectAanvraag\Upload;

use Symfony\Component\HttpFoundation\Request;

class UploadController
{

    public function upload(Request $request)
    {
        $funcNum = $request->query->get('CKEditorFuncNum');
        $CKEditor = $request->query->get('CKEditor');
        $langCode = $request->query->get('langCode');
        $originalName = $request->files->get('upload')->getClientOriginalName();
        $newName =  explode(".", $originalName)[0] . time() . "." . explode(".", $originalName)[1];

        $request->files->get('upload')->move('/var/www/projectaanvraag-api/web/assets/uploads/', $newName);
        $url = 'https://' . $_SERVER['HTTP_HOST']. '/assets/uploads/' . $newName;
        $message = '';
        return  "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    }
}
