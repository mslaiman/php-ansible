<?php
/*
 * This file is part of the php-ansible package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm\Ansible;

use Asm\Ansible\Command\AnsibleGalaxy;
use Asm\Ansible\Command\AnsibleGalaxyInterface;
use Asm\Ansible\Command\AnsiblePlaybook;
use Asm\Ansible\Command\AnsiblePlaybookInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Ansible command factory
 *
 * @package Asm\Ansible
 * @author Marc Aschmann <maschmann@gmail.com>
 */
final class Ansible
{

    /**
     * @var string
     */
    private $playbookCommand;

    /**
     * @var string
     */
    private $galaxyCommand;

    /**
     * @var string
     */
    private $ansibleBaseDir;

    /**
     * @param string $ansibleBaseDir base directory of ansible project structure
     * @param string $playbookCommand path to playbook executable, default ansible-playbook
     * @param string $galaxyCommand path to galaxy executable, default ansible-galaxy
     * @throws \ErrorException
     */
    public function __construct(
        $ansibleBaseDir,
        $playbookCommand = '',
        $galaxyCommand = ''
    ) {
        $this->ansibleBaseDir = $this->checkDir($ansibleBaseDir);
        $this->playbookCommand = $this->checkCommand($playbookCommand, 'ansible-playbook');
        $this->galaxyCommand = $this->checkCommand($galaxyCommand, 'ansible-galaxy');
    }

    /**
     * AnsiblePlaybook instance creator
     *
     * @return AnsiblePlaybookInterface
     */
    public function playbook()
    {
        $process = $this->createProcess($this->playbookCommand);

        return new AnsiblePlaybook(
            $process
        );
    }

    /**
     * AnsibleGalaxy instance creator
     *
     * @return AnsibleGalaxyInterface
     */
    public function galaxy()
    {
        $process = $this->createProcess($this->galaxyCommand);

        return new AnsibleGalaxy(
            $process
        );
    }

    /**
     * @param string $prefix command to execute
     * @return ProcessBuilder
     */
    private function createProcess($prefix)
    {
        $process = new ProcessBuilder();
        $process->setPrefix($prefix);

        return $process;
    }

    /**
     * @param string $command
     * @param string $default
     * @return string
     * @throws \ErrorException
     */
    private function checkCommand($command, $default)
    {
        // normally ansible is in /usr/local/bin/*
        if ('' == $command) {
            if (true == shell_exec('which ' . $default)) {
                $command = $default;
            } else {
                throw new \ErrorException('No ' . $default . ' executable present in PATH!');
            }
        } else {
            if (!is_file($command)) {
                throw new \ErrorException('Command ' . $command . ' does not exist!');
            }
            if (!is_executable($command)) {
                throw new \ErrorException('Command ' . $command . ' is not executable!');
            }
        }

        return $command;
    }

    /**
     * @param string $dir directory to check
     * @return string
     * @throws \ErrorException
     */
    private function checkDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \ErrorException('Ansible project root ' . $dir . ' not found!');
        }

        return $dir;
    }
}
