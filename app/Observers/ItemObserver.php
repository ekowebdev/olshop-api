<?php

namespace App\Observers;

use App\Http\Models\User;
use App\Http\Models\Product;
use App\Notifications\NewItem;

class ItemObserver
{
    public function created(Product $item)
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new NewItem($item, User::find(auth()->user()->id)));
        }
    }
}