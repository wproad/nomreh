<?php

namespace Nomreh;

class Permissions{


    public function check_user_role($user_id) {
        $user = get_userdata($user_id);
        if ($user && in_array('customer', (array) $user->roles)) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'شما مجاز به ورود نیستید!'];

    }


}