<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::resource('doc_types', App\Http\Controllers\API\DocTypeAPIController::class);


// Route::resource('documentations', App\Http\Controllers\API\DocumentationAPIController::class);


// Route::resource('content_docs', App\Http\Controllers\API\ContentDocAPIController::class);

Route::prefix('v1')->group(function () {
    Route::get('consult/{nickname}/{platform}', function ($nickname, $platform) {
        switch ($platform) {
            case 'str':
                $url = 'https://stripchat.com/api/front/v2/models/username/'.$nickname.'/cam';
                $platform = 'STR';
                $response = Http::get($url);
                $json = $response->json();
                if (isset($json['error'])) {
                    return response()->json();
                } elseif (strlen($json['cam']['streamName']) <= 0) {
                    return response()->json();
                }
                break;
            case 'cht':
                $url = 'https://chaturbate.com/api/chatvideocontext/'.$nickname;
                $platform = 'CHT';
                $response = Http::get($url);
                $json = $response->json();
                if (!isset($json['hls_source'])) {
                    return response()->json();
                }
                break;
            default:
                return response()->json();
                break;
        }
        // DB::table('api_log')->insert([
        //     'nickname'  => $nickname,
        //     'plataform' => $platform,
        //     'origin'    => 'Consult',
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        return response()->json($json);
    });

    Route::get('stream/{nickname}/{platform}', function($nickname, $platform) {
        switch ($platform) {
            case 'str':
                $url = 'https://stripchat.com/api/front/v2/models/username/'.$nickname.'/cam';
                $response = Http::get($url);
                $json = $response->json();
                if (isset($json['error'])) {
                    return response()->json();
                } elseif (strlen($json['cam']['streamName']) <= 0) {
                    return response()->json();
                } elseif (strlen($json['cam']['streamName']) > 0) {
                    $hls = $json['cam']['viewServers']['flashphoner-hls'];
                    $streamName = $json['cam']['streamName'];
                    $url_stream = 'https://b-'.$hls.'.strpst.com/hls/'.$streamName.'/'.$streamName.'.m3u8';
                } else {
                    return response()->json();
                }
                break;
            case 'cht':
                $url = 'https://chaturbate.com/api/chatvideocontext/'.$nickname;
                $response = Http::get($url);
                $json = $response->json();
                if (isset($json['hls_source'])) {
                    $url_stream = $json['hls_source'];
                } else {
                    return response()->json();
                }
                break;
            default:
                return response()->json();
                break;
        }


        // DB::table('api_log')->insert([
        //     'nickname'  => $nickname,
        //     'plataform' => $platform,
        //     'origin'    => 'Stream',
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        return strval($url_stream);
    });
});
