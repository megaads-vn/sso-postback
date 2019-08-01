<?php 
namespace Megaads\SsoPostback\Controllers;

use Illuminate\Routing\Controller as BaseController;

class SsoPostbackController extends BaseController 
{
    public function __construct()
    {
        
    }

    public function ssoPostback() {
        $retval = [
            'status' => 'fail'
        ];
        $checkDns = $this->checkDnsCallService();
        if ( !$checkDns ) {
            $retval['message'] = 'Invalid domain! Please check again.';
            return \Response::json($retval);
        }

        if ( !Input::has('email') || !Input::has('active') || !Input::has('username')) {
            $retval['message'] = 'Invalid param email or active. Please check again!';
        } else {
            $email = Input::get('email');
            $active = Input::get("active");
            $user = User::whereRaw("replace(`email`, '.', '') = replace('$email', '.', '')")->first();

            if (!$user) {
                $retval['msg'] = "Email doesn't exist.";
                if (!$active) {
                    $retval['status'] = 'successful';
                    $retval['msg'] = "Email doesn't exist.";
                } else {
                    if (User::where('username', Input::get("username"))->first()) {
                        User::insert([
                            'email' => $email,
                            'username' => Input::get("username") . mt_rand(100,999),
                            'type' => 'staff',
                            'full_name' => Input::get('full_name', ''),
                            'code' => Input::get('code', ''),
                            'status' => User::STATUS_ACTIVE,
                        ]);
                    } else {
                        User::insert([
                            'email' => $email,
                            'username' => Input::get("username"),
                            'type' => 'staff',
                            'full_name' => Input::get('full_name', ''),
                            'code' => Input::get('code', ''),
                            'status' => User::STATUS_ACTIVE,
                        ]);
                    }

                    $retval['status'] = 'successful';
                    $retval['msg'] = "Account created successfully with email $email";
                }
            } else {
                $status = $active ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
                $user->update(['status' => $status]);
                $retval['status'] = 'successful';
                $retval['msg'] = "Update user's status to $status";
            }
        }

        return \Response::json($retval);
    }

    private function checkDnsCallService() {
        $retval = true;
        $dns = dns_get_record("id.megaads.vn", DNS_A);
        if ($dns) {
          $currentIp = $_SERVER['REMOTE_ADDR'];
          $ssoIp = $dns[0]['ip'];
          if ($ssoIp != $currentIp) {
              \Log::error("Ip $currentIp dang truy cap trai phep");
              $retval = false;
          }
        } else {
          \Log::error("Khong the phan giai duoc ten mien id.megaads.vn");
          $retval = false;
        }
        return $retval;
    }
}