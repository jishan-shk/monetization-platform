<?php
if (!defined('SCRIPT_VERSION')) {
    define("SCRIPT_VERSION", (env('APP_ENV') != 'prod') ? rand() : '0.0.1');

    define("BAD_REQUEST_CODE", 400);
    define("UNAUTHORIZED_ACCESS_CODE", 401);
    define("INTERNAL_SERVER_ERROR_CODE", 500);
    define("BAD_GATEWAY_CODE", 502);
    define("UNPROCESSABLE_CONTENT_CODE", 422);
    define("TOO_MANY_ATTEMPT_CODE", 429);
    define("SUCCESS_REQUEST_CODE", 200);

    define("SOMETHING_ERROR_MESSAGE", 'Something went wrong, please try again after sometime');
    define("INTERNAL_SERVER_ERROR_MSG", 'Internal Server Error');
    define("UNAUTHORIZED_ERROR_MSG", 'Unauthorized Access');
    define("VALIDATION_ERROR_MSG", 'Validation Error');
    define('TOO_MANY_ATTEMPT_MESSAGE', "429 Too Many Requests.");
}
