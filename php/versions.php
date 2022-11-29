<?php
    declare(strict_types=1);
    date_default_timezone_set('UTC');

    function collect_images(string $pattern) {
        $image_files = glob($pattern);
        $images = [];
        foreach($image_files as $image_file) {
            // Determine image size and date
            $image_size = filesize($image_file);
            $image_date = filemtime($image_file);

            // Read image checksum
            $image_checksum_file = preg_replace('~\.zip$~', '.sha256', $image_file);
            $image_checksum = file_exists($image_checksum_file)
                ? explode("\t", trim(file_get_contents($image_checksum_file)))[0]
                : 'not available';

            // Skip images without version delimiter
            if(strpos($image_file, '-') === false) {
                continue;
            }

            // Add collected data to list of images
            $images[] = [
                'name' => $image_file,
                'version' => explode('-', preg_replace('~\.img\.zip$~', '', $image_file))[1],
                'size' => $image_size,
                'date' => date('Y-m-d H:i', $image_date),
                'checksum' => $image_checksum,
                'checksum_file' => $image_checksum_file,
            ];
        }

        usort($images, function($a, $b) {
            // Check if version string contains semver
            $aSemver = (bool) preg_match('/^\d+\.\d+\.\d+$/', $a['version']);
            $bSemver = (bool) preg_match('/^\d+\.\d+\.\d+$/', $b['version']);

            // Sort semver descending, then branch names ascending alphabetically
            if($aSemver && $bSemver) {
                // Sort descending if both versions are semver
                return strcmp((string) $b['version'], (string) $a['version']);
            } else if($aSemver && !$bSemver) {
                // Always prefer semver over branch name
                return -1;
            } else if(!$aSemver && $bSemver) {
                // Always prefer semver over branch name
                return 1;
            } else {
                // Sort ascending if both versions are branch
                return strcmp((string) $a['version'], (string) $b['version']);
            }
        });

        return $images;
    }

    $flavors = [
        'crowpi' => collect_images('crowpi-*.img.zip'),
        'picade' => collect_images('picade-*.img.zip'),
    ];

    header('Content-Type: application/json');
    echo json_encode(['flavors' => $flavors]);
