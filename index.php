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
    $common_games = array();
    foreach ($players as $player) {
        $steamid = $steam->resolveVanityURL($player);
        if (!$steamid) {
            $steamid = $player;
        }

        $owned_games_raw = $steam->getOwnedGames($steamid);
        $owned_games = array();

        if (!empty($owned_games_raw)) {
            foreach ($owned_games_raw as $owned_game) {
                $owned_games[$owned_game->appid] = $owned_game;
            }
        }

        if (empty($common_games)) {
            $common_games = $owned_games;
        } else {
            $common_games = array_intersect_key($common_games, $owned_games);
        }
    }
}

$game_names = array();
foreach ($common_games as $key => $row) {
    $game_names[$key] = $row->name;
}
array_multisort($game_names, SORT_ASC, $common_games);

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
    if (!empty($common_games)) :
        foreach ($common_games as $common_game) :
            echo $common_game->name;
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
