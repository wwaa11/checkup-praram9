<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function Login(Request $request)
    {
        $type = $request->input('type');
        switch ($type) {
            case 'verify':
                $route = 'verify.index';
                break;

            default:
                $route = 'stations.index';
                break;
        }

        return view('login')->with(compact('route'));
    }

    public function LoginRequest(Request $request)
    {
        $userid   = $request->userid;
        $password = $request->password;
        $data     = [
            'status'  => 'failed',
            'message' => null,
        ];

        if (env('APP_ENV') == 'local') {
            $data['status']  = 'success';
            $data['message'] = 'เข้าสู่ระบบสำเร็จ';

            $userData = User::where('userid', $userid)->first();
            if ($userData == null) {
                $response = Http::withHeaders(['token' => env('API_AUTH_KEY')])
                    ->timeout(30)
                    ->post('http://172.20.1.12/dbstaff/api/getuser', [
                        'userid' => $request->userid,
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData['status'] == 1) {
                        $userData             = new User();
                        $userData->userid     = $userid;
                        $userData->name       = $responseData['user']['name'];
                        $userData->position   = $responseData['user']['position'];
                        $userData->department = $responseData['user']['department'];
                        $userData->division   = $responseData['user']['division'];
                        $userData->save();
                    }
                }
            }
            Auth::login($userData);

            return response()->json($data, 200);
        }

        try {
            $response = Http::withHeaders(['token' => env('API_AUTH_KEY')])
                ->timeout(30)
                ->post('http://172.20.1.12/dbstaff/api/auth', [
                    'userid'   => $request->userid,
                    'password' => $request->password,
                ]);

            if (! $response->successful()) {
                $data['message'] = 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง';
                return response()->json($data, 200);
            }
            $responseData = $response->json();

            if (! isset($responseData['status'])) {
                $data['message'] = 'ข้อมูลที่ได้รับจากระบบไม่ถูกต้อง';
                return response()->json($data, 200);
            }

        } catch (\Exception $e) {
            $data['message'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง';

            return response()->json($data, 200);
        }

        $data['message'] = 'ไม่พบรหัสพนักงานนี้';

        if ($responseData['status'] == 1) {
            $userData = User::where('userid', $request->userid)->first();
            if (! $userData) {
                $userData         = new User();
                $userData->userid = $userid;
            }
            $userData->name       = $responseData['user']['name'];
            $userData->position   = $responseData['user']['position'];
            $userData->department = $responseData['user']['department'];
            $userData->division   = $responseData['user']['division'];
            $userData->save();

            Auth::login($userData);

            $data['status']  = 'success';
            $data['message'] = 'เข้าสู่ระบบสำเร็จ';
        }

        return response()->json($data, 200);
    }

    public function LogoutRequest(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status'  => 'success',
            'message' => 'ออกจากระบบสำเร็จ',
        ], 200);
    }
}
