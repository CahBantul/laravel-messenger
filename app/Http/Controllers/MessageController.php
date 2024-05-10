<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

class MessageController extends Controller
{
    public function index() : Response
    {
        return inertia('Chat/Index', [
            "users" => User::query()->get()
        ]);
    }
}
