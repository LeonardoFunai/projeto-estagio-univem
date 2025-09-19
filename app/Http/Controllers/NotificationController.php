<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(10);

        // Marca todas as notificações não lidas como lidas ao visitar a página
        auth()->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
    public function read($id)
    {
        // Encontra a notificação específica do usuário logado
        $notification = Auth::user()->notifications()->find($id);

        // Se a notificação for encontrada, marque-a como lida
        if ($notification) {
            $notification->markAsRead();
        }

        // Redireciona para a URL contida na notificação (a página do projeto)
        // ou para o dashboard se não houver URL.
        return redirect($notification->data['url'] ?? route('dashboard'));
    }
}