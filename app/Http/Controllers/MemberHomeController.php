<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberHomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('member.home', [
            'user' => $request->user()->load('member'),
        ]);
    }
}
