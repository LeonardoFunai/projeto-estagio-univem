<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 
use Illuminate\Foundation\Validation\ValidatesRequests;  
use Illuminate\Routing\Controller as BaseController;  
use App\Notifications\ResultadoEnviado;
use App\Notifications\ResultadoAvaliado;
use App\Models\RejeicaoResultado;
use Illuminate\Support\Facades\Notification;    

class Controller extends BaseController 
{
    use AuthorizesRequests, ValidatesRequests; 
}