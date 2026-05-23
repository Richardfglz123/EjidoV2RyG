<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function webhook(Request $request)
    {
        $mensaje = $request->input('queryResult.queryText');

        $apiKey = env('GEMINI_API_KEY');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=".$apiKey;

        $response = Http::post($url, [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => $mensaje
                        ]
                    ]
                ]
            ]
        ]);

        // VER RESPUESTA COMPLETA
        dd($response->json());
    }
}