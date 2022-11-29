<?php
    declare(strict_types=1);

    ob_start();
    require_once('versions.php');
    $versions = json_decode((string) ob_get_contents(), true);
    ob_end_clean();

    $flavor = $_GET['flavor'] ?? null;
    if(empty($flavor) || !isset($versions['flavors'][$flavor])) {
        die('Invalid image flavor specified, unable to download...');
    }

    $latest_image = $versions['flavors'][$flavor][0] ?? null;
    if(empty($latest_image)) {
        die('No versions for this image flavor exist, unable to download...');
    }

    header('Location: /' . $latest_image['name']);
