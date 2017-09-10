<?php

// TODO: Move to a global bootstrap
define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/getID3-master/getid3/getid3.php';

class Podcast {
    // ********************* //
    // ***** CONSTANTS ***** //
    // ********************* //

    // File locations
    const EPISODES_PATH = '/downloads/episodes';
    const EPISODES_DIR = ROOT_DIR . '/public/downloads/episodes';
    const EPISODES_URL = 'http://deciv.com' . self::EPISODES_PATH;

    // Episode info defaults
    const DEFAULT_AUTHOR = 'Decivilization.com';
    const DEFAULT_DESCRIPTION = 'A post-apocalyptic RPG podcast in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.';

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
        'ttl' => 60 * 60 * 24, // How often feed readers check for new material (in seconds) -- mostly ignored by readers
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

    public static function getEpisodes() {
        if (self::$id3Engine === null) {
            self::$id3Engine = new getID3();
        }

        $episodes = [];
        $directory = opendir(self::EPISODES_DIR) or die($php_errormsg);

        // Step through file directory
        while (($file = readdir($directory)) !== false) {
            $filePath = self::EPISODES_DIR . '/' . $file;

            // not . or .., ends in .mp3
            if(is_file($filePath) && strrchr($filePath, '.') == ".mp3") {
                // Initialise file details to sensible defaults
                $episode = [
                    'title' => $file,
                    'url' => self::EPISODES_URL . '/' . $file,
                    'author' => self::DEFAULT_AUTHOR,
                    'duration' => '',
                    'description' => self::DEFAULT_DESCRIPTION,
                    'date' => date(DateTime::RFC2822, filemtime($filePath)),
                    'size' => filesize($filePath),
                ];

                // Read file metadata from the ID3 tags
                $id3_info = self::$id3Engine->analyze($filePath);
                getid3_lib::CopyTagsToComments($id3_info);

                if (!empty($id3_info["comments_html"]["title"][0])) {
                    $episode['title'] = $id3_info["comments_html"]["title"][0];
                }
                if (!empty($id3_info["comments_html"]["artist"][0])) {
                    $episode['author'] = $id3_info["comments_html"]["artist"][0];
                }
                if (!empty($id3_info["playtime_string"])) {
                    $episode['duration'] = $id3_info["playtime_string"];
                }

                $episodes[] = $episode;
            }
        }

        closedir($directory);

        return $episodes;
    }
}

// Dynamically add the date to the copyright text
Podcast::$feed['copyright'] .= ' ' . date('Y');
