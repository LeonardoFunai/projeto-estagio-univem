<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(10);
        auth()->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
    public function read($id)
    {

        $notification = Auth::user()->notifications()->find($id);


        if ($notification) {
            $notification->markAsRead();
        }


        return redirect($notification->data['url'] ?? route('dashboard'));
    }
}