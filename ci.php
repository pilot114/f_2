#!/usr/bin/php
<?php

/**
 * ##################################################################
 * Скрипт для выполнения сборки в jenkins или любом другом CI сервисе.
 * Предполагается выполнение сразу после клонирования
 * в рабочую директорию системы сборки (например, по web-hook)
 *
 * Контекстные данные извлекаются из переменных окружения
 *
 * Скрипт включает в себя:
 * - создание билда и его отправка на сервер
 * - переключение между релизами
 * - release rotation (удаление старых релизов)
 *
 * ##################################################################
 * Переключение между релизами возможно локальным выполнением:
 *
 * php ci.php ENV_NAME COMMAND
 *
 * ENV_NAME = beta | prod
 * COMMAND = ls (список релизов) | release | last | next | prev
 *
 * Без аргументов будет выведена информация по текущим релизам
 *
 * ##################################################################
 * Также есть возможность развернуть на персональное тестовое окружение
 *
 * DOMAIN={NAME} php ci.php
 *
 * NAME - имя поддомена, например chulkov => chulkov-cp.siberianhealth.com
 */

class CommandManager
{
    protected ?string $host = null;
    protected ?string $user = null;
    protected ?string $path = null;

    public function __construct($isProd = false, $user = 'portal_ci', $path = '/var/www/builds')
    {
        $this->host = $isProd ? '192.168.5.49' : '192.168.5.39';
        $this->user = $user;
        $this->path = $path;
    }

    public function exec($command, $split = false): string|array|null
    {
        $time = (new DateTime())->format("d-M-Y h:i:s");
        echo sprintf("%s %s@%s # %s\n", $time, $this->user, $this->host, $command);
        $result = shell_exec(
            sprintf('ssh %s@%s "cd %s && %s"', $this->user, $this->host, $this->path, $command)
        );
        return $split ? array_filter(explode("\n", $result)) : $result;
    }

    public function copy(string $from, string $to): array
    {
        $time = (new DateTime())->format("d-M-Y h:i:s");
        $command = sprintf("scp -rp %s %s@%s:%s", $from, $this->user, $this->host, $this->path . '/' . $to);
        echo sprintf("%s %s\n", $time, $command);
        $result = shell_exec($command);
        return array_filter(explode("\n", $result));
    }

    public function rsync(string $from, string $to): void
    {
        $time = (new DateTime())->format("d-M-Y h:i:s");
        $command = sprintf(
                "rsync --exclude='var/cache/' --exclude='var/log/' -az --delete %s %s@%s:%s",
                $from, $this->user, $this->host, $this->path . '/' . $to
        );
        echo sprintf("%s %s\n", $time, $command);
        shell_exec($command);
    }

    /** @return array{0: int, 1: array<int>} */
    public function getInfo(): array
    {
        $filePath = $this->exec('readlink -f current', true)[0] ?? null;
        if ($filePath === null) {
            throw new LogicException('Unable get info');
        }
        $currentId = (int)basename($filePath);
        $releases = array_filter($this->exec('ls', true), fn($x) => is_numeric($x));
        $releases = array_map(intval(...), $releases);
        return [$currentId, $releases];
    }

    static function localExec($command): array
    {
        $time = (new DateTime())->format("d-M-Y h:i:s");
        echo sprintf("%s %s\n", $time, $command);
        exec($command, $output);
        return $output;
    }
}

class SymlinkManager
{
    public function __construct(
        protected CommandManager $cli,
    ){}

    public function handle(string $command, ?string $releaseId): void
    {
        [$currentId, $releases] = $this->cli->getInfo();

        if ($command === 'ls') {
            echo "Список релизов:\n";
            foreach ($releases as $release) {
                if ($currentId == $release) {
                    echo "> $release\n";
                } else {
                    echo "- $release\n";
                }
            }
        }
        if ($command === 'last') {
            echo "Переключение на последний релиз:\n";

            $lastReleaseId = end($releases);
            if ($lastReleaseId === $currentId) {
                echo "Уже выбран последний релиз\n";
            } else {
                $this->cli->exec(sprintf('ln -nsf %s current', $lastReleaseId), true);
                echo sprintf("%s => %s\n", $currentId, $lastReleaseId);
            }
        }
        if ($command === 'prev') {
            echo "Переключение на предыдущий релиз:\n";

            foreach ($releases as $i => $release) {
                if ($currentId == $release) {
                    if (isset($releases[$i - 1])) {
                        $prevId = $releases[$i - 1];
                        $this->cli->exec(sprintf('ln -nsf %s current', $prevId), true);
                        echo sprintf("%s => %s\n", $currentId, $prevId);
                    } else {
                        echo "Нет предыдущего релиза\n";
                    }
                }
            }
        }
        if ($command === 'next') {
            echo "Переключение на следующий релиз:\n";

            foreach ($releases as $i => $release) {
                if ($currentId == $release) {
                    if (isset($releases[$i + 1])) {
                        $nextId = $releases[$i + 1];
                        $this->cli->exec(sprintf('ln -nsf %s current', $nextId), true);
                        echo sprintf("%s => %s\n", $currentId, $nextId);
                    } else {
                        echo "Нет следующего релиза\n";
                    }
                }
            }
        }
        if ($command === 'release' && $releaseId) {
            echo sprintf("Переключение на релиз %s:\n", $releaseId);

            foreach ($releases as $i => $release) {
                if ($releaseId == $release) {
                    $this->cli->exec(sprintf('ln -nsf %s current', $releaseId), true);
                    echo sprintf("%s => %s\n", $currentId, $releaseId);
                    exit;
                }
            }
            echo sprintf("Не найден релиз %s:\n", $releaseId);
        }
    }
}

class Deployer
{
    public function sync(bool $isProd, string $deployUser, int $buildId): void
    {
        $rm = new CommandManager($isProd, $deployUser);
        [$currentId, $releases] = $rm->getInfo();

        // создаем и заливаем билд
        CommandManager::localExec("php /var/www/html/composer.phar dump-env " . ($isProd ? 'prod' : 'test'));
        CommandManager::localExec("tar -cf $buildId.tar ./* .env.local.php");
        $rm->exec("mkdir $buildId");
        $rm->copy("$buildId.tar", "$buildId/$buildId.tar");
        $rm->exec("cd $buildId && tar -xf $buildId.tar && rm $buildId.tar && chmod -Rf 770 ./");
        CommandManager::localExec("rm *.tar");

        // переключаем симлинк
        $rm->exec(sprintf('ln -nsf %s current', $buildId));
        echo sprintf("%s => %s\n", $currentId, $buildId);

        $this->refreshState($rm);

        // билды перед текущим, которые нужно сохранить
        $limitBuilds = 2;
        // удаляем старые релизы
        if (count($releases) > $limitBuilds) {
            asort($releases);
            $releases = array_values($releases);
            $releasesForRemove = array_slice($releases, 0, count($releases) - $limitBuilds);

            foreach ($releasesForRemove as $releaseForRemove) {
                $rm->exec("rm -Rf $releaseForRemove 2> /dev/null || true");
            }
        }
    }

    public function personalSync(CommandManager $rm, string $domain): void
    {
        echo ("Deploying personal back2...\n");
        CommandManager::localExec("cd back2 && cp .env.local .env.test.local && composer dump-env test");
        $rm->rsync("/var/www/back2/*", $domain);
        $rm->copy("/var/www/back2/.env*", $domain);

        $rm->exec("cd $domain && chmod -Rf 770 ./");
        $rm->exec("cd $domain && rm -R var/cache/*");

        $this->refreshState($rm, isNeedClearOpcache: false);

        CommandManager::localExec("cd back2 && rm .env.local.php .env.test.local");

        echo ("Deploying personal back2 finished!\n");
    }

    private function refreshState(CommandManager $rm, bool $isNeedClearOpcache = true): void
    {
        if ($isNeedClearOpcache) {
            $rm->exec("curl -sO https://gordalina.github.io/cachetool/downloads/cachetool-3.2.2.phar");
            $rm->exec("chmod +x cachetool-3.2.2.phar");
            $rm->exec("php cachetool-3.2.2.phar opcache:reset --fcgi=/var/run/php/php8.2-fpm.sock");
            $rm->exec("rm cachetool-3.2.2.phar");
            $rm->exec("sudo /usr/sbin/service php8.2-fpm reload");
        }

        $output = CommandManager::localExec('git status --porcelain prod.cp.conf');
        if ($output !== []) {
            $rm->exec("sudo /usr/sbin/service nginx reload");
        }

        $rm->exec("redis-cli flushall");
    }
}

/////////////////////////////

// режим сборки
$buildId = (int) getenv('CI_PIPELINE_IID');
if ($buildId) {
    $branch = getenv('CI_COMMIT_BRANCH');
    $deployUser = getenv('SSH_DEPLOY_USER') ?: 'portal_ci';

    $stage = str_contains($branch, 'main') ? 'prod' : 'beta';
    $isProd = ($stage === 'prod');

    (new Deployer())->sync($isProd, $deployUser, $buildId);
    exit;
}

/////////////////////////////

// режим выкладки персонального окружения
$domain = getenv('DOMAIN');

if ($domain) {
    $remote = new CommandManager(
            isProd: false,
            user: $domain
    );
    $deployer = new Deployer();
    $deployer->personalSync($remote, $domain);
    exit;
}
/////////////////////////////

$isProd = ($argv[1] ?? 'beta') === 'prod';
$command = $argv[2] ?? 'ls';
$releaseId = $argv[3] ?? null;

$commands = new SymlinkManager(new CommandManager($isProd));
$commands->handle($command, $releaseId);
