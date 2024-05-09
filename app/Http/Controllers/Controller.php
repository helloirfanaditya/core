<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    const RESPONSE_SUCCESS = true;
    const RESPONSE_ERROR = false;

    protected function jsonRes($message,$status = self::RESPONSE_ERROR,$responseCode = 400,$data = null)
    {
        $msg = 'failed';
        $data = $message;
        if($status == self::RESPONSE_SUCCESS) {
            $responseCode = 200;
            $msg = 'success';
            $data = $message;
        }

        $response = [
            'code' => (string) $responseCode,
            'status' => $status,
            'message' => $msg,
            'data' => $data
        ];

        return response()->json($response,$responseCode);
    }

    public function core()
    {
        try {
            $res = Http::withHeaders([
                'X-Api-Key' => 'PnIAFcJDXiz6aSsxLq2bXA==wBOIU1xLHc2j3MIb',
            ])->get("https://api.api-ninjas.com/v1/quotes?category=love");

            $response = json_decode($res->getBody()->getContents());
            $quote = $response[0]->quote;

        } catch (\Throwable $th) {
            $quote = 'hello';
        }

        return $this->jsonRes($quote,self::RESPONSE_SUCCESS);
    }
}
