<?php

namespace App\Http\Controllers;

use App\Models\PayRequest;
use App\Models\Transaction;
use App\Models\TransactionWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SuperWalletzController extends Controller
{
    public function pay(Request $request){

        //Validamos la peticion
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);

        $transaction = Transaction::create([
            'amount' => $request['amount'],
            'currency' => $request['currency'],
            'status' => 'pending',
        ]);

        //Revisar si hay errores en la peticion
        if($validator->fails()){

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
            //Enviamos la peticion junto al callback

            $payRequest = Http::post(env('SUPER_WALLETZ'), [
                'amount' => (float) $request->amount,
                'currency' => $request->currency,
                'callback_url' => route('super-walletz.callback'),
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
                    'transaction_id' => $payRequest->json()['transaction_id'],
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

    public function callback(Request $request){

        //Guardamos los webhooks recibidos
        TransactionWebhook::create([
            'transaction_id' => $request->json('transaction_id') ?? '',
            'status' => $request->json('status') ?? '',
            'webhook' => $request->all(),
        ]);

        //Respondemos un mensaje de exito para SuperWalletz
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received',
        ], 200);
    }
}
