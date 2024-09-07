<?Php
// app/Services/UploadService.php

// namespace App\Services;

// use App\Services\Contracts\IUploadService;
// use Illuminate\Http\UploadedFile;
// // use Symfony\Component\HttpFoundation\File\UploadedFile;
// use Illuminate\Support\Facades\Storage;

// class UploadService implements IUploadService
// {

//     public function upload(UploadedFile $file)
//     {
//         $path = $file->store('images', 'public');
//         return Storage::url($path);
//     }

//     public function delete(UploadedFile $file)
//     {
//         $path = $file->store('images', 'public');
//         return Storage::delete($path);
//     }

//     public function getUrl(UploadedFile $file)
//     {
//         $path = $file->store('images', 'public');
//         return Storage::url($path);
//     }

//     public function uploadAndEncode(UploadedFile $file): array
//     {
//          // Store the file
//             $path = $file->store('images', 'public');

//             // Retrieve the file's content
//             $fileContent = Storage::disk('public')->get($path);

//             // Encode the file's content in Base64
//             $base64Content = base64_encode($fileContent);

//             return [
//                 'path' => $path,
//                 'base64' => $base64Content,
//             ];
//         }
//     }


