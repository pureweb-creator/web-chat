<?php

namespace Web\Controllers\Traits;

trait CheckData
{
    private function is_empty_data($data, $err_code): array
    {
        if (empty($data)) {
            $this->response[$err_code] = true;
            $this->response["message"] = $this->validation[$err_code];
        }
        return $this->response;
    }

    private function is_wrong_code($email, $code): array
    {   
        $user = $this->load_user($email);

        if ($user->confirmation_code !== $code){
            $this->response["is_wrong_code"] = true;
            $this->response["message"] = $this->validation["is_wrong_code"];
            return $this->response;
        }

        return $this->response;
    }

    private function is_wrong_email($email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response["wrong_email"] = true;
            $this->response["message"] = $this->validation["wrong_email"];
        }

        return $this->response;
    }

    private function is_user_exists($email)
    {
        if ($this->load_user($email)){
            $this->response['user_exists'] = true;
            $this->response['message'] = $this->validation['user_exists'];
            return $this->response;
        }

        $this->response['user_not_exists'] = true;
        $this->response['message'] = $this->validation['user_not_exists'];
        return false;
    }

}