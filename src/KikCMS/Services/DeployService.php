<?php

namespace KikCMS\Services;

use KikCMS\Config\KikCMSConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;

/**
 * @property Config $config
 * @property MailService $mailService
 */
class DeployService extends Injectable
{
    public function deploy()
    {
        // You can only deploy on production!
        if ($this->config->application->env != KikCMSConfig::ENV_PROD) {
            return;
        }

        $rootDir     = dirname($_SERVER['DOCUMENT_ROOT']);
        $composerDir = $rootDir . '/../../bin/';

        $composerCommand = 'php ' . $composerDir . 'composer update -n';
        $deployCommands  = 'git fetch origin && git reset --hard origin/master && ' . $composerCommand . ' 2>&1';

        // Execute deployment command
        putenv('COMPOSER_HOME=' . $composerDir);
        exec('cd ' . $rootDir . ' && ' . $deployCommands, $output);

        // Notify Webmaster
        $this->sendMessage($output);
    }

    /**
     * Send
     *
     * @param array $output
     */
    private function sendMessage($output)
    {
        $hostName    = $_SERVER['HTTP_HOST'];
        $serverName  = $_SERVER['SERVER_NAME'];
        $serverEmail = $_SERVER['SERVER_ADMIN'];

        $webmasterEmail = $this->config->application->webmasterEmail;
        $webmasterName  = $this->config->application->webmasterName;

        $output  = array_map("trim", $output);
        $subject = 'Deploy op ' . $hostName;
        $body    = 'Deploy uitgevoerd op ' . $hostName . " gaf de volgende output:\n\n" . implode(PHP_EOL, $output);

        $message = $this->mailService->createMessage()
            ->setSubject($subject)
            ->setFrom([$serverEmail => $serverName])
            ->setTo([$webmasterEmail => $webmasterName])
            ->setBody($body);

        // Send the message
        $this->mailService->send($message);
    }
}