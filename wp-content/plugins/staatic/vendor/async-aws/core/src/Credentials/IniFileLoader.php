<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core\Credentials;

use Staatic\Vendor\AsyncAws\Core\EnvVar;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\NullLogger;
final class IniFileLoader
{
    const KEY_REGION = 'region';
    const KEY_ACCESS_KEY_ID = 'aws_access_key_id';
    const KEY_SECRET_ACCESS_KEY = 'aws_secret_access_key';
    const KEY_SESSION_TOKEN = 'aws_session_token';
    const KEY_ROLE_ARN = 'role_arn';
    const KEY_ROLE_SESSION_NAME = 'role_session_name';
    const KEY_SOURCE_PROFILE = 'source_profile';
    const KEY_WEB_IDENTITY_TOKEN_FILE = 'web_identity_token_file';
    private $logger;
    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }
    public function loadProfiles(array $filepaths) : array
    {
        $profilesData = [];
        $homeDir = null;
        foreach ($filepaths as $filepath) {
            if ('' === $filepath) {
                continue;
            }
            if ('~' === $filepath[0]) {
                $homeDir = $homeDir ?? $this->getHomeDir();
                $filepath = $homeDir . \substr($filepath, 1);
            }
            if (!\is_readable($filepath) || !\is_file($filepath)) {
                continue;
            }
            foreach ($this->parseIniFile($filepath) as $name => $profile) {
                $name = \preg_replace('/^profile /', '', (string) $name);
                if (!isset($profilesData[$name])) {
                    $profilesData[$name] = \array_map('trim', $profile);
                } else {
                    foreach ($profile as $k => $v) {
                        if (!isset($profilesData[$name][$k])) {
                            $profilesData[$name][$k] = \trim($v);
                        }
                    }
                }
            }
        }
        return $profilesData;
    }
    private function getHomeDir() : string
    {
        if (null !== ($homeDir = EnvVar::get('HOME'))) {
            return $homeDir;
        }
        $homeDrive = EnvVar::get('HOMEDRIVE');
        $homePath = EnvVar::get('HOMEPATH');
        return $homeDrive && $homePath ? $homeDrive . $homePath : '/';
    }
    private function parseIniFile(string $filepath) : array
    {
        if (\false === ($data = \parse_ini_string(\preg_replace('/^#/m', ';', \file_get_contents($filepath)), \true, \INI_SCANNER_RAW))) {
            $this->logger->warning('The ini file {path} is invalid.', ['path' => $filepath]);
            return [];
        }
        return $data;
    }
}
