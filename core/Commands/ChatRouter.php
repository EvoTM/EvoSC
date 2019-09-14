<?php


namespace esc\Commands;


use esc\Classes\Database;
use esc\Classes\Log;
use esc\Classes\Server;
use esc\Controllers\ConfigController;
use esc\Models\Player;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChatRouter extends Command
{
    protected function configure()
    {
        $this->setName('run:chat-router')
            ->setDescription('Run EvoSC chat-routing-service');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        Log::setOutput($output);
        ConfigController::init();
        Database::init();

        try {
            $output->writeln("Connecting to server...");

            Server::init(
                config('server.ip'),
                config('server.port'),
                5,
                config('server.rpc.login'),
                config('server.rpc.password')
            );

            Server::chatEnableManualRouting(true, true);

            $output->writeln("Connection established.");
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $output->writeln("<warning>Connecting to server failed: $msg</warning>");
            exit(1);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $players = collect();

        while (true) {
            foreach (Server::executeCallbacks() as $callback) {
                if ($callback[0] == 'ManiaPlanet.PlayerChat') {
                    $login = $callback[1][1];
                    $text = $callback[1][2];

                    if (substr($text, 0, 1) == '/' || substr($text, 0, 2) == '/') {
                        continue;
                    }

                    if (!$players->has($login)) {
                        $players->put($login, Player::find($login));
                    }

                    $this->playerChat($players->get($login), $text);
                } elseif ($callback[0] == 'ManiaPlanet.PlayerDisconnect') {
                    $players->forget($callback[1][0]);
                }
            }

            usleep(100000);
        }
    }

    public function playerChat(Player $player, $text)
    {
        $nick = $player->NickName;

        if ($player->isSpectator()) {
            $nick = '$eee📷 '.$nick;
        }

        $prefix = $player->group->chat_prefix;
        $color = $player->group->color ?? config('colors.chat');
        $chatText = sprintf('$%s[$z$s%s$z$s$%s] $%s$z$s%s', $color, $nick, $color, config('colors.chat'), $text);

        if ($prefix) {
            $chatText = '$'.$color.$prefix.' '.$chatText;
        }

        Server::ChatSendServerMessage($chatText);
    }
}