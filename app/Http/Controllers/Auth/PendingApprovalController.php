<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PendingApprovalController extends Controller
{
    /**
     * Show the pending approval page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('auth.pending-approval');
    }
}

