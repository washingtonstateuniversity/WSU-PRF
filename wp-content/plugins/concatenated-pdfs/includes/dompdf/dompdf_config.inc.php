<?php
PHP_VERSION >= 5.0 or die("DOMPDF requires PHP 5.0+");
define("DOMPDF_DIR", str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__))));
define("DOMPDF_INC_DIR", DOMPDF_DIR . "/include");
define("DOMPDF_LIB_DIR", DOMPDF_DIR . "/lib");
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    $path = "";
    if (isset($_SERVER['SCRIPT_FILENAME']))
        $path = $_SERVER['SCRIPT_FILENAME'];
    elseif (isset($_SERVER['PATH_TRANSLATED']))
        $path = str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']);
    $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($path, 0, 0 - strlen($_SERVER['PHP_SELF'])));
}
if (file_exists(DOMPDF_DIR . "/dompdf_config.custom.inc.php")) {
    require_once(DOMPDF_DIR . "/dompdf_config.custom.inc.php");
}
require_once(DOMPDF_INC_DIR . "/functions.inc.php");
def("DOMPDF_ADMIN_USERNAME", "user");
def("DOMPDF_ADMIN_PASSWORD", "password");
def("DOMPDF_FONT_DIR", DOMPDF_DIR . "/lib/fonts/");
def("DOMPDF_FONT_CACHE", DOMPDF_FONT_DIR);
def("DOMPDF_TEMP_DIR", DOMPDF_DIR.'/tmp/');
def("DOMPDF_CHROOT", realpath(DOMPDF_DIR));
def("DOMPDF_UNICODE_ENABLED", true);
def("DOMPDF_ENABLE_FONTSUBSETTING", false);
def("DOMPDF_PDF_BACKEND", "CPDF");
def("DOMPDF_DEFAULT_MEDIA_TYPE", "screen");
def("DOMPDF_DEFAULT_PAPER_SIZE", "letter");
def("DOMPDF_DEFAULT_FONT", "serif");
def("DOMPDF_DPI", 96);
def("DOMPDF_ENABLE_PHP", true);
def("DOMPDF_ENABLE_JAVASCRIPT", true);
def("DOMPDF_ENABLE_REMOTE", true);
def("DOMPDF_LOG_OUTPUT_FILE", DOMPDF_DIR. "/logs/log.htm");
def("DOMPDF_FONT_HEIGHT_RATIO", 1.1);
def("DOMPDF_ENABLE_CSS_FLOAT", false);
def("DOMPDF_ENABLE_AUTOLOAD", true);
def("DOMPDF_AUTOLOAD_PREPEND", false);
def("DOMPDF_ENABLE_HTML5PARSER", true);
require_once(DOMPDF_LIB_DIR . "/html5lib/Parser.php");
if (DOMPDF_ENABLE_AUTOLOAD) {
    require_once(DOMPDF_INC_DIR . "/autoload.inc.php");
    require_once(DOMPDF_LIB_DIR . "/php-font-lib/classes/font.cls.php");
}
mb_internal_encoding('UTF-8');
global $_dompdf_warnings;
$_dompdf_warnings = array();
global $_dompdf_show_warnings;
$_dompdf_show_warnings = true;
global $_dompdf_debug;
$_dompdf_debug = true;
global $_DOMPDF_DEBUG_TYPES;
$_DOMPDF_DEBUG_TYPES = array();
def('DEBUGPNG', true);
def('DEBUGKEEPTEMP', true);
def('DEBUGCSS', true);
def('DEBUG_LAYOUT', false);
def('DEBUG_LAYOUT_LINES', true);
def('DEBUG_LAYOUT_BLOCKS', true);
def('DEBUG_LAYOUT_INLINE', true);
def('DEBUG_LAYOUT_PADDINGBOX', true);
