<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return $request->user()->only([
            'id',
            'name',
            'email',
            'role',
            'billing_name',
            'billing_address',
            'billing_city',
            'billing_country',
            'billing_postal_code',
        ]);
    }
}
