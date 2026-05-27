<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function index(): JsonResponse
    {

        $exchanges = Exchange::all();

        return response()->json([
            'status' => 'success',
            'data' => $exchanges,
        ]);
    }
}
