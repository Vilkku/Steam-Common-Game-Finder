<?php

namespace SteamCommonGameFinder;

require_once 'config.php';
require_once 'class.steam.php';

if (isset($_GET['player'])) {
    $players = $_GET['player'];
}

$common_games = array();
$player_names = array();

if (empty($players)) {
    $players = array('', '');
} else {
    $steam = new Steam(APIKEY);
    foreach ($players as $player) {
        $steamid = $steam->resolveVanityURL($player);
        if (!$steamid) {
            $steamid = $player;
        }

        $player_name = $steam->getProfileName($steamid);
        $owned_games_raw = $steam->getOwnedGames($steamid);

        $player_names[$steamid] = $player_name;

        if (!empty($owned_games_raw)) {
            foreach ($owned_games_raw as $owned_game) {
                $owned_games[$owned_game->appid] = $owned_game;

                if (!isset($common_games[$owned_game->appid])) {
                    $common_games[$owned_game->appid] = array(
                        'game' => $owned_game,
                        'players' => array($player_name)
                    );
                } else {
                    $common_games[$owned_game->appid]['players'][] = $player_name;
                }
            }
        }
    }
}

foreach ($common_games as $key => $game) {
    if (count($game['players']) < 2) {
        unset($common_games[$key]);
    }
}

function gameSort($a, $b) {
    if (count($a['players']) === count($b['players'])) {
        return strcasecmp($a['game']->name, $b['game']->name);
    }

    return (count($a['players']) < count($b['players'])) ? 1 : -1;
}

usort($common_games, '\SteamCommonGameFinder\gameSort');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Steam Common Game Finder</title>
    <meta charset="utf-8">

    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <h1>Steam Common Game Finder</h1>
    <p>Find what games you have in common with your friends</p>

    <form id="players" method="get" action="">
        <ul>
            <?php
            foreach ($players as $player):
            ?>
            <li class="input-player">
                <input type="text" name="player[]" value="<?php echo $player; ?>">
                <button class="button-remove">Remove</button>
            </li>
            <?php
            endforeach;
            ?>
        </ul>
        <div><button id="button-add">Add player</button></div>
        <input type="submit" value="Find Games!">
    </form>

    <ul class="game-list">
    <?php
    foreach ($common_games as $game):
    ?>
        <li>
            <?php
                $players_without_game = array_diff($player_names, $game['players']);
                $players_with_game = array_diff($player_names, $players_without_game);

                if (!empty($players_without_game)) {
                    $percent_rounded = round(count($players_with_game) / count($players) * 100, -1);
                    $class = 'color-'.$percent_rounded;
                } else {
                    $class = 'color-100';
                }

                echo '<img src="https://steamcdn-a.akamaihd.net/steamcommunity/public/images/apps/'.$game['game']->appid.'/'.$game['game']->img_icon_url.'.jpg" /> <label class="game-title"><a href="https://store.steampowered.com/app/'.$game['game']->appid.'">'.$game['game']->name.'</a> <span class="'.$class.'" title="'.implode(', ', $players_with_game).'">('.count($game['players']).' / '.count($players).')</span></label>';
            ?>
            <?php if (!empty($players_without_game)): ?>
            <ul class="players-without-game" style="display: none;">
                <?php foreach ($players_without_game as $player): ?>
                <li><?php echo $player; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
    <?php
    endforeach;
    ?>
    </ul>
    </div>

    <script>
        $(document).ready(function() {
            $('#button-add').click(function(e) {
                e.preventDefault();
                $('#players ul').append(playerInput.clone(true));
                refreshButtons();
            });
            $('.button-remove').click(function(e) {
                e.preventDefault();
                if ($('.input-player').length > 2) {
                    $(this).parent().remove();
                }
                refreshButtons();
            });

            function refreshButtons() {
                var playerCount = $('.input-player').length;
                if (playerCount == 2) {
                    $('.button-remove').prop('disabled', true);
                } else {
                    $('.button-remove').prop('disabled', false);
                }
            }

            var playerInput = $('.input-player').first().clone(true);
            playerInput.find('input').val('');
            refreshButtons();

            $('.game-title').on('click', function () {
                $(this).closest('li').find('.players-without-game').toggle();
            });
        });
    </script>
</body>
</html>
