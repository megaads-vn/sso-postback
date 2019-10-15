<?php 
namespace Megaads\SsoPostback\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SsoPostbackController extends BaseController 
{
    public function __construct()
    {
        
    }

    public function ssoPostback() {
        $retval = [
            'status' => 'fail'
        ];
        
        $configUserTable = \Config::get('sso-postback.user_table');
        $configTableColumn = \Config::get('sso-postback.user_account_column');
        if ( !\Config::get('sso-postback.debug') ) {
            $checkDns = $this->checkDnsCallService();
            if ( !$checkDns ) {
                $retval['message'] = 'Invalid domain! Please check again.';
                return \Response::json($retval);
            }
        }

        if ( !Schema::hasTable($configUserTable) ) {
            $retval['message'] = 'Invalid table name. Please check configuration file.';
            return \Response::json($retval);
        }
        $tableColumns = DB::getSchemaBuilder()->getColumnListing($configUserTable);
        if ( !Input::has('email') || !Input::has('status') || !Input::has('username')) {
            $retval['message'] = 'Invalid param email or status. Please check again!';
        } else {
            $email = Input::get('email');
            $active = Input::get("status");
            $username = Input::get("username");
            $user = DB::table($configUserTable)->whereRaw("replace(`email`, '.', '') = replace('$email', '.', '')")->first();

            if (!$user) {
                $retval['msg'] = "Email doesn't exist.";
                if (!$active) {
                    $retval['status'] = 'successful';
                    $retval['msg'] = "Email doesn't exist.";
                } else {
                    $insertParams = $this->buildInsertData($tableColumns);
                    if ( $configTableColumn == 'email' ) {
                        DB::table($configUserTable)->insert($insertParams);
                    } else {
                        $checkUser = DB::table($configUserTable)->where("username", $username)->first();
                        if ($checkUser) {
                            $insertParams['username'] = $username . mt_rand(100,999);
                            DB::table($configUserTable)->insert($insertParams);
                        } else {
                            DB::table($configUserTable)->insert($insertParams);
                        }
                    }
                    $retval['status'] = 'successful';
                    $retval['msg'] = "Account created successfully with email $email";
                }
            } else {
                $status = $active ? \Config::get('sso-postback.active_status') : \Config::get('sso-postback.inactive_status');
                DB::table($configUserTable)->whereRaw("replace(`email`, '.', '') = replace('$email', '.', '')")->update(['status' => $status]);
                $retval['status'] = 'successful';
                $retval['msg'] = "Update user's status to $status";
            }
        }

        return \Response::json($retval);
    }

    private function buildInsertData($tableColumns) {
        unset($tableColumns[0]);
        $mapColumn = \Config::get('sso-postback.map');
        $buildData = [
            'remember_token' => ''
        ];
        foreach($tableColumns as $column) {
            $params = [];
            $getColum = $column;
            if ( in_array($column, $mapColumn) ) {
                $getColum = array_search($column, $mapColumn);
            }
            $params[$column] = Input::get($getColum, '');
            $buildData = $buildData + $params;
        }
        return $buildData;
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