<?php
class Logger
{
    protected $filepath = '';

    public function __construct()
    {
        $dateStr = date('Ymd');
        $this->filepath = "../logs/$dateStr.log";
    }

    public function logError($err)
    {
        $logFile = $err->getFile();
        $logLine = $err->getLine();
        $logMsg = $err->getMessage();
        $logSessionId = session_id();
        $logTime = date('H:i:s');
        $logTrace = explode("\n", $err->getTraceAsString());

        $this->createLogsDir();

        error_log("[$logTime][$logSessionId] ERROR: $logMsg\n", 3, $this->filepath);
        error_log("[$logTime][$logSessionId] $logFile($logLine)\n", 3, $this->filepath);

        foreach ($logTrace as $logTraceLine) {
            error_log("[$logTime][$logSessionId] $logTraceLine\n", 3, $this->filepath);
        }
    }

    private function createLogsDir()
    {
        $logsDirPath = '../logs';

        if (!is_dir($logsDirPath)) {
            mkdir($logsDirPath);
        }
    }
}
?>