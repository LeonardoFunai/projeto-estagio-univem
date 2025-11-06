<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;

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
    public function downloadZip(Request $request, $filename, DatabaseNotification $notification)
    {
        // 1. Caminho do arquivo no disco
        $path = 'lotes-pdf/' . $filename;
        
        // 2. Verifica a existência e permissão
        if (!Storage::disk('public')->exists($path)) {
            // Se o arquivo não existir (404 se não for encontrado)
            return response()->abort(404, 'O arquivo de download não foi encontrado ou expirou.');
        }

        // 3. Marcar a notificação como lida
        $notification->markAsRead();

        // 4. Forçar o download através do Laravel
        return Storage::disk('public')->download($path, $filename);
    }
}