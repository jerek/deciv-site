<?php

// TODO: Move to a global bootstrap
define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/getID3-master/getid3/getid3.php';

require_once ROOT_DIR . '/library/DB.php';

class Podcast {
    // ********************* //
    // ***** CONSTANTS ***** //
    // ********************* //

    /** @var string[] A simple list of known bot names found in user agents. */
    private const BOTS = [
        'Teoma', 'alexa', 'froogle', 'Gigabot', 'inktomi', 'looksmart', 'URL_Spider_SQL', 'Firefly',
        'NationalDirectory', 'Ask Jeeves', 'TECNOSEEK', 'InfoSeek', 'WebFindBot', 'girafabot', 'crawler',
        'www.galaxy.com', 'Googlebot', 'Scooter', 'Slurp', 'msnbot', 'appie', 'FAST', 'WebBug', 'Spade', 'ZyBorg',
        'rabaz', 'Baiduspider', 'Feedfetcher-Google', 'TechnoratiSnoop', 'Rankivabot', 'Mediapartners-Google',
        'Sogou web spider', 'WebAlta Crawler', 'TweetmemeBot', 'Butterfly', 'Twitturls', 'Me.dium', 'Twiceler',
    ];

    // Episode info defaults
    private const DEFAULT_AUTHOR = 'Decivilization.com';
    private const DEFAULT_DESCRIPTION = 'A post-apocalyptic RPG podcast in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.';

    // File locations
    private const EPISODES_PATH = '/downloads/episodes';
    private const EPISODES_HOST = 'http://deciv.com';
    private const PUBLIC_DIR = ROOT_DIR . '/public';

    // ********************** //
    // ***** PROPERTIES ***** //
    // ********************** //

    // ====== //
    // PUBLIC //
    // ====== //

    /**
     * CONFIGURATION VARIABLES:
     * For more info on these settings, see the instructions at
     *
     * http://www.apple.com/itunes/podcasts/specs.html
     *
     * and the RSS 2.0 spec at
     *
     * http://www.rssboard.org/rss-specification
     *
     * @var array
     */
    public static $feed = [
        // Generic
        'title' => 'Decivilization: A Post-Apocalyptic RPG Podcast',
        'link' => 'http://deciv.com/', // "More info" link for your feed
        'description' => 'A post-apocalyptic RPG podcast in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.',
        'copyright' => 'All content &#0169; River City Games',
        'ttl' => 86400, // 60 * 60 * 24 - How often feed readers check for new material (in seconds) -- mostly ignored by readers
        'lang' => 'en-us',

        // iTunes-specific
        'author' => 'The Decivilization Podcast',
        'email' => 'jerekdain@gmail.com',
        'image' => 'http://deciv.com/images/podcast-cover.jpg',
        'explicit' => 'yes',
        'category' => 'Games &amp; Hobbies',
        'subcategory' => 'Hobbies',
    ];

    // ======= //
    // PRIVATE //
    // ======= //

    /** @var getID3 An instance of the getID3 vendor class */
    private static $id3Engine = null;

    // ********************* //
    // ***** FUNCTIONS ***** //
    // ********************* //

    // ====== //
    // PUBLIC //
    // ====== //

    public static function getEpisodes() {
        if (self::$id3Engine === null) {
            self::$id3Engine = new getID3();
        }

        $episodes = [];
        $directory = opendir(self::PUBLIC_DIR . self::EPISODES_PATH) or die($php_errormsg);

        // Step through file directory
        while (($file = readdir($directory)) !== false) {
            $filePath = self::PUBLIC_DIR . self::EPISODES_PATH . '/' . $file;

            // not . or .., ends in .mp3
            if(is_file($filePath) && strrchr($filePath, '.') === '.mp3') {
                // Initialise file details to sensible defaults
                $episode = [
                    'title' => $file,
                    'url' => self::EPISODES_HOST . self::EPISODES_PATH . '/' . $file,
                    'relativeUrl' => self::EPISODES_PATH . '/' . $file,
                    'author' => self::DEFAULT_AUTHOR,
                    'duration' => '',
                    'description' => self::DEFAULT_DESCRIPTION,
                    'date' => date(DateTime::RFC2822, filemtime($filePath)),
                    'timestamp' => filemtime($filePath),
                    'size' => filesize($filePath),
                ];

                // Read file metadata from the ID3 tags
                $id3Info = self::$id3Engine->analyze($filePath);
                getid3_lib::CopyTagsToComments($id3Info);

                if (!empty($id3Info['comments']['title'][0])) {
                    $episode['title'] = $id3Info['comments']['title'][0];
                }
                if (!empty($id3Info['comments']['artist'][0])) {
                    $episode['author'] = $id3Info['comments']['artist'][0];
                }
                if (!empty($id3Info['playtime_string'])) {
                    $episode['duration'] = $id3Info['playtime_string'];
                }
                if (!empty($id3Info['comments']['comment'][0])) {
                    $episode['description'] = $id3Info['comments']['comment'][0];
                }
                $episode['htmlDescription'] = htmlentities($episode['description']);
                $episode['htmlDescription'] = '<p>' . implode('</p><p>', explode("\n\n", $episode['htmlDescription'])) . '</p>';
                $episode['htmlDescription'] = str_replace("\n", '<br>', $episode['htmlDescription']);

                $episodes[] = $episode;
            }
        }

        closedir($directory);

        usort($episodes, function (array $a, array $b): int {
            return strcmp($a['relativeUrl'], $b['relativeUrl']);
        });

        return $episodes;
    }

    /**
     * Download the given episode.
     *
     * @param string  $file  A file path starting from the public root, with no leading slash.
     */
    public static function downloadEpisode(string $file): void {
        // If the user tried to request something outside the episodes dir, show a 404 message.
        $realPath = realpath(self::PUBLIC_DIR . '/' . $file);
        if (dirname($realPath) !== self::PUBLIC_DIR . self::EPISODES_PATH) {
            header("{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found");
            exit;
        }

        // Log that we got a download. 🎊
        try {
            Podcast::logDownload($file);
        } catch (Exception $exception) {
            error_log("[jdError] Caught download logging error: {$exception->getMessage()}");

            try {
                Podcast::logDownload($file, false);
            } catch (Exception $exception) {
                error_log("[jdError] Caught SECOND download logging error: {$exception->getMessage()}");
            }
        }

        // Send the file to the user.
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Length: ' . filesize($realPath));
        header('Content-Type: application/force-download');
        header('Content-Transfer-Encoding: binary');
        ob_clean();
        ob_end_flush();
        readfile($realPath);
    }

    // ======= //
    // PRIVATE //
    // ======= //

    /**
     * If the request appears to be from a bot, return its bot name.
     *
     * @return string|null
     */
    private static function getBotName(): ?string {
        foreach (self::BOTS as $bot) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                return $bot;
            }
        }

        return null;
    }

    /**
     * Log in the database that the given file is being downloaded.
     *
     * @param string  $file
     * @param bool    $fetchIpInfo
     */
    private static function logDownload(string $file, bool $fetchIpInfo = true): void {
        // Determine the user's IP.
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?: '';
        }

        $info = null;
        if ($ip && $fetchIpInfo) {
            // If we have an IP, try to get some basic info on it.
            $curlHandle = curl_init();
            curl_setopt_array($curlHandle, [
                CURLOPT_URL => "https://ipinfo.io/{$ip}",
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
            ]);
            $response = curl_exec($curlHandle);
            $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            $errorMessage = curl_error($curlHandle);
            curl_close($curlHandle);
            if ($statusCode !== 200 || $errorMessage) {
                error_log("[jdWarning] IP info curl failed. Status: [{$statusCode}] Message: [{$errorMessage}]");
            } else {
                $responseData = json_decode($response);
                if (json_last_error() === JSON_ERROR_NONE && $responseData) {
                    if ($botName = self::getBotName()) {
                        $responseData['botName'] = $botName;
                    }
                    $info = json_encode($responseData);
                }
            }
        }

        $sql = 'INSERT INTO accessLog (file, ip, time, info) VALUES (:file, :ip, :time, :info)';
        $params = [
            'file' => $file,
            'ip' => $ip,
            'time' => time(),
            'info' => $info,
        ];

        DB::queryExec($sql, $params);
    }
}

// Dynamically add the date to the copyright text
Podcast::$feed['copyright'] .= ' ' . date('Y');
