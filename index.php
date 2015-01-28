<?php

namespace SteamCommonGameFinder;

require_once 'config.php';
require_once 'class.steam.php';

if (isset($_GET['player'])) {
    $players = $_GET['player'];
}

if (empty($players)) {
    $players = array('', '');
} else {
    $steam = new Steam(APIKEY);
    $all_games = $steam->getGameNames();
    $common_games_appids = array();
    foreach ($players as $player) {
        $steamid = $steam->resolveVanityURL($player);
        if (!$steamid) {
            $steamid = $player;
        }
        $owned_games = $steam->getOwnedGames($steamid);
        $owned_game_ids = array();

        if (!empty($owned_games)) {
            foreach ($owned_games as $game) {
                $owned_game_ids[] = $game->appid;
            }
        }

        if (empty($common_games_appids)) {
            $common_games_appids = $owned_game_ids;
        } else {
            $common_games_appids = array_intersect($common_games_appids, $owned_game_ids);
        }
    }
    $common_games_names = array();
    foreach ($common_games_appids as $appid) {
        $common_games_names[] = $all_games[$appid];
    }
    natcasesort($common_games_names);
}

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
    <h1>Steam Common Game Finder</h1>
    <p>Find what games you have in common with your friends</p>

    <form id="players" method="get" action="">
        <ul>
            <?php
            foreach ($players as $player) :
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

    <pre>
    <?php
    if (!empty($common_games_names)) :
        foreach ($common_games_names as $game_name) :
            echo $game_name;
    ?>

    <?php
        endforeach;
    endif;
    ?>
    </pre>

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
        });
    </script>
</body>
</html>
