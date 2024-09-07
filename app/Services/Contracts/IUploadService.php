<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface IUploadService
{


    public function upload(UploadedFile $file);
}
