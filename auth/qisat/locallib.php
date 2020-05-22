<?php

defined('MOODLE_INTERNAL') || die();

function update_password_all_users($authold){
    global $DB;

    $authnew = get_auth_plugin('qisat');
    $users = $DB->get_records('user', ['auth' => 'qisat'], 'id DESC', 'id,password');

    foreach ($users as $user) {
        $password = $authold->aes->decrypt(base64_decode($user->password));
        $user->password = base64_encode($authnew->aes->encrypt($password));
        $DB->update_record('user', $user);
    }
}
