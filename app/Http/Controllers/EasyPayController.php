<?php

namespace App\Http\Controllers;

use App\Models\PayRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EasyPayController extends Controller
{
    public function pay(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
            'currency' => 'required|string',
        ]);

        //Definimos la transaccion

        $transaction = Transaction::create([
            'amount' => $request['amount'],
            'currency' => $request['currency'],
            'status' => 'pending',
        ]);

        //Revisar si hay errores en la peticion
        if($validator->fails()){

            //Actualizamos el estado de la transaccion y devolvemos los errores
            $transaction->update([
                'status' => 'failed',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $validate = $validator->validated();

        //Definimos la peticion

        $senditRequest = PayRequest::create([
            'request' => $validate,
            'status' => 'pending',
        ]);

        try {

            $payRequest = Http::post(env('EASY_PAY'), [
                'amount' => (integer) $validate['amount'],
                'currency' => $validate['currency'],
            ]);

            //Guardamos la respuesta de la peticion

            $senditRequest->update([
                'response' => $payRequest->json(),
                'status' => 'success',
            ]);

            if($payRequest->successful()){

                //Actualizamos el estado de la peticion y devolvemos un mensaje de exito
                $transaction->update([
                    'status' => 'success',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction successful',
                ], 200);
            }

            //Actualizamos la transaccion y devolvemos un error
            $transaction->update([
                'status' => 'failed',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed',
            ], 400);

        } catch (\Exception $e) {

            //Actualizamos el estado de la peticion y de la transaccion
            $senditRequest->update([
                'response' => $e->getMessage(),
                'status' => 'failed',
            ]);

            $transaction->update([
                'status' => 'failed',
            ]);

            //Devolvemos el error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}

