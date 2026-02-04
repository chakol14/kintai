<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        // 一般ユーザー（role='user'）を取得
        $users = User::where('role', 'user')->get();
        
        return view('admin.staff', [
            'users' => $users,
        ]);
    }
}
