<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{File, Http};
use App\Helpers\Hash\HashGenerator;
use DateTime;
use DateTimeZone;
use Kavenegar;
use Kavenegar\Exceptions\{ApiException, HttpException};

abstract class Controller
{
    public function uploadFile($uploadedFile, $uploadPath)
    {
        $uploaded_file_name = rand(1,9999).$uploadedFile->getClientOriginalName();
        $fileNameToStore = HashGenerator::make(10) . time() . '_' . $uploaded_file_name;
        // check dir exist
        if(!File::exists($uploadPath)){
            File::makeDirectory($uploadPath,0777,true,true);
        }

        $fileUploaded = $uploadedFile->move(public_path($uploadPath), $fileNameToStore);
        if ($fileUploaded) {
            return $fileNameToStore;
        } else {
            return null;
        }
    }


    public function sendOtp($otp_code,$receptor)
    {
        try {
            $url = "https://portal.amootsms.com/rest/SendQuickOTP";

            $url = $url."?"."Token=".urlencode(Env('AMOOT_OTP_SERVICE_TOKEN'));
            $url = $url."&"."Mobile=".$receptor;
            $url = $url."&"."CodeLength=4";
            $url = $url."&"."OptionalCode=".$otp_code;

            $json = file_get_contents($url);

            $result = json_decode($json);

            return $result->Status;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function sendOtpKaveNegar($otp,$receptor)
    {
        try{
            $sender = "200033155";
            $result = Kavenegar::VerifyLookup($receptor,$otp,'','','login');

            return true;
        }
        catch(ApiException $e){
            return $e->getMessage();
        }
        catch(HttpException $e){
            return $e->getMessage();
        }
    }


    // public function sendSmsWithPattern($patternCode,$receptorName,$receptorNumber){
    //     try {
    //         $response = Http::get('https://portal.amootsms.com/rest/SendWithPattern', [
    //             'Token'         => env('AMOOT_OTP_SERVICE_TOKEN'),
    //             'Mobile'        => $receptorNumber,
    //             'PatternCodeID' => $patternCode,
    //             'PatternValues' => $receptorName
    //         ]);

    //         $data = $response->json();

    //         if ($response->successful()) {
    //             return $data['Status'] ?? 'No Status';
    //         } else {
    //             return 'خطا در پاسخ سرور: ' . ($data['Message'] ?? 'خطای ناشناخته');
    //         }

    //     } catch (\Exception $e) {
    //         return 'خطای استثنا: ' . $e->getMessage();
    //     }

    // }

    public function sendOrderMessage($receptorName,$receptorNumber){
        try{
            $sender = "200033155";
            $message = $receptorName." عزیز،
سفارش شما در مخزن۲۴ ثبت شده و در حال پردازش است
با تشکر از خرید شما";
            // $receptor = array($receptorNumber);
            $result = Kavenegar::Send($sender,$receptorNumber,$message);

        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return 'خطا در پاسخ سرور: ' . ($e->errorMessage() ?? 'خطای ناشناخته');
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return 'خطای استثنا: ' . $e->getMessage();
        }

    }
}
