<?php
header('Content-Type: application/json');
if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) {
    foreach (debug_backtrace() as $error) {
        if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) {
            $message = array('file' => $error['file'],
                'line' => $error['line'],
                'function' => $error['function']) ;
        }
    }
}
echo json_encode(array('status'=>false, 'message'=>$message));

