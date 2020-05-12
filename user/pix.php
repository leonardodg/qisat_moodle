<?PHP
require_once('../config.php');
global $DB, $CFG;
require_once($CFG->libdir.'/filelib.php');
$CFG->pixpath = $CFG->wwwroot .'/pix';

if ($CFG->forcelogin) {
    require_login();
    //if(!empty($CFG->forcelogin) AND !isloggedin())
    if (!isloggedin()) {
        redirect($CFG->pixpath.'/u/f1.png');
    }
}

// disable moodle specific debug messages
// disable_debugging();
$relativepath = get_file_argument('pix.php');
$args = explode('/', trim($relativepath, '/'));
if (count($args) == 2) {
    $userid   = (integer)$args[0];

    $sql = "SELECT * FROM {user} u WHERE u.id = $userid AND u.deleted = 0 AND u.picture > 0";
    if ($user = $DB->get_record_sql($sql)) {
        $image    = $args[1];
        $pathname = make_user_directory($userid, true) . "/$image";
        if (strrpos(".", $pathname) === false) {
            $extensoes = ['gif', 'jpe', 'jpeg', 'jpg', 'png', 'svg', 'svgz'];
            foreach($extensoes as $extensao){
                $pathname_extensao = $pathname . '.' . $extensao;
                if (file_exists($pathname_extensao) and !is_dir($pathname_extensao)) {
                    send_file($pathname_extensao, $image . '.' . $extensao);
                }
            }
        } else if (file_exists($pathname) and !is_dir($pathname)) {
            send_file($pathname, $image);
        }
    }
}
// picture was deleted - use default instead
redirect($CFG->pixpath.'/u/f1.png');

/**
 * Makes a directory for a particular user.
 *
 * @uses $CFG
 * @param int $userid The id of the user in question - maps to id field of 'user' table.
 * @param bool $test Whether we are only testing the return value (do not create the directory)
 * @return string|false Returns full path to directory if successful, false if not
 */
function make_user_directory($userid, $test=false) {
    global $CFG;
    if (is_bool($userid) || $userid < 0 || !preg_match('/^[0-9]{1,10}$/', $userid) || $userid > 2147483647) {
        if (!$test) {
            notify("Given userid was not a valid integer! (" . gettype($userid) . " $userid)");
        }
        return false;
    }
    // Generate a two-level path for the userid. First level groups them by slices of 1000 users, second level is userid
    $level1 = floor($userid / 1000) * 1000;
    $userdir = "user/$level1/$userid";
    if ($test) {
        return $CFG->dataroot . '/' . $userdir;
    } else {
        return make_upload_directory($userdir);
    }
}
?>
