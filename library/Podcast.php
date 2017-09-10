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
    const EPISODES_DIR  = '/public/downloads/episodes';
    const EPISODES_HOST = 'http://deciv.com';

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

    public static function getEpisodes() {
        if (self::$id3Engine === null) {
            self::$id3Engine = new getID3();
        }

        $episodes = [];
        $directory = opendir(ROOT_DIR . self::EPISODES_DIR) or die($php_errormsg);

        // Step through file directory
        while (($file = readdir($directory)) !== false) {
            $filePath = ROOT_DIR . self::EPISODES_DIR . '/' . $file;

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

        return $episodes;
    }
}

// Dynamically add the date to the copyright text
Podcast::$feed['copyright'] .= ' ' . date('Y');
