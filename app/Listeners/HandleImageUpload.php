<?php

// namespace App\Listeners;

// use App\Events\ClientCreated;
// use App\Events\UserCreated;
// use App\Jobs\StoreImageInCloud;

// class HandleImageUpload
// {
//     public function handle(UserCreated $event)
//     {
//         // Dispatcher le job pour stocker l'image dans le cloud
//         StoreImageInCloud::dispatch($event->user, $event->photoPath);
//     }
// }


namespace App\Listeners;

use App\Events\ClientCreated;
use App\Events\UserCreated;
use App\Jobs\StoreImageInCloud;

class HandleImageUpload
{
    public function handle(UserCreated $event)
    {
        // Dispatcher le job pour stocker l'image dans le cloud
        StoreImageInCloud::dispatch($event->user, $event->photoPath);
    }
}


// namespace App\Listeners;

// use App\Events\ClientCreated;
// use App\Events\UserCreated;
// use App\Jobs\StoreImageInCloud;

// class HandleImageUpload
// {
//     public function handle(ClientCreated $event)
//     {
//         // Dispatcher le job pour stocker l'image dans le cloud
//         StoreImageInCloud::dispatch($event->client, $event->photoFilePath);
//     }
// }